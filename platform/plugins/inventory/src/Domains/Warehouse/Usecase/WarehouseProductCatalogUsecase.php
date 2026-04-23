<?php

namespace Botble\Inventory\Domains\Warehouse\Usecase;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseProductCatalogUsecase
{
    public function __construct(
        private WarehouseProductService $warehouseProductService
    ) {
    }

    public function list(array $filters = []): array
    {
        $isSuperAdmin = inventory_is_super_admin();
        $warehouseIds = inventory_warehouse_ids();
        $allowedWarehouseIds = $this->allowedWarehouseIds($isSuperAdmin, $warehouseIds);
        $selectedWarehouseId = $this->selectedWarehouseId($filters, $isSuperAdmin, $allowedWarehouseIds);
        $selectedWarehouse = $this->selectedWarehouse($selectedWarehouseId);
        $status = $this->status($filters, $isSuperAdmin);
        $query = trim((string) ($filters['q'] ?? ''));

        $products = $this->productQuery($status, $query, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId)
            ->paginate(20)
            ->withQueryString();

        $this->attachWarehouseProducts($products, $isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId);

        return [
            'products' => $products,
            'warehouses' => $this->warehouses($isSuperAdmin, $allowedWarehouseIds),
            'summary' => $this->summary($isSuperAdmin, $allowedWarehouseIds, $selectedWarehouseId),
            'filters' => [
                'q' => $query,
                'status' => $status,
                'warehouse_id' => $selectedWarehouseId,
            ],
            'selectedWarehouse' => $selectedWarehouse,
            'toggleProducts' => $this->toggleProducts($selectedWarehouse),
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }

    public function assignProductToWarehouse(int $productId, int $warehouseId): void
    {
        $product = Product::query()->withoutGlobalScopes()->findOrFail($productId);
        $warehouse = Warehouse::query()->findOrFail($warehouseId);

        WarehouseProduct::query()->updateOrCreate(
            [
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'product_variation_id' => null,
            ],
            [
                'default_location_id' => null,
                'supplier_id' => null,
                'supplier_product_id' => null,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        );
    }

    public function assignProductToWarehouses(int $productId, array $warehouseIds): void
    {
        foreach (array_unique(array_map('intval', $warehouseIds)) as $warehouseId) {
            if ($warehouseId > 0) {
                $this->assignProductToWarehouse($productId, $warehouseId);
            }
        }
    }

    public function toggleProductInWarehouse(int $productId, int $warehouseId): string
    {
        $product = Product::query()->withoutGlobalScopes()->findOrFail($productId);
        $warehouse = Warehouse::query()->findOrFail($warehouseId);
        $warehouseProduct = WarehouseProduct::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $product->getKey())
            ->whereNull('product_variation_id')
            ->first();

        if (! $warehouseProduct || ! $warehouseProduct->is_active) {
            $this->assignProductToWarehouse($product->getKey(), $warehouse->getKey());

            return 'added';
        }

        if ((float) $product->quantity !== 0.0) {
            throw ValidationException::withMessages([
                'product_id' => trans('plugins/inventory::inventory.warehouse_product.validation.cannot_remove_has_quantity'),
            ]);
        }

        $this->warehouseProductService->deleteOrDeactivate($warehouse, $warehouseProduct);

        return 'removed';
    }

    public function applyProductChangesForWarehouse(int $warehouseId, array $addProductIds = [], array $removeProductIds = []): void
    {
        $warehouse = Warehouse::query()->findOrFail($warehouseId);
        $addProductIds = $this->normalizeIds($addProductIds);
        $removeProductIds = $this->normalizeIds($removeProductIds);

        DB::transaction(function () use ($warehouse, $addProductIds, $removeProductIds): void {
            foreach ($addProductIds as $productId) {
                $product = Product::query()->withoutGlobalScopes()->findOrFail($productId);

                WarehouseProduct::query()->updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->getKey(),
                        'product_id' => $product->getKey(),
                        'product_variation_id' => null,
                    ],
                    [
                        'default_location_id' => null,
                        'supplier_id' => null,
                        'supplier_product_id' => null,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                    ]
                );
            }

            foreach ($removeProductIds as $productId) {
                $product = Product::query()->withoutGlobalScopes()->findOrFail($productId);

                if ((float) $product->quantity !== 0.0) {
                    throw ValidationException::withMessages([
                        'product_id' => trans('plugins/inventory::inventory.warehouse_product.validation.cannot_remove_has_quantity'),
                    ]);
                }

                $warehouseProduct = WarehouseProduct::query()
                    ->where('warehouse_id', $warehouse->getKey())
                    ->where('product_id', $product->getKey())
                    ->whereNull('product_variation_id')
                    ->where('is_active', true)
                    ->first();

                if ($warehouseProduct) {
                    $this->warehouseProductService->deleteOrDeactivate($warehouse, $warehouseProduct);
                }
            }
        });
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

