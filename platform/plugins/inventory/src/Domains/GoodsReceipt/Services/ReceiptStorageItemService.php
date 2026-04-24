<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Services;

use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceipt;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceiptItem;
use Botble\Inventory\Domains\GoodsReceipt\Models\ReceiptStorageItem;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProductPolicy;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseSetting;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseSettingService;
use Botble\Inventory\Domains\Warehouse\Support\PalletLocationRules;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceiptStorageItemService
{
    public function generateFromReceipt(GoodsReceipt $goodsReceipt): Collection
    {
        return DB::transaction(function () use ($goodsReceipt): Collection {
            $goodsReceipt->loadMissing(['items.batches', 'warehouse.setting']);

            $settings = $goodsReceipt->warehouse->setting
                ?: app(WarehouseSettingService::class)->firstOrCreateDefault($goodsReceipt->warehouse);

            $items = collect();

            foreach ($goodsReceipt->items as $item) {
                $policy = $this->resolvePolicy($goodsReceipt, $item);
                $items = $items->merge($this->buildItemsForReceiptItem($goodsReceipt, $item, $settings, $policy));
            }

            return $items;
        });
    }

    public function updateStorageLocation(ReceiptStorageItem $storageItem, array $data): ReceiptStorageItem
    {
        return DB::transaction(function () use ($storageItem, $data): ReceiptStorageItem {
            $storageItem->loadMissing(['warehouse.setting', 'pallet', 'goodsReceiptItem']);

            $settings = $storageItem->warehouse->setting
                ?: app(WarehouseSettingService::class)->firstOrCreateDefault($storageItem->warehouse);
            $policy = $this->resolvePolicyForStorageItem($storageItem);

            $locationId = Arr::get($data, 'warehouse_location_id') ? (int) Arr::get($data, 'warehouse_location_id') : null;
            $palletId = Arr::get($data, 'pallet_id') ? (int) Arr::get($data, 'pallet_id') : null;
            $status = (string) Arr::get($data, 'status', $storageItem->status);

            if ($palletId && ! $locationId) {
                $pallet = $this->ensurePalletAllowed($storageItem, $palletId, null);
                $locationId = $pallet->current_location_id ?: null;
            }

            if ($locationId) {
                $this->ensureLocationAllowed($storageItem, $locationId);
            }

            if ($palletId) {
                $this->ensurePalletAllowed($storageItem, $palletId, $locationId);
            }

            if (in_array($status, ['stored', 'pending_putaway'], true) && ! $locationId && ! ($policy?->allow_receive_without_location ?? true)) {
                throw ValidationException::withMessages([
                    'warehouse_location_id' => 'Sản phẩm này bắt buộc chọn vị trí kho trước khi nhập kho.',
                ]);
            }

            if (in_array($status, ['stored', 'pending_putaway'], true) && ($policy?->require_pallet || $settings->require_pallet) && ! $palletId) {
                throw ValidationException::withMessages([
                    'pallet_id' => 'Sản phẩm này bắt buộc gắn pallet trước khi lưu.',
                ]);
            }

            $storageItem->warehouse_location_id = $locationId;
            $storageItem->pallet_id = $palletId;
            $storageItem->status = $status;
            $storageItem->note = Arr::get($data, 'note', $storageItem->note);
            $storageItem->meta_json = array_merge($storageItem->meta_json ?? [], Arr::get($data, 'meta_json', []));

            $netQty = $this->netQuantity($storageItem);

            if ($status === 'stored') {
                $storageItem->stored_at = now();
                $storageItem->putaway_at = $storageItem->putaway_at ?: now();
                $storageItem->qc_hold_qty = 0;
                $storageItem->available_qty = $netQty;
            } elseif ($status === 'qc_hold') {
                $storageItem->qc_at = now();
                $storageItem->qc_hold_qty = $netQty;
                $storageItem->available_qty = 0;
            } elseif (in_array($status, ['receiving', 'pending_putaway', 'damaged', 'rejected', 'closed'], true)) {
                $storageItem->available_qty = 0;

                if ($status === 'closed') {
                    $storageItem->closed_at = now();
                }
            }

            $storageItem->save();

            return $storageItem->refresh();
        });
    }

    protected function buildItemsForReceiptItem(
        GoodsReceipt $goodsReceipt,
        GoodsReceiptItem $item,
        WarehouseSetting $settings,
        ?WarehouseProductPolicy $policy
    ): Collection {
        $items = collect();
        $batches = $item->batches->isNotEmpty() ? $item->batches : collect([null]);
        $useQc = $policy?->require_qc ?? $settings->use_qc;

        foreach ($batches as $batch) {
            $attributes = [
                'goods_receipt_id' => $goodsReceipt->getKey(),
                'goods_receipt_item_id' => $item->getKey(),
                'goods_receipt_batch_id' => $batch?->getKey(),
            ];

            $existing = ReceiptStorageItem::query()->where($attributes)->first();

            if ($existing) {
                $items->push($existing);

                continue;
            }

            $receivedQty = (float) ($batch?->received_qty ?? $item->received_qty ?? $item->ordered_qty ?? 0);
            $status = $useQc ? 'qc_hold' : 'receiving';

            $items->push(ReceiptStorageItem::query()->create([
                'goods_receipt_id' => $goodsReceipt->getKey(),
                'goods_receipt_item_id' => $item->getKey(),
                'goods_receipt_batch_id' => $batch?->getKey(),
                'warehouse_id' => $goodsReceipt->warehouse_id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'tracking_type' => $batch?->serial_no ? 'serial' : ($batch?->batch_no ? 'batch' : 'none'),
                'status' => $status,
                'received_qty' => $receivedQty,
                'available_qty' => $useQc ? 0 : $receivedQty,
                'qc_hold_qty' => $useQc ? $receivedQty : 0,
                'damaged_qty' => 0,
                'rejected_qty' => 0,
                'received_at' => now(),
                'qc_at' => $useQc ? now() : null,
                'note' => $item->note,
                'created_by' => auth()->id(),
            ]));
        }

        return $items;
    }

    protected function resolvePolicy(GoodsReceipt $goodsReceipt, GoodsReceiptItem $item): ?WarehouseProductPolicy
    {
        $warehouseProduct = WarehouseProduct::query()
            ->with('policy')
            ->where('warehouse_id', $goodsReceipt->warehouse_id)
            ->where('product_id', $item->product_id)
            ->when(
                $item->product_variation_id,
                fn ($query) => $query->where('product_variation_id', $item->product_variation_id),
                fn ($query) => $query->whereNull('product_variation_id')
            )
            ->first();

        return $warehouseProduct?->policy;
    }

    protected function resolvePolicyForStorageItem(ReceiptStorageItem $storageItem): ?WarehouseProductPolicy
    {
        $warehouseProduct = WarehouseProduct::query()
            ->with('policy')
            ->where('warehouse_id', $storageItem->warehouse_id)
            ->where('product_id', $storageItem->product_id)
            ->when(
                $storageItem->product_variation_id,
                fn ($query) => $query->where('product_variation_id', $storageItem->product_variation_id),
                fn ($query) => $query->whereNull('product_variation_id')
            )
            ->first();

        return $warehouseProduct?->policy;
    }

    protected function ensureLocationAllowed(ReceiptStorageItem $storageItem, int $locationId): WarehouseLocation
    {
        $location = WarehouseLocation::query()
            ->whereKey($locationId)
            ->where('warehouse_id', $storageItem->warehouse_id)
            ->first();

        if (! $location) {
            throw ValidationException::withMessages([
                'warehouse_location_id' => 'Vị trí không hợp lệ cho kho này.',
            ]);
        }

        if (! PalletLocationRules::isAllowed($location->type)) {
            throw ValidationException::withMessages([
                'warehouse_location_id' => 'Vị trí này chưa hỗ trợ đặt pallet hoặc storage item.',
            ]);
        }

        return $location;
    }

    protected function ensurePalletAllowed(ReceiptStorageItem $storageItem, int $palletId, ?int $locationId): Pallet
    {
        $pallet = Pallet::query()
            ->whereKey($palletId)
            ->where('warehouse_id', $storageItem->warehouse_id)
            ->first();

        if (! $pallet) {
            throw ValidationException::withMessages([
                'pallet_id' => 'Pallet không hợp lệ cho kho này.',
            ]);
        }

        if ($locationId && (int) $pallet->current_location_id !== $locationId) {
            throw ValidationException::withMessages([
                'pallet_id' => 'Pallet phải nằm đúng vị trí kho được chọn.',
            ]);
        }

        return $pallet;
    }

    protected function netQuantity(ReceiptStorageItem $storageItem): float
    {
        return max(
            (float) $storageItem->received_qty
            - (float) $storageItem->damaged_qty
            - (float) $storageItem->rejected_qty,
            0
        );
    }
}
