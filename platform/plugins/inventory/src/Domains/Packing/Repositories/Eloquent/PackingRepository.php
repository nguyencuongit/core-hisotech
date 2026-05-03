<?php

namespace Botble\Inventory\Domains\Packing\Repositories\Eloquent;

use Botble\Inventory\Domains\Packing\Models\Package;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Models\PackingListItem;
use Botble\Inventory\Domains\Packing\Models\PackingLog;
use Botble\Inventory\Domains\Packing\Repositories\Interfaces\PackingInterface;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class PackingRepository implements PackingInterface
{
    public function findOrFail(int|string $id): PackingList
    {
        return PackingList::query()->findOrFail($id);
    }

    public function loadForEdit(PackingList $packingList): PackingList
    {
        return $packingList->load([
            'export.items',
            'warehouse',
            'packages.items.product',
            'packages.items.exportItem',
        ]);
    }

    public function reload(PackingList $packingList, array $with = []): PackingList
    {
        $fresh = $packingList->fresh($with);

        return $fresh ?: $packingList->load($with);
    }

    public function createList(array $attributes): PackingList
    {
        return PackingList::query()->create($attributes);
    }

    public function updateList(PackingList $packingList, array $attributes): PackingList
    {
        $packingList->fill($attributes);
        $packingList->save();

        return $packingList;
    }

    public function deleteList(PackingList $packingList): bool
    {
        return (bool) $packingList->delete();
    }

    public function deleteChildren(PackingList $packingList): void
    {
        PackingListItem::query()
            ->where('packing_list_id', $packingList->getKey())
            ->delete();

        Package::withTrashed()
            ->where('packing_list_id', $packingList->getKey())
            ->forceDelete();
    }

    public function createPackage(PackingList $packingList, array $attributes): Package
    {
        return $packingList->packages()->create($attributes);
    }

    public function createItem(PackingList $packingList, Package $package, array $attributes): PackingListItem
    {
        return PackingListItem::query()->create(array_merge($attributes, [
            'packing_list_id' => $packingList->getKey(),
            'package_id' => $package->getKey(),
            'packing_id' => $package->getKey(),
        ]));
    }

    public function createLog(PackingList $packingList, array $attributes): PackingLog
    {
        return PackingLog::query()->create(array_merge([
            'packing_list_id' => $packingList->getKey(),
            'export_id' => $packingList->export_id,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ], $attributes));
    }

    public function exportItemsByIds(array $ids): SupportCollection
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return collect();
        }

        return ExportItem::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    public function exportItemsByProductIds(int $exportId, array $productIds): SupportCollection
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));

        if ($productIds === []) {
            return collect();
        }

        return ExportItem::query()
            ->where('export_id', $exportId)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');
    }

    public function exportItemsForExport(int $exportId): Collection
    {
        return ExportItem::query()
            ->where('export_id', $exportId)
            ->orderBy('id')
            ->get();
    }

    public function packedQuantitiesForList(PackingList $packingList): array
    {
        return PackingListItem::query()
            ->where('packing_list_id', $packingList->getKey())
            ->whereNotNull('export_item_id')
            ->selectRaw('export_item_id, SUM(packed_qty) as packed_qty')
            ->groupBy('export_item_id')
            ->pluck('packed_qty', 'export_item_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    public function syncPackedQuantitiesForExportItems(array $exportItemIds): void
    {
        $exportItemIds = array_values(array_unique(array_filter(array_map('intval', $exportItemIds))));

        if ($exportItemIds === []) {
            return;
        }

        $totals = DB::table('inv_packing_list_items as items')
            ->join('inv_packing_lists as lists', 'lists.id', '=', 'items.packing_list_id')
            ->whereIn('items.export_item_id', $exportItemIds)
            ->whereNull('lists.deleted_at')
            ->whereIn('lists.status', ['packing', 'packed'])
            ->selectRaw('items.export_item_id as export_item_id, SUM(items.packed_qty) as packed_qty')
            ->groupBy('items.export_item_id')
            ->pluck('packed_qty', 'export_item_id')
            ->map(fn ($value) => (float) $value);

        ExportItem::query()
            ->whereIn('id', $exportItemIds)
            ->get()
            ->each(function (ExportItem $exportItem) use ($totals): void {
                $this->updateExportItemProgress(
                    $exportItem,
                    (float) ($totals->get($exportItem->getKey()) ?? 0)
                );
            });
    }

    public function updateExportItemProgress(ExportItem $exportItem, float $packedQty): ExportItem
    {
        $exportItem->packed_qty = $packedQty;

        if ((float) ($exportItem->picked_qty ?? 0) < $packedQty) {
            $exportItem->picked_qty = $packedQty;
        }

        if ((float) ($exportItem->reserved_qty ?? 0) < (float) $exportItem->picked_qty) {
            $exportItem->reserved_qty = (float) $exportItem->picked_qty;
        }

        $exportItem->save();

        return $exportItem;
    }
}
