<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WarehouseProductRepository extends RepositoriesAbstract implements WarehouseProductInterface
{
    public function __construct(WarehouseProduct $model)
    {
        parent::__construct($model);
    }

    public function findOrFail($id, array $with = []): WarehouseProduct
    {
        return $this->model->newQuery()->with($with)->findOrFail($id);
    }

    public function createForWarehouse(Warehouse $warehouse, array $data): WarehouseProduct
    {
        return $this->model->newQuery()->create(array_merge($data, [
            'warehouse_id' => $warehouse->getKey(),
            'created_by' => auth()->id(),
        ]));
    }

    public function updateWarehouseProduct(WarehouseProduct $warehouseProduct, array $data): WarehouseProduct
    {
        $warehouseProduct->update($data);

        return $warehouseProduct->refresh();
    }

    public function deleteWarehouseProduct(WarehouseProduct $warehouseProduct): bool
    {
        return (bool) $warehouseProduct->delete();
    }

    public function deactivate(WarehouseProduct $warehouseProduct): bool
    {
        return (bool) $warehouseProduct->update(['is_active' => false]);
    }

    public function belongsToWarehouse(WarehouseProduct $warehouseProduct, Warehouse $warehouse): bool
    {
        return (int) $warehouseProduct->warehouse_id === (int) $warehouse->getKey();
    }

    public function existsDuplicate(Warehouse $warehouse, int $productId, ?int $productVariationId = null, ?WarehouseProduct $ignore = null): bool
    {
        $query = $this->model->newQuery()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $productId);

        if ($productVariationId) {
            $query->where('product_variation_id', $productVariationId);
        } else {
            $query->whereNull('product_variation_id');
        }

        if ($ignore) {
            $query->where($ignore->getKeyName(), '!=', $ignore->getKey());
        }

        return $query->exists();
    }

    public function updateOrCreateBaseAssignment(Warehouse $warehouse, Product $product): WarehouseProduct
    {
        return $this->model->newQuery()->updateOrCreate(
            [
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'product_variation_id' => null,
            ],
            [
                'supplier_id' => null,
                'supplier_product_id' => null,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        );
    }

    public function findBaseAssignment(Warehouse $warehouse, Product $product, bool $activeOnly = false): ?WarehouseProduct
    {
        return $this->model->newQuery()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $product->getKey())
            ->whereNull('product_variation_id')
            ->when($activeOnly, fn (Builder $query) => $query->where('is_active', true))
            ->first();
    }

    public function configuredProductIds(int $warehouseId): array
    {
        return $this->model->newQuery()
            ->where('warehouse_id', $warehouseId)
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    public function activeProductIdsForWarehouse(int $warehouseId): array
    {
        return $this->model->newQuery()
            ->where('warehouse_id', $warehouseId)
            ->where('is_active', true)
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    public function activeAssignmentsByProductIds(array $productIds, bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId = null, array $with = []): Collection
    {
        return $this->model->newQuery()
            ->with($with)
            ->whereIn('product_id', $productIds)
            ->where('is_active', true)
            ->when(! $isSuperAdmin, fn (Builder $query) => $query->whereIn('warehouse_id', $allowedWarehouseIds))
            ->when($selectedWarehouseId, fn (Builder $query) => $query->where('warehouse_id', $selectedWarehouseId))
            ->orderBy('warehouse_id')
            ->get()
            ->groupBy('product_id');
    }

    public function configuredRowsCount(?array $warehouseIds = null): int
    {
        return $this->model->newQuery()
            ->when($warehouseIds !== null, fn (Builder $query) => $query->whereIn('warehouse_id', $warehouseIds))
            ->count();
    }
}
