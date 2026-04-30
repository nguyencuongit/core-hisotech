<?php

namespace Botble\Inventory\Domains\Packing\Repositories\Interfaces;

use Botble\Inventory\Domains\Packing\Models\Package;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Models\PackingListItem;
use Botble\Inventory\Domains\Packing\Models\PackingLog;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface PackingInterface
{
    public function findOrFail(int|string $id): PackingList;

    public function loadForEdit(PackingList $packingList): PackingList;

    public function reload(PackingList $packingList, array $with = []): PackingList;

    public function createList(array $attributes): PackingList;

    public function updateList(PackingList $packingList, array $attributes): PackingList;

    public function deleteList(PackingList $packingList): bool;

    public function deleteChildren(PackingList $packingList): void;

    public function createPackage(PackingList $packingList, array $attributes): Package;

    public function createItem(PackingList $packingList, Package $package, array $attributes): PackingListItem;

    public function createLog(PackingList $packingList, array $attributes): PackingLog;

    public function exportItemsByIds(array $ids): SupportCollection;

    public function exportItemsByProductIds(int $exportId, array $productIds): SupportCollection;

    public function exportItemsForExport(int $exportId): Collection;

    public function packedQuantitiesForList(PackingList $packingList): array;

    public function syncPackedQuantitiesForExportItems(array $exportItemIds): void;

    public function updateExportItemProgress(ExportItem $exportItem, float $packedQty): ExportItem;
}
