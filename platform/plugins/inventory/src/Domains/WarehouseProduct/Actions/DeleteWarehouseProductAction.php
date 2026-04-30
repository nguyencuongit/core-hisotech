<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Actions;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductService;

class DeleteWarehouseProductAction
{
    public function __construct(
        protected WarehouseProductService $service,
    ) {
    }

    public function execute(Warehouse $warehouse, WarehouseProduct $warehouseProduct): bool
    {
        return $this->service->deleteOrDeactivate($warehouse, $warehouseProduct);
    }
}
