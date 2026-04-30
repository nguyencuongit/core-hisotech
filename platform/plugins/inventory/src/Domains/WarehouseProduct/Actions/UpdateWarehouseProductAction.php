<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Actions;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductDTO;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductService;

class UpdateWarehouseProductAction
{
    public function __construct(
        protected WarehouseProductService $service,
    ) {
    }

    public function execute(Warehouse $warehouse, WarehouseProduct $warehouseProduct, WarehouseProductDTO $dto): WarehouseProduct
    {
        return $this->service->update($warehouse, $warehouseProduct, $dto);
    }
}
