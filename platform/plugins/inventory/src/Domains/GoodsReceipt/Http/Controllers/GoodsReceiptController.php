<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\GoodsReceipt\Http\Requests\GoodsReceiptRequest;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceipt;
use Botble\Inventory\Domains\GoodsReceipt\Models\ReceiptStorageItem;
use Botble\Inventory\Domains\GoodsReceipt\Services\GoodsReceiptService;
use Botble\Inventory\Domains\GoodsReceipt\Services\ReceiptStorageItemService;
use Botble\Inventory\Domains\GoodsReceipt\Tables\GoodsReceiptTable;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Domains\Warehouse\Services\StockLedgerService;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseSettingService;
use Botble\Inventory\Domains\Warehouse\Support\PalletLocationRules;
use Botble\Inventory\Enums\GoodsReceiptStatusEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

        $goodsReceipt->load([
            'supplier',
            'warehouse.setting',
            'items.product',
            'items.productVariation',
            'items.batches',
            'creator',
            'approver',
            'storageItems.goodsReceiptItem',
            'storageItems.goodsReceiptBatch',
            'storageItems.product',
            'storageItems.warehouseLocation',
            'storageItems.pallet.currentLocation',
            'storageItems.poster',
        ]);

        $warehouseSetting = $goodsReceipt->warehouse->setting
            ?: app(WarehouseSettingService::class)->firstOrCreateDefault($goodsReceipt->warehouse);

        $storageLocations = WarehouseLocation::query()
            ->where('warehouse_id', $goodsReceipt->warehouse_id)
            ->whereIn('type', PalletLocationRules::allowedTypes())
            ->orderBy('path')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'path', 'type']);

        $pallets = Pallet::query()
            ->where('warehouse_id', $goodsReceipt->warehouse_id)
            ->with('currentLocation')
            ->orderBy('code')
            ->get(['id', 'code', 'warehouse_id', 'current_location_id', 'status']);

        $this->pageTitle($goodsReceipt->code);

        return view('plugins/inventory::goods-receipts.show', [
            'goodsReceipt' => $goodsReceipt,
            'allowedStorageLocationTypes' => PalletLocationRules::allowedTypes(),
            'storageLocations' => $storageLocations,
            'pallets' => $pallets,
            'warehouseSetting' => $warehouseSetting,
        ]);
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

    public function generateStorageItems(GoodsReceipt $goodsReceipt, ReceiptStorageItemService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.edit'), 403);

        $items = $service->generateFromReceipt($goodsReceipt->loadMissing(['items.batches', 'warehouse.setting']));

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Đã đồng bộ storage items từ phiếu nhập.',
                'count' => $items->count(),
            ]);
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.goods-receipts.show', $goodsReceipt))
            ->setMessage('Đã đồng bộ storage items từ phiếu nhập.');
    }

    public function updateStorageItem(GoodsReceipt $goodsReceipt, ReceiptStorageItem $storageItem, ReceiptStorageItemService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.edit'), 403);

        if ((string) $storageItem->goods_receipt_id !== (string) $goodsReceipt->getKey()) {
            abort(404);
        }

        $data = request()->validate([
            'warehouse_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'pallet_id' => ['nullable', 'integer', 'exists:inv_pallets,id'],
            'status' => ['required', 'in:receiving,qc_hold,pending_putaway,stored,damaged,rejected,closed'],
            'note' => ['nullable', 'string'],
            'meta_json' => ['nullable', 'array'],
        ]);

        $storageItem = $service->updateStorageLocation($storageItem, $data);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Cập nhật storage item thành công.',
                'data' => $storageItem,
            ]);
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.goods-receipts.show', $goodsReceipt))
            ->setMessage('Cập nhật storage item thành công.');
    }

    public function postStorageItem(GoodsReceipt $goodsReceipt, ReceiptStorageItem $storageItem, StockLedgerService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.edit'), 403);

        if ((string) $storageItem->goods_receipt_id !== (string) $goodsReceipt->getKey()) {
            abort(404);
        }

        $service->postReceiptStorageItem($storageItem);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.goods-receipts.show', $goodsReceipt))
            ->setMessage('Đã ghi tồn kho cho storage item.');
    }

    public function postAllStorageItems(GoodsReceipt $goodsReceipt, StockLedgerService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.goods-receipts.edit'), 403);

        $eligibleItems = $goodsReceipt->storageItems()
            ->whereNull('posted_at')
            ->whereIn('status', ['stored', 'qc_hold', 'damaged', 'rejected'])
            ->get();

        DB::transaction(function () use ($eligibleItems, $service): void {
            foreach ($eligibleItems as $storageItem) {
                $service->postReceiptStorageItem($storageItem);
            }
        });

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.goods-receipts.show', $goodsReceipt))
            ->setMessage(sprintf('Đã ghi tồn kho cho %d storage item.', $eligibleItems->count()));
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
