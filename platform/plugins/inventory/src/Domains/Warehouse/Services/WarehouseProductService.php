<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Supplier\Models\SupplierProduct;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseProductService
{
    public function create(Warehouse $warehouse, array $data): WarehouseProduct
    {
        return DB::transaction(function () use ($warehouse, $data): WarehouseProduct {
            $prepared = $this->prepareData($warehouse, $data);
            $this->ensureNotDuplicate($warehouse, $prepared);

            return WarehouseProduct::query()->create(array_merge($prepared, [
                'warehouse_id' => $warehouse->getKey(),
                'created_by' => auth()->id(),
            ]));
        });
    }

    public function update(Warehouse $warehouse, WarehouseProduct $warehouseProduct, array $data): WarehouseProduct
    {
        $this->ensureWarehouseProductBelongsToWarehouse($warehouse, $warehouseProduct);

        return DB::transaction(function () use ($warehouse, $warehouseProduct, $data): WarehouseProduct {
            $prepared = $this->prepareData($warehouse, array_merge([
                'product_id' => $warehouseProduct->product_id,
                'product_variation_id' => $warehouseProduct->product_variation_id,
            ], $data));

            $this->ensureNotDuplicate($warehouse, $prepared, $warehouseProduct);

            $warehouseProduct->update($prepared);

            return $warehouseProduct->refresh();
        });
    }

    public function deleteOrDeactivate(Warehouse $warehouse, WarehouseProduct $warehouseProduct): bool
    {
        $this->ensureWarehouseProductBelongsToWarehouse($warehouse, $warehouseProduct);

        return DB::transaction(function () use ($warehouseProduct): bool {
            if ($this->hasUsage($warehouseProduct)) {
                return (bool) $warehouseProduct->update(['is_active' => false]);
            }

            return (bool) $warehouseProduct->delete();
        });
    }

    public function supplierProductSuggestion(?string $supplierId, ?int $productId): ?array
    {
        if (! $supplierId || ! $productId) {
            return null;
        }

        $supplierProduct = SupplierProduct::query()
            ->with('product')
            ->where('supplier_id', $supplierId)
            ->where('product_id', $productId)
            ->first();

        if (! $supplierProduct) {
            return null;
        }

        return [
            'supplier_product_id' => $supplierProduct->getKey(),
            'purchase_price' => $supplierProduct->purchase_price,
            'moq' => $supplierProduct->moq,
            'lead_time_days' => $supplierProduct->lead_time_days,
        ];
    }

    protected function prepareData(Warehouse $warehouse, array $data): array
    {
        $productId = (int) Arr::get($data, 'product_id');
        $productVariationId = Arr::get($data, 'product_variation_id') ? (int) Arr::get($data, 'product_variation_id') : null;
        $defaultLocationId = Arr::get($data, 'default_location_id') ? (int) Arr::get($data, 'default_location_id') : null;
        $supplierId = Arr::get($data, 'supplier_id') ?: null;
        $supplierProductId = Arr::get($data, 'supplier_product_id') ?: null;

        $this->ensureProductExists($productId);
        $this->ensureVariationBelongsToProduct($productId, $productVariationId);
        $this->ensureLocationBelongsToWarehouse($warehouse, $defaultLocationId);

        $supplierProduct = $this->resolveSupplierProduct($supplierProductId);

        if ($supplierProduct) {
            if ($supplierId && $supplierProduct->supplier_id !== $supplierId) {
                $this->throwValidation('supplier_product_id', trans('plugins/inventory::inventory.warehouse_product.validation.supplier_product_supplier_mismatch'));
            }

            if ((int) $supplierProduct->product_id !== $productId) {
                $this->throwValidation('supplier_product_id', trans('plugins/inventory::inventory.warehouse_product.validation.supplier_product_product_mismatch'));
            }

            $supplierId = $supplierId ?: $supplierProduct->supplier_id;
        }

        return [
            'product_id' => $productId,
            'product_variation_id' => $productVariationId,
            'default_location_id' => $defaultLocationId,
            'supplier_id' => $supplierId,
            'supplier_product_id' => $supplierProduct?->getKey(),
            'is_active' => (bool) Arr::get($data, 'is_active', true),
            'note' => Arr::get($data, 'note'),
        ];
    }

    protected function ensureProductExists(int $productId): void
    {
        if (! Product::query()->withoutGlobalScopes()->whereKey($productId)->exists()) {
            $this->throwValidation('product_id', trans('plugins/inventory::inventory.warehouse_product.validation.product_not_found'));
        }
    }

    protected function ensureVariationBelongsToProduct(int $productId, ?int $productVariationId): void
    {
        if (! $productVariationId) {
            return;
        }

        $variation = ProductVariation::query()->find($productVariationId);

        if (! $variation) {
            $this->throwValidation('product_variation_id', trans('plugins/inventory::inventory.warehouse_product.validation.variation_not_found'));
        }

        if ((int) $variation->product_id !== $productId && (int) $variation->configurable_product_id !== $productId) {
            $this->throwValidation('product_variation_id', trans('plugins/inventory::inventory.warehouse_product.validation.variation_product_mismatch'));
        }
    }

    protected function ensureLocationBelongsToWarehouse(Warehouse $warehouse, ?int $defaultLocationId): void
    {
        if (! $defaultLocationId) {
            return;
        }

        $locationExists = WarehouseLocation::query()
            ->whereKey($defaultLocationId)
            ->where('warehouse_id', $warehouse->getKey())
            ->exists();

        if (! $locationExists) {
            $this->throwValidation('default_location_id', trans('plugins/inventory::inventory.warehouse_product.validation.location_warehouse_mismatch'));
        }
    }

    protected function resolveSupplierProduct(?string $supplierProductId): ?SupplierProduct
    {
        if (! $supplierProductId) {
            return null;
        }

        return SupplierProduct::query()->find($supplierProductId);
    }

    protected function ensureNotDuplicate(Warehouse $warehouse, array $data, ?WarehouseProduct $ignore = null): void
    {
        $query = WarehouseProduct::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $data['product_id']);

        if ($data['product_variation_id']) {
            $query->where('product_variation_id', $data['product_variation_id']);
        } else {
            $query->whereNull('product_variation_id');
        }

        if ($ignore) {
            $query->where($ignore->getKeyName(), '!=', $ignore->getKey());
        }

        if ($query->exists()) {
            $this->throwValidation('product_id', trans('plugins/inventory::inventory.warehouse_product.validation.duplicated'));
        }
    }

    protected function ensureWarehouseProductBelongsToWarehouse(Warehouse $warehouse, WarehouseProduct $warehouseProduct): void
    {
        if ((int) $warehouseProduct->warehouse_id !== (int) $warehouse->getKey()) {
            abort(404);
        }
    }

    protected function hasUsage(WarehouseProduct $warehouseProduct): bool
    {
        $variationCondition = function ($query) use ($warehouseProduct): void {
            if ($warehouseProduct->product_variation_id) {
                $query->where('product_variation_id', $warehouseProduct->product_variation_id);
            } else {
                $query->whereNull('product_variation_id');
            }
        };

        $hasStockTransaction = DB::table('inv_stock_transactions')
            ->where('warehouse_id', $warehouseProduct->warehouse_id)
            ->where('product_id', $warehouseProduct->product_id)
            ->where($variationCondition)
            ->exists();

        if ($hasStockTransaction) {
            return true;
        }

        $hasStockBalance = DB::table('inv_stock_balances')
            ->where('warehouse_id', $warehouseProduct->warehouse_id)
            ->where('product_id', $warehouseProduct->product_id)
            ->where($variationCondition)
            ->exists();

        if ($hasStockBalance) {
            return true;
        }

        return DB::table('inv_goods_receipt_items')
            ->join('inv_goods_receipts', 'inv_goods_receipts.id', '=', 'inv_goods_receipt_items.goods_receipt_id')
            ->where('inv_goods_receipts.warehouse_id', $warehouseProduct->warehouse_id)
            ->where('inv_goods_receipt_items.product_id', $warehouseProduct->product_id)
            ->where(function ($query) use ($warehouseProduct): void {
                if ($warehouseProduct->product_variation_id) {
                    $query->where('inv_goods_receipt_items.product_variation_id', $warehouseProduct->product_variation_id);
                } else {
                    $query->whereNull('inv_goods_receipt_items.product_variation_id');
                }
            })
            ->exists();
    }

    protected function throwValidation(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
