<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseProductRequest;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseProductService;
use Illuminate\Http\JsonResponse;

class WarehouseProductController extends BaseController
{
    public function store(Warehouse $warehouse, WarehouseProductRequest $request, WarehouseProductService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $service->create($warehouse, $request->validated());

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function update(Warehouse $warehouse, WarehouseProduct $warehouseProduct, WarehouseProductRequest $request, WarehouseProductService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $service->update($warehouse, $warehouseProduct, $request->validated());

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Warehouse $warehouse, WarehouseProduct $warehouseProduct, WarehouseProductService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $service->deleteOrDeactivate($warehouse, $warehouseProduct);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function searchProducts(Warehouse $warehouse): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.index'), 403);

        $query = trim((string) request('q'));
        $configuredProductIds = WarehouseProduct::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->pluck('product_id')
            ->all();

        $products = Product::query()
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
            ->when($configuredProductIds !== [], fn ($q) => $q->whereNotIn('id', $configuredProductIds))
            ->when($query !== '', function ($q) use ($query): void {
                $q->where(function ($sub) use ($query): void {
                    $sub->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%')
                        ->orWhere('barcode', 'like', '%' . $query . '%')
                        ->orWhere('id', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        $variations = ProductVariation::query()
            ->whereIn('product_id', $products->pluck('id')->all())
            ->get()
            ->keyBy('product_id');

        return response()->json([
            'results' => $products->map(function (Product $product) use ($variations): array {
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
            })->values(),
        ]);
    }

    public function supplierProduct(Warehouse $warehouse, WarehouseProductService $service): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.index'), 403);

        return response()->json([
            'data' => $service->supplierProductSuggestion(
                request('supplier_id') ? (string) request('supplier_id') : null,
                request('product_id') ? (int) request('product_id') : null
            ),
        ]);
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
}
