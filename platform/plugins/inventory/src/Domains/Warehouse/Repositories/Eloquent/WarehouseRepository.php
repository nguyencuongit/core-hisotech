<?php

namespace Botble\Inventory\Domains\Warehouse\Repositories\Eloquent;

use Botble\Inventory\Domains\Warehouse\Repositories\Interfaces\WarehouseInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WarehouseRepository extends RepositoriesAbstract implements WarehouseInterface
{
    public function query(): Builder
    {
        $query = $this->model->newQuery();
        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('id', $warehouseIds);
        }

        return $query;
    }

    public function test(): Collection
    {
        return $this->query()->get();
    }
}
