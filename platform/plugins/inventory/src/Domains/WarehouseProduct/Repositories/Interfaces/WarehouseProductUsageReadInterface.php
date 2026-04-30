<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces;

use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;

interface WarehouseProductUsageReadInterface
{
    public function hasUsage(WarehouseProduct $warehouseProduct): bool;
}
