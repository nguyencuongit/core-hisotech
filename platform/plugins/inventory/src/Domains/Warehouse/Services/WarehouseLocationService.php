<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseLocationService
{
    public function create(Warehouse $warehouse, array $data): WarehouseLocation
    {
        return DB::transaction(function () use ($warehouse, $data): WarehouseLocation {
            $location = new WarehouseLocation();
            $location->fill($this->prepareData($warehouse, $data));
            $location->warehouse_id = $warehouse->getKey();
            $location->parent_id = $this->resolveParentId($warehouse, Arr::get($data, 'parent_id'));
            $this->applyTreeValues($location, $warehouse);
            $location->save();

            return $location->refresh();
        });
    }

    public function update(Warehouse $warehouse, WarehouseLocation $location, array $data): WarehouseLocation
    {
        $this->ensureBelongsToWarehouse($warehouse, $location);

        return DB::transaction(function () use ($warehouse, $location, $data): WarehouseLocation {
            $oldPath = $location->path;
            $oldLevel = (int) $location->level;

            $location->fill($this->prepareData($warehouse, $data));
            $location->parent_id = $this->resolveParentId($warehouse, Arr::get($data, 'parent_id'), $location);
            $this->applyTreeValues($location, $warehouse);
            $location->save();

            $this->rebuildDescendants($warehouse, $location, $oldPath, $oldLevel);

            return $location->refresh();
        });
    }

    public function deactivateOrDelete(Warehouse $warehouse, WarehouseLocation $location): bool
    {
        $this->ensureBelongsToWarehouse($warehouse, $location);

        if ($this->hasUsage($location)) {
            return (bool) $location->update(['status' => false]);
        }

        if ($location->children()->exists()) {
            return (bool) $location->update(['status' => false]);
        }

        return (bool) $location->delete();
    }

    protected function prepareData(Warehouse $warehouse, array $data): array
    {
        return [
            'code' => trim((string) Arr::get($data, 'code')),
            'name' => trim((string) Arr::get($data, 'name')),
            'type' => Arr::get($data, 'type'),
            'status' => (bool) Arr::get($data, 'status', true),
            'description' => Arr::get($data, 'description'),
        ];
    }

    protected function resolveParentId(Warehouse $warehouse, mixed $parentId, ?WarehouseLocation $currentLocation = null): ?int
    {
        if (! $parentId) {
            return null;
        }

        $parentId = (int) $parentId;

        if ($currentLocation && $parentId === (int) $currentLocation->getKey()) {
            throw ValidationException::withMessages([
                'parent_id' => trans('plugins/inventory::inventory.warehouse_location.validation.parent_self'),
            ]);
        }

        $parent = WarehouseLocation::query()
            ->whereKey($parentId)
            ->where('warehouse_id', $warehouse->getKey())
            ->first();

        if (! $parent) {
            throw ValidationException::withMessages([
                'parent_id' => trans('plugins/inventory::inventory.warehouse_location.validation.parent_not_in_warehouse'),
            ]);
        }

        if ($currentLocation && $currentLocation->path && str_starts_with($parent->path . '/', $currentLocation->path . '/')) {
            throw ValidationException::withMessages([
                'parent_id' => trans('plugins/inventory::inventory.warehouse_location.validation.parent_descendant'),
            ]);
        }

        return $parent->getKey();
    }

    protected function applyTreeValues(WarehouseLocation $location, Warehouse $warehouse): void
    {
        if (! $location->parent_id) {
            $location->level = 1;
            $location->path = $location->code;
            return;
        }

        $parent = WarehouseLocation::query()
            ->whereKey($location->parent_id)
            ->where('warehouse_id', $warehouse->getKey())
            ->firstOrFail();

        $location->level = ((int) $parent->level) + 1;
        $location->path = trim($parent->path . '/' . $location->code, '/');
    }

    protected function rebuildDescendants(Warehouse $warehouse, WarehouseLocation $location, string $oldPath, int $oldLevel): void
    {
        $descendants = WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('path', 'like', $oldPath . '/%')
            ->orderByRaw('LENGTH(path) asc')
            ->get();

        foreach ($descendants as $descendant) {
            $suffix = substr($descendant->path, strlen($oldPath));
            $suffix = ltrim((string) $suffix, '/');
            $descendant->path = trim($location->path . '/' . $suffix, '/');
            $descendant->level = $location->level + substr_count($suffix, '/');
            $descendant->save();
        }
    }

    protected function hasUsage(WarehouseLocation $location): bool
    {
        if (Pallet::query()->where('current_location_id', $location->getKey())->exists()) {
            return true;
        }

        if (DB::table('inv_warehouse_map_items')->where('location_id', $location->getKey())->exists()) {
            return true;
        }

        return DB::table('inv_stock_transactions')->where('location_id', $location->getKey())->exists()
            || DB::table('inv_stock_balances')->where('location_id', $location->getKey())->exists();
    }

    protected function ensureBelongsToWarehouse(Warehouse $warehouse, WarehouseLocation $location): void
    {
        if ((int) $location->warehouse_id !== (int) $warehouse->getKey()) {
            abort(404);
        }
    }
}
