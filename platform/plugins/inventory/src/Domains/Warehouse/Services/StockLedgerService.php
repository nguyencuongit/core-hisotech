<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\GoodsReceipt\Models\ReceiptStorageItem;
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
            $balance->updated_at = now();
            $balance->save();

            StockTransaction::query()->create([
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
                $target->updated_at = now();
                $target->save();

                StockTransaction::query()->create([
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

    protected function nextTransactionCode(): string
    {
        do {
            $code = 'STK-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (StockTransaction::query()->where('transaction_code', $code)->exists());

        return $code;
    }
}
