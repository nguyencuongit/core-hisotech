<?php

namespace Botble\Inventory\Domains\WarehouseProduct\UseCases;

use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductAssignDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductCatalogFilterDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductToggleDTO;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductCatalogService;
use Botble\Inventory\Domains\WarehouseProduct\Services\WarehouseProductService;

class WarehouseProductCatalogUsecase
{
    public function __construct(
        protected WarehouseProductCatalogService $catalogService,
        protected WarehouseProductService $warehouseProductService,
        protected WarehouseReadInterface $warehouses,
    ) {
    }

    public function list(WarehouseProductCatalogFilterDTO $dto): array
    {
        return $this->catalogService->list($dto);
    }

    public function assignProductToWarehouses(WarehouseProductAssignDTO $dto): void
    {
        $this->assertCanManageWarehouseIds($dto->warehouseIds);

        $this->warehouseProductService->assignProductToWarehouses($dto->productId, $dto->warehouseIds);
    }

    public function toggle(WarehouseProductToggleDTO $dto): ?string
    {
        $this->assertCanManageWarehouseIds([$dto->warehouseId]);
        $warehouse = $this->warehouses->findOrFail($dto->warehouseId);

        if ($dto->isBulk()) {
            $this->warehouseProductService->applyProductChangesForWarehouse(
                $warehouse,
                $dto->addProductIds,
                $dto->removeProductIds
            );

            return null;
        }

        return $this->warehouseProductService->toggleProductInWarehouse((int) $dto->productId, $warehouse);
    }

    protected function assertCanManageWarehouseIds(array $warehouseIds): void
    {
        if (inventory_is_super_admin()) {
            return;
        }

        $allowedWarehouseIds = array_values(array_filter(array_map('intval', inventory_warehouse_ids())));

        foreach (array_unique(array_map('intval', $warehouseIds)) as $warehouseId) {
            abort_unless($warehouseId > 0 && in_array($warehouseId, $allowedWarehouseIds, true), 403);
        }
    }
}
