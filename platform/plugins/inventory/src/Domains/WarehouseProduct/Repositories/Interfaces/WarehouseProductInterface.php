<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Support\Collection;

interface WarehouseProductInterface extends RepositoryInterface
{
    public function findOrFail($id, array $with = []): WarehouseProduct;

    public function createForWarehouse(Warehouse $warehouse, array $data): WarehouseProduct;

    public function updateWarehouseProduct(WarehouseProduct $warehouseProduct, array $data): WarehouseProduct;

    public function deleteWarehouseProduct(WarehouseProduct $warehouseProduct): bool;

    public function deactivate(WarehouseProduct $warehouseProduct): bool;

    public function belongsToWarehouse(WarehouseProduct $warehouseProduct, Warehouse $warehouse): bool;

    public function existsDuplicate(Warehouse $warehouse, int $productId, ?int $productVariationId = null, ?WarehouseProduct $ignore = null): bool;

    public function updateOrCreateBaseAssignment(Warehouse $warehouse, Product $product): WarehouseProduct;

    public function findBaseAssignment(Warehouse $warehouse, Product $product, bool $activeOnly = false): ?WarehouseProduct;

    public function configuredProductIds(int $warehouseId): array;

    public function activeProductIdsForWarehouse(int $warehouseId): array;

    public function activeAssignmentsByProductIds(array $productIds, bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId = null, array $with = []): Collection;

    public function configuredRowsCount(?array $warehouseIds = null): int;
}
