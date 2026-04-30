<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\ProductReadInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductReadRepository implements ProductReadInterface
{
    public function findOrFail(int $id): Product
    {
        return Product::query()->withoutGlobalScopes()->findOrFail($id);
    }

    public function exists(int $id): bool
    {
        return Product::query()->withoutGlobalScopes()->whereKey($id)->exists();
    }

    public function searchAvailableForWarehouse(int $warehouseId, string $query, array $excludedProductIds = [], int $limit = 20): Collection
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->select([
                'id',
                'name',
                'sku',
                'barcode',
                'cost_per_item',
                'quantity',
                'with_storehouse_management',
                'stock_status',
                'image',
            ])
            ->when($excludedProductIds !== [], fn (Builder $builder) => $builder->whereNotIn('id', $excludedProductIds))
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%')
                        ->orWhere('barcode', 'like', '%' . $query . '%')
                        ->orWhere('id', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function paginateCatalog(string $status, string $query, bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId, int $perPage = 20): LengthAwarePaginator
    {
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
            return $products->whereRaw('1 = 0')->orderBy($productTable . '.name')->paginate($perPage)->withQueryString();
        }

        if ($status === 'without_warehouse') {
            return $products
                ->whereNotExists(fn ($subQuery) => $this->warehouseProductExistsQuery($subQuery, $productTable))
                ->orderBy($productTable . '.name')
                ->paginate($perPage)
                ->withQueryString();
        }

        if ($status === 'in_warehouse' || ! $isSuperAdmin || $selectedWarehouseId) {
            $products->whereExists(function ($subQuery) use ($productTable, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId): void {
                $this->warehouseProductExistsQuery($subQuery, $productTable);
                $this->scopeWarehouseProductQuery($subQuery, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId);
            });
        }

        return $products->orderBy($productTable . '.name')->paginate($perPage)->withQueryString();
    }

    public function productsForToggle(): Collection
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'name', 'sku', 'quantity'])
            ->orderBy('name')
            ->get();
    }

    public function totalCount(): int
    {
        return Product::query()->withoutGlobalScopes()->count();
    }

    public function countWithWarehouse(bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId): int
    {
        $productTable = (new Product())->getTable();
        $scopedWarehouseIds = $selectedWarehouseId && $selectedWarehouseId > 0 ? [$selectedWarehouseId] : $allowedWarehouseIds;

        return Product::query()
            ->withoutGlobalScopes()
            ->whereExists(function ($subQuery) use ($productTable, $isSuperAdmin, $scopedWarehouseIds): void {
                $this->warehouseProductExistsQuery($subQuery, $productTable);

                if (! $isSuperAdmin || $scopedWarehouseIds !== []) {
                    $subQuery->whereIn('warehouse_id', $scopedWarehouseIds);
                }
            })
            ->count();
    }

    public function countWithoutWarehouse(): int
    {
        $productTable = (new Product())->getTable();

        return Product::query()
            ->withoutGlobalScopes()
            ->whereNotExists(fn ($subQuery) => $this->warehouseProductExistsQuery($subQuery, $productTable))
            ->count();
    }

    protected function warehouseProductExistsQuery($query, string $productTable)
    {
        return $query
            ->select(DB::raw(1))
            ->from('inv_warehouse_products')
            ->whereColumn('inv_warehouse_products.product_id', $productTable . '.id')
            ->where('inv_warehouse_products.is_active', true);
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
}
