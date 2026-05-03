<?php

namespace Botble\Inventory\Domains\Packing\Services;

use Botble\Inventory\Domains\Packing\DTO\PackingDTO;
use Botble\Inventory\Domains\Packing\Models\Package;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Models\PackingListItem;
use Botble\Inventory\Domains\Packing\Repositories\Interfaces\PackingInterface;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Botble\Inventory\Domains\Warehouse\Services\StockLedgerService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PackingService
{
    public function __construct(
        protected PackingInterface $packings,
        protected StockLedgerService $stockLedger,
    ) {
    }

    public function create(PackingDTO $dto): PackingList
    {
        return DB::transaction(function () use ($dto): PackingList {
            $dto = $this->normalizeStatusByCompleteness($dto);

            $this->validatePayload($dto);

            $packingList = $this->packings->createList($this->prepareListAttributes($dto));
            $newExportItemIds = $this->syncPackages($packingList, $dto);

            $this->packings->syncPackedQuantitiesForExportItems($newExportItemIds);
            $this->logCompleteIfNeeded($packingList, null);
            $this->postPackedLedgerIfNeeded($packingList, null);

            return $this->packings->reload($packingList, [
                'export.items',
                'warehouse',
                'packages.items.exportItem',
                'logs',
            ]);
        });
    }

    public function update(PackingList $packingList, PackingDTO $dto): PackingList
    {
        return DB::transaction(function () use ($packingList, $dto): PackingList {
            $oldStatus = (string) $packingList->status;

            if ($oldStatus === 'packed') {
                throw ValidationException::withMessages([
                    'status' => 'Phiếu đóng gói đã packed, không thể sửa trực tiếp. Cần tạo nghiệp vụ hủy/đảo ledger riêng.',
                ]);
            }

            $dto = $this->normalizeStatusByCompleteness($dto, $packingList);

            $this->validatePayload($dto, $packingList);

            $oldExportItemIds = array_keys($this->packings->packedQuantitiesForList($packingList));

            $this->packings->updateList($packingList, $this->prepareListAttributes($dto, $packingList));
            $this->packings->deleteChildren($packingList);

            $newExportItemIds = $this->syncPackages($packingList, $dto);
            $this->packings->syncPackedQuantitiesForExportItems(array_merge($oldExportItemIds, $newExportItemIds));
            $this->logCompleteIfNeeded($packingList, $oldStatus);
            $this->postPackedLedgerIfNeeded($packingList, $oldStatus);

            return $this->packings->reload($packingList, [
                'export.items',
                'warehouse',
                'packages.items.exportItem',
                'logs',
            ]);
        });
    }

    protected function normalizeStatusByCompleteness(PackingDTO $dto, ?PackingList $packingList = null): PackingDTO
    {
        $status = (string) Arr::get($dto->attributes, 'status', 'draft');

        if ($status === 'cancelled') {
            return $dto;
        }

        $attributes = $dto->attributes;

        if ($this->payloadCoversExportItems($dto, $packingList)) {
            $attributes['status'] = 'packed';

            return new PackingDTO($attributes, $dto->packages);
        }

        $attributes['status'] = 'draft';
        $attributes['started_at'] = null;
        $attributes['packed_at'] = null;
        $attributes['completed_at'] = null;

        return new PackingDTO($attributes, $dto->packages);
    }

    protected function payloadCoversExportItems(PackingDTO $dto, ?PackingList $packingList = null): bool
    {
        $exportId = (int) Arr::get($dto->attributes, 'export_id');

        if (! $exportId) {
            return false;
        }

        $exportItems = ExportItem::query()
            ->where('export_id', $exportId)
            ->get(['id', 'document_qty'])
            ->keyBy('id');

        if ($exportItems->isEmpty()) {
            return false;
        }

        $requestedQuantities = $this->requestedQuantitiesByExportItem($dto);

        if ($requestedQuantities === []) {
            return false;
        }

        $alreadyPackedQuantities = $this->alreadyPackedQuantities($exportItems->keys()->all(), $packingList);

        foreach ($exportItems as $exportItemId => $exportItem) {
            $documentQty = (float) ($exportItem->document_qty ?? 0);

            if ($documentQty <= 0) {
                continue;
            }

            $alreadyPackedQty = (float) $alreadyPackedQuantities->get($exportItemId, 0);
            $requestedQty = (float) ($requestedQuantities[(int) $exportItemId] ?? 0);

            if ($alreadyPackedQty + $requestedQty + 0.0001 < $documentQty) {
                return false;
            }
        }

        return true;
    }

    public function delete(PackingList $packingList): bool
    {
        return DB::transaction(function () use ($packingList): bool {
            if ((string) $packingList->status === 'packed') {
                throw ValidationException::withMessages([
                    'status' => 'Khong the xoa phieu dong goi da packed.',
                ]);
            }

            $oldExportItemIds = array_keys($this->packings->packedQuantitiesForList($packingList));

            $this->packings->deleteChildren($packingList);
            $deleted = $this->packings->deleteList($packingList);
            $this->packings->syncPackedQuantitiesForExportItems($oldExportItemIds);

            return $deleted;
        });
    }

    protected function validatePayload(PackingDTO $dto, ?PackingList $packingList = null): void
    {
        $exportId = (int) Arr::get($dto->attributes, 'export_id');
        $warehouseId = (int) Arr::get($dto->attributes, 'warehouse_id');
        $status = (string) Arr::get($dto->attributes, 'status', 'draft');

        $export = Export::query()->find($exportId);

        if (! $export) {
            throw ValidationException::withMessages([
                'export_id' => 'Phiếu xuất không tồn tại.',
            ]);
        }

        $activePacking = PackingList::query()
            ->where('export_id', $exportId)
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'cancelled'))
            ->when($packingList, fn ($query) => $query->whereKeyNot($packingList->getKey()))
            ->first(['id', 'code', 'status']);

        if ($activePacking) {
            throw ValidationException::withMessages([
                'export_id' => sprintf(
                    'Phieu xuat nay da co phieu dong goi %s (%s). Vui long mo phieu do de sua, khong tao lai.',
                    $activePacking->code ?: 'PACK-' . $activePacking->getKey(),
                    $activePacking->status ?: 'draft'
                ),
            ]);
        }

        if (! inventory_is_super_admin()) {
            $warehouseIds = array_values(array_map('intval', inventory_warehouse_ids()));

            if ($export->warehouse_id && ! in_array((int) $export->warehouse_id, $warehouseIds, true)) {
                throw ValidationException::withMessages([
                    'export_id' => 'Bạn không có quyền đóng gói phiếu xuất của kho này.',
                ]);
            }
        }

        if ($export->warehouse_id && $warehouseId && (int) $export->warehouse_id !== $warehouseId) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Kho đóng gói phải trùng với kho xuất của phiếu xuất.',
            ]);
        }

        $requestedQuantities = $this->requestedQuantitiesByExportItem($dto);

        if ($status === 'packed' && $requestedQuantities === []) {
            throw ValidationException::withMessages([
                'packages' => 'Phiếu packed phải có ít nhất một dòng hàng đóng gói.',
            ]);
        }

        if ($status === 'packed') {
            $this->validatePackedPackages($dto);
        }

        if ($requestedQuantities === []) {
            return;
        }

        $exportItems = ExportItem::query()
            ->where('export_id', $exportId)
            ->whereIn('id', array_keys($requestedQuantities))
            ->get()
            ->keyBy('id');

        $alreadyPackedQuantities = $this->alreadyPackedQuantities(array_keys($requestedQuantities), $packingList);

        foreach ($requestedQuantities as $exportItemId => $qty) {
            if (! $exportItems->has($exportItemId)) {
                throw ValidationException::withMessages([
                    'packages' => 'Dòng phiếu xuất không thuộc phiếu xuất đang chọn.',
                ]);
            }

            $documentQty = (float) ($exportItems->get($exportItemId)->document_qty ?? 0);
            $alreadyPackedQty = (float) $alreadyPackedQuantities->get($exportItemId, 0);
            $allowedQty = max($documentQty - $alreadyPackedQty, 0);

            if ($qty > $allowedQty + 0.0001) {
                throw ValidationException::withMessages([
                    'packages' => sprintf('Dòng phiếu xuất #%s chỉ còn được đóng %.4f, nhưng đang nhập %.4f.', $exportItemId, $allowedQty, $qty),
                ]);
            }
        }
    }

    protected function validatePackedPackages(PackingDTO $dto): void
    {
        foreach ($dto->packages as $packageIndex => $package) {
            $items = array_filter(
                Arr::get($package, 'items', []),
                fn (array $item): bool => (float) Arr::get($item, 'packed_qty', 0) > 0
            );

            if ($items === []) {
                throw ValidationException::withMessages([
                    sprintf('packages.%s.items', $packageIndex) => 'Moi kien hang phai chon it nhat mot san pham truoc khi hoan tat dong goi.',
                ]);
            }
        }
    }

    protected function requestedQuantitiesByExportItem(PackingDTO $dto): array
    {
        $quantities = [];

        foreach ($dto->packages as $packageIndex => $package) {
            foreach (Arr::get($package, 'items', []) as $itemIndex => $item) {
                $qty = (float) Arr::get($item, 'packed_qty', 0);

                if ($qty <= 0) {
                    continue;
                }

                $exportItemId = Arr::get($item, 'export_item_id');

                if (! $exportItemId) {
                    throw ValidationException::withMessages([
                        sprintf('packages.%s.items.%s.export_item_id', $packageIndex, $itemIndex) => 'Chọn dòng phiếu xuất trước khi đóng gói.',
                    ]);
                }

                $exportItemId = (int) $exportItemId;
                $quantities[$exportItemId] = ($quantities[$exportItemId] ?? 0) + $qty;
            }
        }

        return $quantities;
    }

    protected function alreadyPackedQuantities(array $exportItemIds, ?PackingList $packingList = null): \Illuminate\Support\Collection
    {
        $exportItemIds = array_values(array_unique(array_filter(array_map('intval', $exportItemIds))));

        if ($exportItemIds === []) {
            return collect();
        }

        return DB::table('inv_packing_list_items as items')
            ->join('inv_packing_lists as lists', 'lists.id', '=', 'items.packing_list_id')
            ->whereIn('items.export_item_id', $exportItemIds)
            ->whereNull('lists.deleted_at')
            ->whereIn('lists.status', ['packing', 'packed'])
            ->when($packingList, fn ($query) => $query->where('lists.id', '!=', $packingList->getKey()))
            ->selectRaw('items.export_item_id as export_item_id, SUM(items.packed_qty) as packed_qty')
            ->groupBy('items.export_item_id')
            ->pluck('packed_qty', 'export_item_id')
            ->map(fn ($value): float => (float) $value);
    }

    protected function prepareListAttributes(PackingDTO $dto, ?PackingList $packingList = null): array
    {
        $attributes = Arr::only($dto->attributes, [
            'code',
            'export_id',
            'warehouse_id',
            'status',
            'packer_id',
            'packed_at',
            'started_at',
            'completed_at',
            'cancelled_at',
            'cancelled_by',
            'cancelled_reason',
            'note',
        ]);

        $totals = $this->calculateTotals($dto->packages);
        $status = (string) Arr::get($attributes, 'status', 'draft');

        $attributes = array_merge($attributes, $totals);

        if ($status === 'packing' && ! Arr::get($attributes, 'started_at')) {
            $attributes['started_at'] = $packingList?->started_at ?: now();
        }

        if ($status === 'packed') {
            $attributes['started_at'] = Arr::get($attributes, 'started_at') ?: $packingList?->started_at ?: now();
            $attributes['packed_at'] = Arr::get($attributes, 'packed_at') ?: $packingList?->packed_at ?: now();
            $attributes['completed_at'] = Arr::get($attributes, 'completed_at') ?: $packingList?->completed_at ?: now();
        }

        if ($status === 'cancelled') {
            $attributes['cancelled_at'] = Arr::get($attributes, 'cancelled_at') ?: $packingList?->cancelled_at ?: now();
            $attributes['cancelled_by'] = Arr::get($attributes, 'cancelled_by') ?: auth()->id();
        }

        return $attributes;
    }

    protected function calculateTotals(array $packages): array
    {
        $totalItems = 0.0;
        $totalWeight = 0.0;
        $totalVolume = 0.0;

        foreach ($packages as $package) {
            $totalWeight += (float) Arr::get($package, 'weight', 0);
            $totalVolume += (float) Arr::get($package, 'volume', 0);

            foreach (Arr::get($package, 'items', []) as $item) {
                $totalItems += (float) Arr::get($item, 'packed_qty', 0);
            }
        }

        return [
            'total_packages' => count($packages),
            'total_items' => $totalItems,
            'total_weight' => $totalWeight,
            'total_volume' => $totalVolume,
        ];
    }

    protected function syncPackages(PackingList $packingList, PackingDTO $dto): array
    {
        $exportItemIds = $this->collectExportItemIds($dto);
        $productIds = $this->collectProductIds($dto);
        $exportItemsById = $this->packings->exportItemsByIds($exportItemIds);
        $exportItemsByProductId = $this->packings->exportItemsByProductIds((int) $packingList->export_id, $productIds);
        $syncedExportItemIds = [];

        foreach ($dto->packages as $packageData) {
            $package = $this->packings->createPackage($packingList, $this->packageAttributes($packingList, $packageData));

            $this->packings->createLog($packingList, [
                'package_id' => $package->getKey(),
                'action' => 'create_package',
                'note' => $package->package_code,
            ]);

            foreach (Arr::get($packageData, 'items', []) as $itemData) {
                $exportItem = null;

                if ($exportItemId = Arr::get($itemData, 'export_item_id')) {
                    $exportItem = $exportItemsById->get($exportItemId);

                    if ($exportItem && (int) $exportItem->export_id !== (int) $packingList->export_id) {
                        $exportItem = null;
                    }
                }

                if (! $exportItem && ($productId = Arr::get($itemData, 'product_id'))) {
                    $exportItem = $exportItemsByProductId->get($productId);
                }

                $item = $this->packings->createItem(
                    $packingList,
                    $package,
                    $this->itemAttributes($itemData, $exportItem)
                );

                if ($item->export_item_id) {
                    $syncedExportItemIds[] = (int) $item->export_item_id;
                }

                $this->logAddItem($packingList, $package, $item);
            }
        }

        return array_values(array_unique($syncedExportItemIds));
    }

    protected function packageAttributes(PackingList $packingList, array $packageData): array
    {
        $attributes = Arr::only($packageData, [
            'package_code',
            'package_no',
            'package_type_id',
            'status',
            'length',
            'width',
            'height',
            'dimension_unit',
            'volume',
            'volume_weight',
            'weight',
            'weight_unit',
            'tracking_code',
            'shipping_label_url',
            'note',
        ]);

        if (! Arr::get($attributes, 'package_code')) {
            $attributes['package_code'] = substr(sprintf(
                '%s-PKG-%s',
                $packingList->code ?: 'PACK-' . $packingList->getKey(),
                str_pad((string) Arr::get($attributes, 'package_no', 1), 3, '0', STR_PAD_LEFT)
            ), 0, 120);
        }

        return $attributes;
    }

    protected function itemAttributes(array $itemData, mixed $exportItem = null): array
    {
        return [
            'export_item_id' => $exportItem?->getKey(),
            'product_id' => Arr::get($itemData, 'product_id') ?: $exportItem?->product_id,
            'product_variation_id' => Arr::get($itemData, 'product_variation_id') ?: $exportItem?->product_variation_id,
            'product_code' => Arr::get($itemData, 'product_code') ?: $exportItem?->product_code,
            'product_name' => Arr::get($itemData, 'product_name') ?: $exportItem?->product_name,
            'packed_qty' => (float) Arr::get($itemData, 'packed_qty', 0),
            'unit_id' => Arr::get($itemData, 'unit_id') ?: $exportItem?->unit_id,
            'unit_name' => Arr::get($itemData, 'unit_name') ?: $exportItem?->unit_name,
            'warehouse_location_id' => Arr::get($itemData, 'warehouse_location_id') ?: $exportItem?->warehouse_location_id,
            'pallet_id' => Arr::get($itemData, 'pallet_id') ?: $exportItem?->pallet_id,
            'batch_id' => Arr::get($itemData, 'batch_id') ?: $exportItem?->batch_id,
            'goods_receipt_batch_id' => Arr::get($itemData, 'goods_receipt_batch_id') ?: $exportItem?->goods_receipt_batch_id,
            'stock_balance_id' => Arr::get($itemData, 'stock_balance_id') ?: $exportItem?->stock_balance_id,
            'storage_item_id' => Arr::get($itemData, 'storage_item_id'),
            'lot_no' => Arr::get($itemData, 'lot_no') ?: $exportItem?->lot_no,
            'expiry_date' => Arr::get($itemData, 'expiry_date') ?: $exportItem?->expiry_date,
            'note' => Arr::get($itemData, 'note'),
        ];
    }

    protected function logAddItem(PackingList $packingList, Package $package, PackingListItem $item): void
    {
        $this->packings->createLog($packingList, [
            'package_id' => $package->getKey(),
            'export_item_id' => $item->export_item_id,
            'product_id' => $item->product_id,
            'product_variation_id' => $item->product_variation_id,
            'action' => 'add_item',
            'new_qty' => (float) $item->packed_qty,
            'note' => $item->note,
        ]);
    }

    protected function logCompleteIfNeeded(PackingList $packingList, ?string $oldStatus): void
    {
        if ((string) $packingList->status !== 'packed' || $oldStatus === 'packed') {
            return;
        }

        $this->packings->createLog($packingList, [
            'action' => 'complete_packing',
            'new_qty' => (float) $packingList->total_items,
        ]);
    }

    protected function postPackedLedgerIfNeeded(PackingList $packingList, ?string $oldStatus): void
    {
        if ((string) $packingList->status !== 'packed' || $oldStatus === 'packed') {
            return;
        }

        $packingList->loadMissing('packages.items');

        foreach ($packingList->packages as $package) {
            foreach ($package->items as $item) {
                $this->stockLedger->postPackingListItem($item, 'packing_packed');
            }
        }
    }

    protected function collectExportItemIds(PackingDTO $dto): array
    {
        $ids = [];

        foreach ($dto->packages as $package) {
            foreach (Arr::get($package, 'items', []) as $item) {
                if ($id = Arr::get($item, 'export_item_id')) {
                    $ids[] = (int) $id;
                }
            }
        }

        return $ids;
    }

    protected function collectProductIds(PackingDTO $dto): array
    {
        $ids = [];

        foreach ($dto->packages as $package) {
            foreach (Arr::get($package, 'items', []) as $item) {
                if ($id = Arr::get($item, 'product_id')) {
                    $ids[] = (int) $id;
                }
            }
        }

        return $ids;
    }
}
