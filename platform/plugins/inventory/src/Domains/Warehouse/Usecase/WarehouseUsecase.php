<?php

namespace Botble\Inventory\Domains\Warehouse\Usecase;

use Botble\Inventory\Domains\Warehouse\Repositories\Interfaces\WarehouseInterface;
use Illuminate\Support\Collection;

class WarehouseUsecase
{
    public function __construct(
        private WarehouseInterface $warehouseInterface
    ) {
    }

    public function test(): Collection
    {
        return $this->warehouseInterface->test();
    }
}
