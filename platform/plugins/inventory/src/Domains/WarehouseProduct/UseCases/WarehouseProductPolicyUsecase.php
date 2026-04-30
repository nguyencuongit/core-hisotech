<?php

namespace Botble\Inventory\Domains\WarehouseProduct\UseCases;

use Botble\Inventory\Domains\WarehouseProduct\Actions\SaveWarehouseProductPolicyAction;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductPolicyDTO;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProductPolicy;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductPolicyInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductService;

class WarehouseProductPolicyUsecase
{
    public function __construct(
        protected WarehouseReadInterface $warehouses,
        protected WarehouseProductInterface $warehouseProducts,
        protected WarehouseProductPolicyInterface $policies,
        protected WarehouseProductService $warehouseProductService,
        protected SaveWarehouseProductPolicyAction $saveAction,
    ) {
    }

    public function store(int $warehouseId, int $warehouseProductId, WarehouseProductPolicyDTO $dto): WarehouseProductPolicy
    {
        $warehouse = $this->warehouses->findOrFail($warehouseId);
        $warehouseProduct = $this->warehouseProducts->findOrFail($warehouseProductId);
        $this->warehouseProductService->ensureWarehouseProductBelongsToWarehouse($warehouse, $warehouseProduct);

        return $this->saveAction->execute($warehouseProduct, $dto);
    }

    public function update(int $warehouseId, int $warehouseProductId, int $policyId, WarehouseProductPolicyDTO $dto): WarehouseProductPolicy
    {
        $warehouse = $this->warehouses->findOrFail($warehouseId);
        $warehouseProduct = $this->warehouseProducts->findOrFail($warehouseProductId);
        $this->warehouseProductService->ensureWarehouseProductBelongsToWarehouse($warehouse, $warehouseProduct);

        if (! $this->policies->belongsToWarehouseProduct($policyId, $warehouseProduct)) {
            abort(404);
        }

        return $this->saveAction->execute($warehouseProduct, $dto);
    }
}