    protected function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id): bool => $id > 0)));
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
            ->where('is_active', true)
            ->when(! $isSuperAdmin, fn (Builder $query) => $query->whereIn('warehouse_id', $allowedWarehouseIds))
            ->when($selectedWarehouseId, fn (Builder $query) => $query->where('warehouse_id', $selectedWarehouseId))
            ->orderBy('warehouse_id')
            ->get()
            ->groupBy('product_id');

        $allWarehouseProducts = WarehouseProduct::query()
            ->with(['warehouse'])
            ->whereIn('product_id', $productIds)
            ->where('is_active', true)
            ->when(! $isSuperAdmin, fn (Builder $query) => $query->whereIn('warehouse_id', $allowedWarehouseIds))
            ->orderBy('warehouse_id')
            ->get()
            ->groupBy('product_id');

        $products->getCollection()->each(function (Product $product) use ($warehouseProducts, $allWarehouseProducts): void {
            $product->setRelation('inventoryWarehouseProducts', $warehouseProducts->get($product->getKey(), collect()));
            $product->setRelation('allInventoryWarehouseProducts', $allWarehouseProducts->get($product->getKey(), collect()));
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

    protected function selectedWarehouse(?int $selectedWarehouseId): ?Warehouse
    {
        if (! $selectedWarehouseId || $selectedWarehouseId < 1) {
            return null;
        }

        return Warehouse::query()
            ->select(['id', 'code', 'name'])
            ->find($selectedWarehouseId);
    }

    protected function toggleProducts(?Warehouse $selectedWarehouse): array
    {
        if (! $selectedWarehouse) {
            return [
                'in' => collect(),
                'out' => collect(),
            ];
        }

        $assignedProductIds = WarehouseProduct::query()
            ->where('warehouse_id', $selectedWarehouse->getKey())
            ->where('is_active', true)
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $products = Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'name', 'sku', 'quantity'])
            ->orderBy('name')
            ->get();

        return [
            'in' => $products->whereIn('id', $assignedProductIds)->values(),
            'out' => $products->whereNotIn('id', $assignedProductIds)->values(),
        ];
    }

    protected function summary(bool $isSuperAdmin, array $allowedWarehouseIds, ?int $selectedWarehouseId): array
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

        $scopedWarehouseIds = $selectedWarehouseId && $selectedWarehouseId > 0 ? [$selectedWarehouseId] : $allowedWarehouseIds;

        return [
            'total_products' => (clone $baseProductQuery)
                ->whereExists(function ($subQuery) use ($productTable, $scopedWarehouseIds): void {
                    $this->warehouseProductExistsQuery($subQuery, $productTable);
                    $subQuery->whereIn('warehouse_id', $scopedWarehouseIds);
                })
                ->count(),
            'in_warehouse' => (clone $baseProductQuery)
                ->whereExists(function ($subQuery) use ($productTable, $scopedWarehouseIds): void {
                    $this->warehouseProductExistsQuery($subQuery, $productTable);
                    $subQuery->whereIn('warehouse_id', $scopedWarehouseIds);
                })
                ->count(),
            'without_warehouse' => null,
            'configured_rows' => WarehouseProduct::query()
                ->whereIn('warehouse_id', $scopedWarehouseIds)
                ->count(),
        ];
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
            return $isSuperAdmin ? null : ($allowedWarehouseIds[0] ?? -1);
        }

        if (! $isSuperAdmin && ! in_array($warehouseId, $allowedWarehouseIds, true)) {
            return -1;
        }

        return $warehouseId;
    }

    protected function status(array $filters, bool $isSuperAdmin): string
    {
        $status = (string) ($filters['status'] ?? 'all');

        if (! in_array($status, ['all', 'in_warehouse', 'without_warehouse'], true)) {
            return 'all';
        }

        if (! $isSuperAdmin && in_array($status, ['in_warehouse', 'without_warehouse'], true)) {
            return 'all';
        }

        return $status;
    }
}
