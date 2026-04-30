<?php

namespace Botble\Inventory\Domains\WarehouseProduct\UseCases;

use Botble\Inventory\Domains\WarehouseProduct\Actions\CreateWarehouseProductAction;
use Botble\Inventory\Domains\WarehouseProduct\Actions\DeleteWarehouseProductAction;
use Botble\Inventory\Domains\WarehouseProduct\Actions\UpdateWarehouseProductAction;
use Botble\Inventory\Domains\WarehouseProduct\DTO\SupplierProductSuggestionDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductSearchDTO;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductService;

class WarehouseProductUsecase
{
    public function __construct(
        protected WarehouseReadInterface $warehouses,
        protected WarehouseProductInterface $warehouseProducts,
        protected WarehouseProductService $service,
        protected CreateWarehouseProductAction $createAction,
        protected UpdateWarehouseProductAction $updateAction,
        protected DeleteWarehouseProductAction $deleteAction,
    ) {
    }

    public function create(int $warehouseId, WarehouseProductDTO $dto): WarehouseProduct
    {
        return $this->createAction->execute($this->warehouses->findOrFail($warehouseId), $dto);
    }

    public function update(int $warehouseId, int $warehouseProductId, WarehouseProductDTO $dto): WarehouseProduct
    {
        return $this->updateAction->execute(
            $this->warehouses->findOrFail($warehouseId),
            $this->warehouseProducts->findOrFail($warehouseProductId),
            $dto
        );
    }

    public function delete(int $warehouseId, int $warehouseProductId): bool
    {
        return $this->deleteAction->execute(
            $this->warehouses->findOrFail($warehouseId),
            $this->warehouseProducts->findOrFail($warehouseProductId)
        );
    }

    public function searchProducts(int $warehouseId, WarehouseProductSearchDTO $dto): array
    {
        return $this->service->searchProducts($this->warehouses->findOrFail($warehouseId), $dto);
    }

    public function supplierProductSuggestion(SupplierProductSuggestionDTO $dto): ?array
    {
        return $this->service->supplierProductSuggestion($dto);
    }
}
