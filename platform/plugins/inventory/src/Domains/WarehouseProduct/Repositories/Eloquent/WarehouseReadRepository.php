<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WarehouseReadRepository implements WarehouseReadInterface
{
    public function findOrFail(int $id): Warehouse
    {
        return Warehouse::query()->findOrFail($id);
    }

    public function findSummary(?int $id): ?Warehouse
    {
        if (! $id || $id < 1) {
            return null;
        }

        return Warehouse::query()
            ->select(['id', 'code', 'name'])
            ->find($id);
    }

    public function findWithSetting(int $id): Warehouse
    {
        return Warehouse::query()
            ->with('setting')
            ->findOrFail($id);
    }

    public function listForScope(bool $isSuperAdmin, array $allowedWarehouseIds): Collection
    {
        return Warehouse::query()
            ->select(['id', 'code', 'name'])
            ->when(! $isSuperAdmin, fn (Builder $query) => $query->whereIn('id', $allowedWarehouseIds))
            ->orderBy('name')
            ->get();
    }
}
