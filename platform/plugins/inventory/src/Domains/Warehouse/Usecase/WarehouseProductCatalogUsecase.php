<?php

namespace Botble\Inventory\Domains\Warehouse\Usecase;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseProductCatalogUsecase
{
    public function list(array $filters = []): array
    {
        $isSuperAdmin = inventory_is_super_admin();
        $warehouseIds = inventory_warehouse_ids();
        $allowedWarehouseIds = $this->allowedWarehouseIds($isSuperAdmin, $warehouseIds);
        $selectedWarehouseId = $this->selectedWarehouseId($filters, $isSuperAdmin, $allowedWarehouseIds);
        $status = $this->status($filters, $isSuperAdmin);
        $query = trim((string) ($filters['q'] ?? ''));

        $products = $this->productQuery($status, $query, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId)
            ->paginate(20)
            ->withQueryString();

        $this->attachWarehouseProducts($products, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId);

        return [
            'products' => $products,
            'warehouses' => $this->warehouses($isSuperAdmin, $allowedWarehouseIds),
            'summary' => $this->summary($isSuperAdmin, $allowedWarehouseIds),
            'filters' => [
                'q' => $query,
                'status' => $status,
                'warehouse_id' => $selectedWarehouseId,
            ],
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }

    protected function productQuery(
        string $status,
        string $query,
        bool $isSuperAdmin,
        array $allowedWarehouseIds,
        ?int $selectedWarehouseId
    ): Builder {
        $productTable = (new Product())->getTable();

        $products = Product::query()
            ->withoutGlobalScopes()
            ->select([
                $productTable . '.id',
                $productTable . '.name',
                $productTable . '.sku',
                $productTable . '.barcode',
                $productTable . '.quantity',
                $productTable . '.stock_status',
                $productTable . '.cost_per_item',
                $productTable . '.with_storehouse_management',
                $productTable . '.image',
                $productTable . '.created_at',
            ])
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%')
                        ->orWhere('barcode', 'like', '%' . $query . '%')
                        ->orWhere('id', 'like', '%' . $query . '%');
                });
            });

        if (! $isSuperAdmin && $allowedWarehouseIds === []) {
            return $products->whereRaw('1 = 0');
        }

        if ($status === 'without_warehouse') {
            return $products
                ->whereNotExists(fn ($subQuery) => $this->warehouseProductExistsQuery($subQuery, $productTable))
                ->orderBy($productTable . '.name');
        }

        if ($status === 'in_warehouse' || ! $isSuperAdmin || $selectedWarehouseId) {
            $products->whereExists(function ($subQuery) use ($productTable, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId): void {
                $this->warehouseProductExistsQuery($subQuery, $productTable);
                $this->scopeWarehouseProductQuery($subQuery, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId);
            });
        }

        return $products->orderBy($productTable . '.name');
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

        $warehouseProducts = WarehouseProduct::query()
            ->with(['warehouse', 'productVariation', 'defaultLocation', 'supplier'])
            ->whereIn('product_id', $productIds)
            ->when(! $isSuperAdmin, fn (Builder $query) => $query->whereIn('warehouse_id', $allowedWarehouseIds))
            ->when($selectedWarehouseId, fn (Builder $query) => $query->where('warehouse_id', $selectedWarehouseId))
            ->orderBy('warehouse_id')
            ->get()
            ->groupBy('product_id');

        $products->getCollection()->each(function (Product $product) use ($warehouseProducts): void {
            $product->setRelation('inventoryWarehouseProducts', $warehouseProducts->get($product->getKey(), collect()));
        });
    }

    protected function warehouses(bool $isSuperAdmin, array $allowedWarehouseIds): Collection
    {
        return Warehouse::query()
            ->select(['id', 'code', 'name'])
            ->when(! $isSuperAdmin, fn (Builder $query) => $query->whereIn('id', $allowedWarehouseIds))
            ->orderBy('name')
            ->get();
    }

    protected function summary(bool $isSuperAdmin, array $allowedWarehouseIds): array
    {
        $productTable = (new Product())->getTable();
        $baseProductQuery = Product::query()->withoutGlobalScopes();

        if ($isSuperAdmin) {
            return [
                'total_products' => (clone $baseProductQuery)->count(),
                'in_warehouse' => (clone $baseProductQuery)
                    ->whereExists(fn ($subQuery) => $this->warehouseProductExistsQuery($subQuery, $productTable))
                    ->count(),
                'without_warehouse' => (clone $baseProductQuery)
                    ->whereNotExists(fn ($subQuery) => $this->warehouseProductExistsQuery($subQuery, $productTable))
                    ->count(),
                'configured_rows' => WarehouseProduct::query()->count(),
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

        return [
            'total_products' => (clone $baseProductQuery)
                ->whereExists(function ($subQuery) use ($productTable, $allowedWarehouseIds): void {
                    $this->warehouseProductExistsQuery($subQuery, $productTable);
                    $subQuery->whereIn('warehouse_id', $allowedWarehouseIds);
                })
                ->count(),
            'in_warehouse' => (clone $baseProductQuery)
                ->whereExists(function ($subQuery) use ($productTable, $allowedWarehouseIds): void {
                    $this->warehouseProductExistsQuery($subQuery, $productTable);
                    $subQuery->whereIn('warehouse_id', $allowedWarehouseIds);
                })
                ->count(),
            'without_warehouse' => null,
            'configured_rows' => WarehouseProduct::query()
                ->whereIn('warehouse_id', $allowedWarehouseIds)
                ->count(),
        ];
    }

    protected function warehouseProductExistsQuery($query, string $productTable)
    {
        return $query
            ->select(DB::raw(1))
            ->from('inv_warehouse_products')
            ->whereColumn('inv_warehouse_products.product_id', $productTable . '.id');
    }

    protected function scopeWarehouseProductQuery($query, bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId): void
    {
        if ($selectedWarehouseId) {
            $query->where('warehouse_id', $selectedWarehouseId);

            return;
        }

        if (! $isSuperAdmin) {
            $query->whereIn('warehouse_id', $allowedWarehouseIds);
        }
    }

    protected function allowedWarehouseIds(bool $isSuperAdmin, array $warehouseIds): array
    {
        if ($isSuperAdmin) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $warehouseIds)));
    }

    protected function selectedWarehouseId(array $filters, bool $isSuperAdmin, array $allowedWarehouseIds): ?int
    {
        $warehouseId = (int) ($filters['warehouse_id'] ?? 0);

        if (! $warehouseId) {
            return null;
        }

        if (! $isSuperAdmin && ! in_array($warehouseId, $allowedWarehouseIds, true)) {
            return -1;
        }

        return $warehouseId;
    }

    protected function status(array $filters, bool $isSuperAdmin): string
    {
        $status = (string) ($filters['status'] ?? ($isSuperAdmin ? 'all' : 'in_warehouse'));

        if (! in_array($status, ['all', 'in_warehouse', 'without_warehouse'], true)) {
            return $isSuperAdmin ? 'all' : 'in_warehouse';
        }

        if (! $isSuperAdmin && $status !== 'in_warehouse') {
            return 'in_warehouse';
        }

        return $status;
    }
}
