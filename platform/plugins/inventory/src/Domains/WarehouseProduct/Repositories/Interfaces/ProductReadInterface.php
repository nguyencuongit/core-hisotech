<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces;

use Botble\Ecommerce\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductReadInterface
{
    public function findOrFail(int $id): Product;

    public function exists(int $id): bool;

    public function searchAvailableForWarehouse(int $warehouseId, string $query, array $excludedProductIds = [], int $limit = 20): Collection;

    public function paginateCatalog(string $status, string $query, bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId, int $perPage = 20): LengthAwarePaginator;

    public function productsForToggle(): Collection;

    public function totalCount(): int;

    public function countWithWarehouse(bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId): int;

    public function countWithoutWarehouse(): int;
}
