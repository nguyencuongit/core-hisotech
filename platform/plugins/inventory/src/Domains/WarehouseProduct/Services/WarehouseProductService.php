<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Services;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseProduct\DTO\SupplierProductSuggestionDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductSearchDTO;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\ProductReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\ProductVariationReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\SupplierProductReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductUsageReadInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseProductService
{
    public function __construct(
        protected WarehouseProductInterface $warehouseProducts,
        protected ProductReadInterface $products,
        protected ProductVariationReadInterface $productVariations,
        protected SupplierProductReadInterface $supplierProducts,
        protected WarehouseReadInterface $warehouses,
        protected WarehouseProductUsageReadInterface $usageRead,
    ) {
    }

    public function create(Warehouse $warehouse, WarehouseProductDTO $dto): WarehouseProduct
    {
        return DB::transaction(function () use ($warehouse, $dto): WarehouseProduct {
            $prepared = $this->prepareData($warehouse, $dto->toArray());
            $this->ensureNotDuplicate($warehouse, $prepared);

            return $this->warehouseProducts->createForWarehouse($warehouse, $prepared);
        });
    }

    public function update(Warehouse $warehouse, WarehouseProduct $warehouseProduct, WarehouseProductDTO $dto): WarehouseProduct
    {
        $this->ensureWarehouseProductBelongsToWarehouse($warehouse, $warehouseProduct);

        return DB::transaction(function () use ($warehouse, $warehouseProduct, $dto): WarehouseProduct {
            $payload = $dto->toArray();
            if ($payload['product_id'] === null) {
                unset($payload['product_id']);
            }
            if (! $dto->has('product_variation_id')) {
                unset($payload['product_variation_id']);
            }

            $prepared = $this->prepareData($warehouse, array_merge([
                'product_id' => $warehouseProduct->product_id,
                'product_variation_id' => $warehouseProduct->product_variation_id,
            ], $payload));

            $this->ensureNotDuplicate($warehouse, $prepared, $warehouseProduct);

            return $this->warehouseProducts->updateWarehouseProduct($warehouseProduct, $prepared);
        });
    }

    public function deleteOrDeactivate(Warehouse $warehouse, WarehouseProduct $warehouseProduct): bool
    {
        $this->ensureWarehouseProductBelongsToWarehouse($warehouse, $warehouseProduct);

        return DB::transaction(function () use ($warehouseProduct): bool {
            if ($this->usageRead->hasUsage($warehouseProduct)) {
                return $this->warehouseProducts->deactivate($warehouseProduct);
            }

            return $this->warehouseProducts->deleteWarehouseProduct($warehouseProduct);
        });
    }

    public function assignProductToWarehouses(int $productId, array $warehouseIds): void
    {
        DB::transaction(function () use ($productId, $warehouseIds): void {
            foreach (array_unique(array_map('intval', $warehouseIds)) as $warehouseId) {
                if ($warehouseId > 0) {
                    $this->assignProductToWarehouse($productId, $this->warehouses->findOrFail($warehouseId));
                }
            }
        });
    }

    public function toggleProductInWarehouse(int $productId, Warehouse $warehouse): string
    {
        $product = $this->products->findOrFail($productId);
        $warehouseProduct = $this->warehouseProducts->findBaseAssignment($warehouse, $product);

        if (! $warehouseProduct || ! $warehouseProduct->is_active) {
            $this->warehouseProducts->updateOrCreateBaseAssignment($warehouse, $product);

            return 'added';
        }

        $this->ensureProductHasNoQuantity($product);
        $this->deleteOrDeactivate($warehouse, $warehouseProduct);

        return 'removed';
    }

    public function applyProductChangesForWarehouse(Warehouse $warehouse, array $addProductIds = [], array $removeProductIds = []): void
    {
        DB::transaction(function () use ($warehouse, $addProductIds, $removeProductIds): void {
            foreach ($this->normalizeIds($addProductIds) as $productId) {
                $this->assignProductToWarehouse($productId, $warehouse);
            }

            foreach ($this->normalizeIds($removeProductIds) as $productId) {
                $product = $this->products->findOrFail($productId);
                $this->ensureProductHasNoQuantity($product);

                $warehouseProduct = $this->warehouseProducts->findBaseAssignment($warehouse, $product, true);

                if ($warehouseProduct) {
                    $this->deleteOrDeactivate($warehouse, $warehouseProduct);
                }
            }
        });
    }

    public function searchProducts(Warehouse $warehouse, WarehouseProductSearchDTO $dto): array
    {
        $configuredProductIds = $this->warehouseProducts->configuredProductIds((int) $warehouse->getKey());
        $products = $this->products->searchAvailableForWarehouse((int) $warehouse->getKey(), $dto->query, $configuredProductIds);
        $variations = $this->productVariations->byProductIds($products->pluck('id')->all());

        return $products->map(function (Product $product) use ($variations): array {
            $variation = $variations->get($product->getKey());
            $stockStatus = $product->stock_status;

            if (is_object($stockStatus) && method_exists($stockStatus, 'getValue')) {
                $stockStatus = $stockStatus->getValue();
            }

            return [
                'id' => $product->getKey(),
                'text' => $this->formatProductText($product),
                'product_id' => $product->getKey(),
                'product_variation_id' => $variation?->getKey(),
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'cost_per_item' => $product->cost_per_item,
                'quantity' => $product->quantity,
                'with_storehouse_management' => (bool) $product->with_storehouse_management,
                'stock_status' => $stockStatus,
            ];
        })->values()->all();
    }

    public function supplierProductSuggestion(SupplierProductSuggestionDTO $dto): ?array
    {
        $supplierProduct = $this->supplierProducts->findBySupplierAndProduct($dto->supplierId, $dto->productId);

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

    public function ensureWarehouseProductBelongsToWarehouse(Warehouse $warehouse, WarehouseProduct $warehouseProduct): void
    {
        if (! $this->warehouseProducts->belongsToWarehouse($warehouseProduct, $warehouse)) {
            abort(404);
        }
    }

    protected function assignProductToWarehouse(int $productId, Warehouse $warehouse): WarehouseProduct
    {
        $product = $this->products->findOrFail($productId);

        return $this->warehouseProducts->updateOrCreateBaseAssignment($warehouse, $product);
    }

    protected function prepareData(Warehouse $warehouse, array $data): array
    {
        $productId = (int) Arr::get($data, 'product_id');
        $productVariationId = Arr::get($data, 'product_variation_id') ? (int) Arr::get($data, 'product_variation_id') : null;
        $supplierId = Arr::get($data, 'supplier_id') ?: null;
        $supplierProductId = Arr::get($data, 'supplier_product_id') ?: null;

        $this->ensureProductExists($productId);
        $this->ensureVariationBelongsToProduct($productId, $productVariationId);

        $supplierProduct = $this->supplierProducts->find($supplierProductId);

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
            'supplier_id' => $supplierId,
            'supplier_product_id' => $supplierProduct?->getKey(),
            'is_active' => (bool) Arr::get($data, 'is_active', true),
            'note' => Arr::get($data, 'note'),
        ];
    }

    protected function ensureProductExists(int $productId): void
    {
        if (! $this->products->exists($productId)) {
            $this->throwValidation('product_id', trans('plugins/inventory::inventory.warehouse_product.validation.product_not_found'));
        }
    }

    protected function ensureVariationBelongsToProduct(int $productId, ?int $productVariationId): void
    {
        if (! $productVariationId) {
            return;
        }

        $variation = $this->productVariations->find($productVariationId);

        if (! $variation) {
            $this->throwValidation('product_variation_id', trans('plugins/inventory::inventory.warehouse_product.validation.variation_not_found'));
        }

        if ((int) $variation->product_id !== $productId && (int) $variation->configurable_product_id !== $productId) {
            $this->throwValidation('product_variation_id', trans('plugins/inventory::inventory.warehouse_product.validation.variation_product_mismatch'));
        }
    }

    protected function ensureNotDuplicate(Warehouse $warehouse, array $data, ?WarehouseProduct $ignore = null): void
    {
        if ($this->warehouseProducts->existsDuplicate($warehouse, (int) $data['product_id'], $data['product_variation_id'], $ignore)) {
            $this->throwValidation('product_id', trans('plugins/inventory::inventory.warehouse_product.validation.duplicated'));
        }
    }

    protected function ensureProductHasNoQuantity(Product $product): void
    {
        if ((float) $product->quantity !== 0.0) {
            throw ValidationException::withMessages([
                'product_id' => trans('plugins/inventory::inventory.warehouse_product.validation.cannot_remove_has_quantity'),
            ]);
        }
    }

    protected function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id): bool => $id > 0)));
    }

    protected function formatProductText(Product $product): string
    {
        $name = trim((string) $product->name);
        $sku = trim((string) $product->sku);

        if ($name !== '' && $sku !== '') {
            return sprintf('%s (%s)', $name, $sku);
        }

        return $name ?: $sku ?: (string) $product->getKey();
    }

    protected function throwValidation(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
