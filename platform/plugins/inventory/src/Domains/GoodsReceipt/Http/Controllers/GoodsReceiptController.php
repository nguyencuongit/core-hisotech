<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\GoodsReceipt\Http\Requests\GoodsReceiptRequest;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceipt;
use Botble\Inventory\Domains\GoodsReceipt\Services\GoodsReceiptService;
use Botble\Inventory\Domains\GoodsReceipt\Tables\GoodsReceiptTable;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Enums\GoodsReceiptStatusEnum;
use Illuminate\Http\JsonResponse;

class GoodsReceiptController extends BaseController
{
    public function __construct()
    {
        $this->breadcrumb()->add(trans('plugins/inventory::inventory.name'), route('inventory.goods-receipts.index'));
    }

    public function index(GoodsReceiptTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.goods_receipt.name'));

        return $table->renderTable();
    }

    public function create()
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.create'), 403);

        $this->pageTitle(trans('plugins/inventory::inventory.goods_receipt.create'));

        return view('plugins/inventory::goods-receipts.create', $this->formData());
    }

    public function store(GoodsReceiptRequest $request, GoodsReceiptService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.create'), 403);

        $goodsReceipt = $service->create($request->validated());

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.goods-receipts.index'))
            ->setNextUrl(route('inventory.goods-receipts.edit', $goodsReceipt->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.show'), 403);

        $goodsReceipt->load(['supplier', 'warehouse', 'items.product', 'items.productVariation', 'creator', 'approver']);

        $this->pageTitle($goodsReceipt->code);

        return view('plugins/inventory::goods-receipts.show', compact('goodsReceipt'));
    }

    public function edit(GoodsReceipt $goodsReceipt)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.edit'), 403);

        $goodsReceipt->load(['supplier', 'warehouse', 'items.product', 'items.productVariation']);

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $goodsReceipt->code]));

        return view('plugins/inventory::goods-receipts.edit', array_merge(
            compact('goodsReceipt'),
            $this->formData()
        ));
    }

    public function update(GoodsReceipt $goodsReceipt, GoodsReceiptRequest $request, GoodsReceiptService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.edit'), 403);

        $service->update($goodsReceipt, $request->validated());

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.goods-receipts.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.delete'), 403);

        return DeleteResourceAction::make($goodsReceipt);
    }

    public function searchProducts(): JsonResponse
    {
        $query = trim((string) request('q'));
        $warehouseId = request('warehouse_id') ? (int) request('warehouse_id') : null;

        if (! $warehouseId) {
            return response()->json(['results' => []]);
        }

        $warehouseProducts = WarehouseProduct::query()
            ->with([
                'product' => function ($q): void {
                    $q->withoutGlobalScopes()
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
                        ]);
                },
                'supplierProduct',
            ])
            ->where('warehouse_id', $warehouseId)
            ->where('is_active', true)
            ->whereHas('product', function ($q) use ($query): void {
                $q->withoutGlobalScopes()
                    ->when($query !== '', function ($sub) use ($query): void {
                        $sub->where(function ($nested) use ($query): void {
                            $nested->where('name', 'like', '%' . $query . '%')
                                ->orWhere('sku', 'like', '%' . $query . '%')
                                ->orWhere('barcode', 'like', '%' . $query . '%')
                                ->orWhere('id', 'like', '%' . $query . '%');
                        });
                    });
            })
            ->latest()
            ->limit(20)
            ->get();

        $results = $warehouseProducts->map(function (WarehouseProduct $warehouseProduct): array {
            $product = $warehouseProduct->product;
            $image = null;
            $stockStatus = $product?->stock_status;

            if (! empty($product?->image)) {
                try {
                    $image = rv_media()->getImageUrl($product->image, 'thumb');
                } catch (\Throwable) {
                    $image = null;
                }
            }

            if (is_object($stockStatus) && method_exists($stockStatus, 'getValue')) {
                $stockStatus = $stockStatus->getValue();
            }

            return [
                'id' => $warehouseProduct->getKey(),
                'text' => $this->formatProductText($product),
                'product_id' => $product?->getKey(),
                'product_variation_id' => $warehouseProduct->product_variation_id,
                'supplier_product_id' => $warehouseProduct->supplier_product_id,
                'product_name' => $product?->name,
                'sku' => $product?->sku,
                'barcode' => $product?->barcode,
                'unit_cost' => $warehouseProduct->supplierProduct?->purchase_price ?: $product?->cost_per_item ?: 0,
                'quantity' => $product?->quantity,
                'with_storehouse_management' => (bool) $product?->with_storehouse_management,
                'stock_status' => $stockStatus,
                'image' => $image,
            ];
        })->values();

        return response()->json(['results' => $results]);
    }

    public function supplierProducts(GoodsReceiptService $service): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.create') || auth()->user()?->hasPermission('inventory.goods-receipts.edit'), 403);

        $supplierId = (string) request('supplier_id');

        if ($supplierId === '') {
            return response()->json(['results' => []]);
        }

        $warehouseId = request('warehouse_id') ? (int) request('warehouse_id') : null;

        if (! $warehouseId) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => $service->supplierProductSuggestions(
                $supplierId,
                $warehouseId
            ),
        ]);
    }

    protected function formData(): array
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(fn (Supplier $supplier): array => [
                $supplier->getKey() => trim(sprintf('%s - %s', $supplier->code, $supplier->name), ' -'),
            ])
            ->all();

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(fn (Warehouse $warehouse): array => [
                $warehouse->getKey() => trim(sprintf('%s - %s', $warehouse->code, $warehouse->name), ' -'),
            ])
            ->all();

        return [
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'statuses' => collect(GoodsReceiptStatusEnum::cases())
                ->mapWithKeys(fn (GoodsReceiptStatusEnum $status): array => [$status->value => $status->label()])
                ->all(),
        ];
    }

    protected function formatProductText(?Product $product): string
    {
        if (! $product) {
            return '';
        }

        $name = trim((string) $product->name);
        $sku = trim((string) $product->sku);

        if ($name !== '' && $sku !== '') {
            return sprintf('%s (%s)', $name, $sku);
        }

        return $name ?: $sku ?: (string) $product->getKey();
    }
}
