<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Services;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductCatalogFilterDTO;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\ProductReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseProductCatalogService
{
    public function __construct(
        protected ProductReadInterface $products,
        protected WarehouseReadInterface $warehouses,
        protected WarehouseProductInterface $warehouseProducts,
    ) {
    }

    public function list(WarehouseProductCatalogFilterDTO $dto): array
    {
        $isSuperAdmin = inventory_is_super_admin();
        $allowedWarehouseIds = $this->allowedWarehouseIds($isSuperAdmin, inventory_warehouse_ids());
        $selectedWarehouseId = $this->selectedWarehouseId($dto->warehouseId, $isSuperAdmin, $allowedWarehouseIds);
        $status = $this->status($dto->status, $isSuperAdmin);

        $products = $this->products->paginateCatalog(
            $status,
            $dto->query,
            $isSuperAdmin,
            $allowedWarehouseIds,
            $selectedWarehouseId
        );

        $this->attachWarehouseProducts($products, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId);

        return [
            'products' => $products,
            'warehouses' => $this->warehouses->listForScope($isSuperAdmin, $allowedWarehouseIds),
            'summary' => $this->summary($isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId),
            'filters' => [
                'q' => $dto->query,
                'status' => $status,
                'warehouse_id' => $selectedWarehouseId,
            ],
            'selectedWarehouse' => $this->warehouses->findSummary($selectedWarehouseId),
            'toggleProducts' => $this->toggleProducts($selectedWarehouseId),
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }

    protected function attachWarehouseProducts(
        LengthAwarePaginator $products,
        bool $isSuperAdmin,
        array $allowedWarehouseIds,
        ?int $selectedWarehouseId
    ): void {
        $productIds = $products->getCollection()->pluck('id')->all();

        if ($productIds === []) {
            return;
        }

        $warehouseProducts = $this->warehouseProducts->activeAssignmentsByProductIds(
            $productIds,
            $isSuperAdmin,
            $allowedWarehouseIds,
            $selectedWarehouseId,
            ['warehouse', 'productVariation', 'supplier']
        );

        $allWarehouseProducts = $this->warehouseProducts->activeAssignmentsByProductIds(
            $productIds,
            $isSuperAdmin,
            $allowedWarehouseIds,
            null,
            ['warehouse']
        );

        $products->getCollection()->each(function (Product $product) use ($warehouseProducts, $allWarehouseProducts): void {
            $product->setRelation('inventoryWarehouseProducts', $warehouseProducts->get($product->getKey(), collect()));
            $product->setRelation('allInventoryWarehouseProducts', $allWarehouseProducts->get($product->getKey(), collect()));
        });
    }

    protected function toggleProducts(?int $selectedWarehouseId): array
    {
        if (! $selectedWarehouseId || $selectedWarehouseId < 1) {
            return [
                'in' => collect(),
                'out' => collect(),
            ];
        }

        $assignedProductIds = $this->warehouseProducts->activeProductIdsForWarehouse($selectedWarehouseId);
        $products = $this->products->productsForToggle();

        return [
            'in' => $products->whereIn('id', $assignedProductIds)->values(),
            'out' => $products->whereNotIn('id', $assignedProductIds)->values(),
        ];
    }

    protected function summary(bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId): array
    {
        if ($isSuperAdmin) {
            return [
                'total_products' => $this->products->totalCount(),
                'in_warehouse' => $this->products->countWithWarehouse(true, [], null),
                'without_warehouse' => $this->products->countWithoutWarehouse(),
                'configured_rows' => $this->warehouseProducts->configuredRowsCount(),
            ];
        }

        if ($allowedWarehouseIds === []) {
            return [
                'total_products' => 0,
                'in_warehouse' => 0,
                'without_warehouse' => null,
                'configured_rows' => 0,
            ];
        }

        $scopedWarehouseIds = $selectedWarehouseId && $selectedWarehouseId > 0 ? [$selectedWarehouseId] : $allowedWarehouseIds;

        return [
            'total_products' => $this->products->countWithWarehouse(false, $scopedWarehouseIds, null),
            'in_warehouse' => $this->products->countWithWarehouse(false, $scopedWarehouseIds, null),
            'without_warehouse' => null,
            'configured_rows' => $this->warehouseProducts->configuredRowsCount($scopedWarehouseIds),
        ];
    }

    protected function allowedWarehouseIds(bool $isSuperAdmin, array $warehouseIds): array
    {
        if ($isSuperAdmin) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $warehouseIds)));
    }

    protected function selectedWarehouseId(?int $warehouseId, bool $isSuperAdmin, array $allowedWarehouseIds): ?int
    {
        if (! $warehouseId) {
            return $isSuperAdmin ? null : ($allowedWarehouseIds[0] ?? -1);
        }

        if (! $isSuperAdmin && ! in_array($warehouseId, $allowedWarehouseIds, true)) {
            return -1;
        }

        return $warehouseId;
    }

    protected function status(string $status, bool $isSuperAdmin): string
    {
        if (! in_array($status, ['all', 'in_warehouse', 'without_warehouse'], true)) {
            return 'all';
        }

        if (! $isSuperAdmin && in_array($status, ['in_warehouse', 'without_warehouse'], true)) {
            return 'all';
        }

        return $status;
    }
}
