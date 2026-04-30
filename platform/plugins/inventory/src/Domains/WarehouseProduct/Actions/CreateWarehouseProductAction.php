<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Actions;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductDTO;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductService;

class CreateWarehouseProductAction
{
    public function __construct(
        protected WarehouseProductService $service,
    ) {
    }

    public function execute(Warehouse $warehouse, WarehouseProductDTO $dto): WarehouseProduct
    {
        return $this->service->create($warehouse, $dto);
    }
}
