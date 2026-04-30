<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Actions;

use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductPolicyDTO;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProductPolicy;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductPolicyService;

class SaveWarehouseProductPolicyAction
{
    public function __construct(
        protected WarehouseProductPolicyService $service,
    ) {
    }

    public function execute(WarehouseProduct $warehouseProduct, WarehouseProductPolicyDTO $dto): WarehouseProductPolicy
    {
        return $this->service->save($warehouseProduct, $dto);
    }
}
