<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\GoodsReceipt\Models\ReceiptStorageItem;
use Botble\Inventory\Domains\Packing\Models\PackingListItem;
use Botble\Inventory\Domains\Transfer\Models\InternalTransferItem;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\StockBalance;
use Botble\Inventory\Domains\Warehouse\Models\StockTransaction;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockLedgerService
{
    public function postReceiptStorageItem(ReceiptStorageItem $storageItem, ?string $transactionType = null): void
    {
        DB::transaction(function () use ($storageItem, $transactionType): void {
            $storageItem->loadMissing(['goodsReceiptItem', 'goodsReceiptBatch']);
            $storageItem->refresh();

            if ($storageItem->posted_at) {
                return;
            }

            if (
                StockTransaction::query()
                    ->where('reference_type', 'receipt_storage_item')
                    ->where('reference_id', $storageItem->getKey())
                    ->exists()
            ) {
                $storageItem->forceFill([
                    'posted_at' => $storageItem->posted_at ?: now(),
                    'posted_by' => $storageItem->posted_by ?: auth()->id(),
                ])->save();

                return;
            }

            $status = (string) $storageItem->status;

            if (! in_array($status, ['stored', 'qc_hold', 'damaged', 'rejected'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Trạng thái hiện tại chưa thể ghi tồn kho.',
                ]);
            }

            if ($status === 'stored' && ! $storageItem->warehouse_location_id) {
                throw ValidationException::withMessages([
                    'warehouse_location_id' => 'Cần chọn vị trí kho trước khi ghi tồn.',
                ]);
            }

            [$quantity, $availableQty, $qcHoldQty, $damagedQty, $rejectedQty] = $this->resolveBalanceBuckets($storageItem);

            $balance = StockBalance::query()->firstOrNew([
                'warehouse_id' => $storageItem->warehouse_id,
                'warehouse_location_id' => $storageItem->warehouse_location_id,
                'pallet_id' => $storageItem->pallet_id,
                'product_id' => $storageItem->product_id,
                'product_variation_id' => $storageItem->product_variation_id,
                'goods_receipt_batch_id' => $storageItem->goods_receipt_batch_id,
            ]);

            $beforeQty = (float) ($balance->quantity ?? 0);

            $balance->batch_id = $balance->batch_id ?: $storageItem->goods_receipt_batch_id;
            $balance->goods_receipt_batch_id = $storageItem->goods_receipt_batch_id;
            $balance->quantity = $beforeQty + $quantity;
            $balance->available_qty = (float) ($balance->available_qty ?? 0) + $availableQty;
            $balance->qc_hold_qty = (float) ($balance->qc_hold_qty ?? 0) + $qcHoldQty;
            $balance->damaged_qty = (float) ($balance->damaged_qty ?? 0) + $damagedQty;
            $balance->rejected_qty = (float) ($balance->rejected_qty ?? 0) + $rejectedQty;
            $balance->reserved_qty = (float) ($balance->reserved_qty ?? 0);
            $balance->last_unit_cost = (float) ($storageItem->goodsReceiptItem?->unit_cost ?? $balance->last_unit_cost ?? 0);
            $balance->average_cost = $this->resolveAverageCost($balance, $beforeQty, (float) ($storageItem->goodsReceiptItem?->unit_cost ?? 0), $quantity);
            $balance->dimension_key = $this->dimensionKey($balance);
            $balance->last_movement_at = now();
            $balance->updated_at = now();
            $balance->save();

            StockTransaction::query()->create([
                'stock_balance_id' => $balance->getKey(),
                'transaction_code' => $this->nextTransactionCode(),
                'type' => $transactionType ?: $this->resolveTransactionType($status),
                'reference_type' => 'receipt_storage_item',
                'reference_id' => $storageItem->getKey(),
                'reference_item_id' => $storageItem->goods_receipt_item_id,
                'product_id' => $storageItem->product_id,
                'product_variation_id' => $storageItem->product_variation_id,
                'warehouse_id' => $storageItem->warehouse_id,
                'warehouse_location_id' => $storageItem->warehouse_location_id,
                'pallet_id' => $storageItem->pallet_id,
                'storage_item_id' => $storageItem->getKey(),
                'goods_receipt_id' => $storageItem->goods_receipt_id,
                'goods_receipt_item_id' => $storageItem->goods_receipt_item_id,
                'goods_receipt_batch_id' => $storageItem->goods_receipt_batch_id,
                'batch_id' => $storageItem->goods_receipt_batch_id,
                'quantity' => $quantity,
                'reserved_delta' => 0,
                'available_delta' => $availableQty,
                'unit_cost' => (float) ($storageItem->goodsReceiptItem?->unit_cost ?? 0),
                'before_qty' => $beforeQty,
                'after_qty' => (float) $balance->quantity,
                'note' => $storageItem->note,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            $storageItem->forceFill([
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ])->save();
        });
    }

    public function movePallet(Pallet $pallet, ?WarehouseLocation $fromLocation, ?WarehouseLocation $toLocation, string $movementType = 'internal_move', ?string $note = null): void
    {
        DB::transaction(function () use ($pallet, $fromLocation, $toLocation, $movementType, $note): void {
            $balances = StockBalance::query()
                ->where('warehouse_id', $pallet->warehouse_id)
                ->where('pallet_id', $pallet->getKey())
                ->where('warehouse_location_id', $fromLocation?->getKey())
                ->get();

            foreach ($balances as $balance) {
                $target = StockBalance::query()->firstOrNew([
                    'warehouse_id' => $balance->warehouse_id,
                    'warehouse_location_id' => $toLocation?->getKey(),
                    'pallet_id' => $balance->pallet_id,
                    'product_id' => $balance->product_id,
                    'product_variation_id' => $balance->product_variation_id,
                    'goods_receipt_batch_id' => $balance->goods_receipt_batch_id,
                ]);

                $targetBeforeQty = (float) ($target->quantity ?? 0);

                $target->batch_id = $balance->batch_id;
                $target->goods_receipt_batch_id = $balance->goods_receipt_batch_id;
                $target->quantity = $targetBeforeQty + (float) $balance->quantity;
                $target->available_qty = (float) ($target->available_qty ?? 0) + (float) $balance->available_qty;
                $target->reserved_qty = (float) ($target->reserved_qty ?? 0) + (float) $balance->reserved_qty;
                $target->qc_hold_qty = (float) ($target->qc_hold_qty ?? 0) + (float) $balance->qc_hold_qty;
                $target->damaged_qty = (float) ($target->damaged_qty ?? 0) + (float) $balance->damaged_qty;
                $target->rejected_qty = (float) ($target->rejected_qty ?? 0) + (float) $balance->rejected_qty;
                $target->average_cost = $balance->average_cost;
                $target->last_unit_cost = $balance->last_unit_cost;
                $target->dimension_key = $this->dimensionKey($target);
                $target->last_movement_at = now();
                $target->updated_at = now();
                $target->save();

                StockTransaction::query()->create([
                    'stock_balance_id' => $target->getKey(),
                    'transaction_code' => $this->nextTransactionCode(),
                    'type' => $movementType === 'internal_move' ? 'move' : $movementType,
                    'reference_type' => 'pallet',
                    'reference_id' => (string) $pallet->getKey(),
                    'reference_item_id' => null,
                    'product_id' => $balance->product_id,
                    'product_variation_id' => $balance->product_variation_id,
                    'warehouse_id' => $balance->warehouse_id,
                    'warehouse_location_id' => $toLocation?->getKey(),
                    'pallet_id' => $pallet->getKey(),
                    'storage_item_id' => null,
                    'goods_receipt_id' => null,
                    'goods_receipt_item_id' => null,
                    'goods_receipt_batch_id' => $balance->goods_receipt_batch_id,
                    'batch_id' => $balance->batch_id,
                    'quantity' => (float) $balance->quantity,
                    'reserved_delta' => 0,
                    'available_delta' => (float) $balance->available_qty,
                    'unit_cost' => (float) ($balance->last_unit_cost ?? 0),
                    'before_qty' => $targetBeforeQty,
                    'after_qty' => (float) $target->quantity,
                    'note' => $note,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);

                $balance->delete();
            }
        });
    }

    public function postInternalTransferOut(InternalTransferItem $item): void
    {
        if (! $item->stock_balance_id || (float) $item->requested_qty <= 0) {
            return;
        }

        DB::transaction(function () use ($item): void {
            $item->loadMissing('transfer');

            if ($this->hasStockTransaction('internal_transfer_out', 'internal_transfer_item', (string) $item->getKey())) {
                return;
            }

            $transfer = $item->transfer;
            $balance = StockBalance::query()
                ->lockForUpdate()
                ->find($item->stock_balance_id);

            if (! $balance || ! $transfer || (int) $balance->warehouse_id !== (int) $transfer->from_warehouse_id) {
                throw ValidationException::withMessages([
                    'items' => 'Tồn kho nguồn của phiếu chuyển không hợp lệ.',
                ]);
            }

            $qty = (float) $item->requested_qty;
            $beforeQty = (float) ($balance->quantity ?? 0);
            $beforeAvailable = (float) ($balance->available_qty ?? 0);

            if ($beforeAvailable + 0.0001 < $qty) {
                throw ValidationException::withMessages([
                    'items' => sprintf('Tồn khả dụng của %s chỉ còn %.4f, không đủ xuất chuyển %.4f.', $item->product_name ?: $item->product_id, $beforeAvailable, $qty),
                ]);
            }

            $balance->quantity = max($beforeQty - $qty, 0);
            $balance->available_qty = max($beforeAvailable - $qty, 0);
            $balance->dimension_key = $this->dimensionKey($balance);
            $balance->last_movement_at = now();
            $balance->updated_at = now();
            $balance->save();

            StockTransaction::query()->create([
                'stock_balance_id' => $balance->getKey(),
                'transaction_code' => $this->nextTransactionCode(),
                'type' => 'internal_transfer_out',
                'reference_type' => 'internal_transfer_item',
                'reference_id' => (string) $item->getKey(),
                'reference_item_id' => (string) $item->transfer_id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'warehouse_id' => $balance->warehouse_id,
                'warehouse_location_id' => $item->from_location_id ?: $balance->warehouse_location_id,
                'pallet_id' => $item->pallet_id ?: $balance->pallet_id,
                'storage_item_id' => null,
                'goods_receipt_id' => null,
                'goods_receipt_item_id' => null,
                'goods_receipt_batch_id' => $item->goods_receipt_batch_id ?: $balance->goods_receipt_batch_id,
                'batch_id' => $item->batch_id ?: $balance->batch_id,
                'quantity' => -1 * $qty,
                'reserved_delta' => 0,
                'available_delta' => -1 * $qty,
                'unit_cost' => (float) ($balance->last_unit_cost ?? $item->unit_price ?? 0),
                'before_qty' => $beforeQty,
                'after_qty' => (float) $balance->quantity,
                'note' => $item->note,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }

    public function postInternalTransferIn(InternalTransferItem $item): void
    {
        if ((float) $item->requested_qty <= 0) {
            return;
        }

        DB::transaction(function () use ($item): void {
            $item->loadMissing('transfer');

            if ($this->hasStockTransaction('internal_transfer_in', 'internal_transfer_item', (string) $item->getKey())) {
                return;
            }

            $transfer = $item->transfer;

            if (! $transfer || ! $item->to_location_id) {
                throw ValidationException::withMessages([
                    'items' => 'Cần chọn vị trí nhập kho đích trước khi hoàn tất chuyển kho.',
                ]);
            }

            $receivedQty = (float) $item->received_qty;

            if ($receivedQty <= 0 && (float) ($item->shortage_qty ?: 0) <= 0 && (float) ($item->damaged_qty ?: 0) <= 0) {
                $receivedQty = (float) $item->requested_qty;
            }
            $damagedQty = min(max((float) ($item->damaged_qty ?: 0), 0), $receivedQty);
            $qty = max($receivedQty - $damagedQty, 0);
            $target = StockBalance::query()->firstOrNew([
                'warehouse_id' => $transfer->to_warehouse_id,
                'warehouse_location_id' => $item->to_location_id,
                'pallet_id' => $item->to_pallet_id ?: null,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
            ]);

            $beforeQty = (float) ($target->quantity ?? 0);
            $unitCost = (float) ($item->unit_price ?? $target->last_unit_cost ?? 0);

            $target->batch_id = $item->batch_id ?: $target->batch_id;
            $target->goods_receipt_batch_id = $item->goods_receipt_batch_id;
            $target->quantity = $beforeQty + $qty;
            $target->available_qty = (float) ($target->available_qty ?? 0) + $qty;
            $target->reserved_qty = (float) ($target->reserved_qty ?? 0);
            $target->qc_hold_qty = (float) ($target->qc_hold_qty ?? 0);
            $target->damaged_qty = (float) ($target->damaged_qty ?? 0) + $damagedQty;
            $target->rejected_qty = (float) ($target->rejected_qty ?? 0);
            $target->last_unit_cost = $unitCost;
            $target->average_cost = $this->resolveAverageCost($target, $beforeQty, $unitCost, $qty);
            $target->dimension_key = $this->dimensionKey($target);
            $target->last_movement_at = now();
            $target->updated_at = now();
            $target->save();

            StockTransaction::query()->create([
                'stock_balance_id' => $target->getKey(),
                'transaction_code' => $this->nextTransactionCode(),
                'type' => 'internal_transfer_in',
                'reference_type' => 'internal_transfer_item',
                'reference_id' => (string) $item->getKey(),
                'reference_item_id' => (string) $item->transfer_id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'warehouse_id' => $transfer->to_warehouse_id,
                'warehouse_location_id' => $item->to_location_id,
                'pallet_id' => $item->to_pallet_id ?: null,
                'storage_item_id' => null,
                'goods_receipt_id' => null,
                'goods_receipt_item_id' => null,
                'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
                'batch_id' => $item->batch_id,
                'quantity' => $qty,
                'reserved_delta' => 0,
                'available_delta' => $qty,
                'unit_cost' => $unitCost,
                'before_qty' => $beforeQty,
                'after_qty' => (float) $target->quantity,
                'note' => $item->note,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }

    public function postPackingListItem(PackingListItem $item, string $transactionType = 'packing_packed'): void
    {
        if (! $item->stock_balance_id || (float) $item->packed_qty <= 0) {
            return;
        }

        DB::transaction(function () use ($item, $transactionType): void {
            $item->loadMissing(['packingList', 'exportItem']);

            if (
                StockTransaction::query()
                    ->where('type', $transactionType)
                    ->where('reference_type', 'packing_list_item')
                    ->where('reference_id', (string) $item->getKey())
                    ->exists()
            ) {
                return;
            }

            $balance = StockBalance::query()
                ->lockForUpdate()
                ->find($item->stock_balance_id);

            if (! $balance) {
                return;
            }

            $packedQty = (float) $item->packed_qty;
            $beforeQty = (float) ($balance->quantity ?? 0);
            $beforeReserved = (float) ($balance->reserved_qty ?? 0);
            $beforeAvailable = (float) ($balance->available_qty ?? 0);
            $quantityDelta = 0.0;
            $reservedDelta = 0.0;
            $availableDelta = 0.0;

            if ($transactionType === 'export_shipped') {
                $quantityDelta = -1 * $packedQty;
                $reservedDelta = -1 * min($beforeReserved, $packedQty);
                $availableDelta = -1 * min($beforeAvailable, $packedQty);

                $balance->quantity = max($beforeQty + $quantityDelta, 0);
                $balance->reserved_qty = max($beforeReserved + $reservedDelta, 0);
                $balance->available_qty = max($beforeAvailable + $availableDelta, 0);

                if ($item->exportItem) {
                    $item->exportItem->shipped_qty = min(
                        (float) ($item->exportItem->packed_qty ?? $packedQty),
                        (float) ($item->exportItem->shipped_qty ?? 0) + $packedQty
                    );
                    $item->exportItem->save();
                }
            } else {
                $reservedDelta = -1 * min($beforeReserved, $packedQty);
                $balance->reserved_qty = max($beforeReserved + $reservedDelta, 0);
            }

            $balance->dimension_key = $this->dimensionKey($balance);
            $balance->last_movement_at = now();
            $balance->updated_at = now();
            $balance->save();

            StockTransaction::query()->create([
                'stock_balance_id' => $balance->getKey(),
                'transaction_code' => $this->nextTransactionCode(),
                'type' => $transactionType,
                'reference_type' => 'packing_list_item',
                'reference_id' => (string) $item->getKey(),
                'reference_item_id' => $item->export_item_id ? (string) $item->export_item_id : null,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'warehouse_id' => $balance->warehouse_id,
                'warehouse_location_id' => $item->warehouse_location_id ?: $balance->warehouse_location_id,
                'pallet_id' => $item->pallet_id ?: $balance->pallet_id,
                'storage_item_id' => $item->storage_item_id,
                'goods_receipt_id' => null,
                'goods_receipt_item_id' => null,
                'goods_receipt_batch_id' => $item->goods_receipt_batch_id ?: $balance->goods_receipt_batch_id,
                'batch_id' => $item->batch_id ?: $balance->batch_id,
                'quantity' => $quantityDelta,
                'reserved_delta' => $reservedDelta,
                'available_delta' => $availableDelta,
                'unit_cost' => (float) ($balance->last_unit_cost ?? 0),
                'before_qty' => $beforeQty,
                'after_qty' => (float) $balance->quantity,
                'note' => $item->note,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }

    protected function resolveBalanceBuckets(ReceiptStorageItem $storageItem): array
    {
        $netQty = max(
            (float) $storageItem->received_qty
            - (float) $storageItem->damaged_qty
            - (float) $storageItem->rejected_qty,
            0
        );

        return match ((string) $storageItem->status) {
            'stored' => [
                $netQty,
                max((float) $storageItem->available_qty, $netQty),
                0.0,
                (float) $storageItem->damaged_qty,
                (float) $storageItem->rejected_qty,
            ],
            'qc_hold' => [
                $netQty,
                0.0,
                $netQty,
                (float) $storageItem->damaged_qty,
                (float) $storageItem->rejected_qty,
            ],
            'damaged' => [
                max((float) $storageItem->damaged_qty, $netQty),
                0.0,
                0.0,
                max((float) $storageItem->damaged_qty, $netQty),
                (float) $storageItem->rejected_qty,
            ],
            'rejected' => [
                0.0,
                0.0,
                0.0,
                0.0,
                max((float) $storageItem->rejected_qty, (float) $storageItem->received_qty),
            ],
            default => [0.0, 0.0, 0.0, 0.0, 0.0],
        };
    }

    protected function resolveTransactionType(string $status): string
    {
        return match ($status) {
            'stored' => 'putaway_in',
            'qc_hold' => 'qc_hold',
            'damaged' => 'damaged',
            'rejected' => 'rejected',
            default => 'receipt_in',
        };
    }

    protected function resolveAverageCost(StockBalance $balance, float $beforeQty, float $unitCost, float $deltaQty): float
    {
        $existingAverage = (float) ($balance->average_cost ?? 0);

        if ($deltaQty <= 0) {
            return $existingAverage;
        }

        $afterQty = $beforeQty + $deltaQty;

        if ($afterQty <= 0) {
            return $unitCost;
        }

        return (($beforeQty * $existingAverage) + ($deltaQty * $unitCost)) / $afterQty;
    }

    protected function hasStockTransaction(string $type, string $referenceType, string $referenceId): bool
    {
        return StockTransaction::query()
            ->where('type', $type)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->exists();
    }

    protected function nextTransactionCode(): string
    {
        do {
            $code = 'STK-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (StockTransaction::query()->where('transaction_code', $code)->exists());

        return $code;
    }

    protected function dimensionKey(StockBalance $balance): string
    {
        return implode('|', [
            (string) ($balance->warehouse_id ?: 0),
            (string) ($balance->warehouse_location_id ?: 0),
            (string) ($balance->pallet_id ?: 0),
            (string) ($balance->product_id ?: 0),
            (string) ($balance->product_variation_id ?: 0),
            (string) ($balance->batch_id ?: ''),
            (string) ($balance->goods_receipt_batch_id ?: ''),
        ]);
    }
}
