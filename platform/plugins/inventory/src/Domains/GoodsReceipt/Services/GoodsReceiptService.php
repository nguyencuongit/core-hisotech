<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Services;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceipt;
use Botble\Inventory\Domains\Supplier\Models\SupplierProduct;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Enums\GoodsReceiptStatusEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function create(array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($data): GoodsReceipt {
            $items = $this->normalizeItems($data);
            $totals = $this->calculateTotals($items, $data);

            $goodsReceipt = GoodsReceipt::query()->create(array_merge($this->prepareReceiptData($data), $totals, [
                'created_by' => auth()->id(),
            ]));

            $this->syncItems($goodsReceipt, $items);

            return $goodsReceipt->load(['supplier', 'warehouse', 'items.product']);
        });
    }

    public function update(GoodsReceipt $goodsReceipt, array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($goodsReceipt, $data): GoodsReceipt {
            $items = $this->normalizeItems($data);
            $totals = $this->calculateTotals($items, $data);

            $goodsReceipt->update(array_merge($this->prepareReceiptData($data, $goodsReceipt), $totals));

            $this->syncItems($goodsReceipt, $items, true);

            return $goodsReceipt->refresh()->load(['supplier', 'warehouse', 'items.product']);
        });
    }

    public function supplierProductSuggestions(string $supplierId, ?int $warehouseId = null): array
    {
        $warehouseProducts = $warehouseId
            ? WarehouseProduct::query()
                ->where('warehouse_id', $warehouseId)
                ->where('is_active', true)
                ->get()
                ->keyBy('product_id')
            : collect();

        return SupplierProduct::query()
            ->with(['product' => function ($query): void {
                $query->withoutGlobalScopes()
                    ->select([
                        'id',
                        'name',
                        'sku',
                        'barcode',
                        'cost_per_item',
                        'quantity',
                        'with_storehouse_management',
                        'stock_status',
                    ]);
            }])
            ->where('supplier_id', $supplierId)
            ->when($warehouseId, fn ($query) => $query->whereIn('product_id', $warehouseProducts->pluck('product_id')->all()))
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(function (SupplierProduct $supplierProduct) use ($warehouseProducts): array {
                $product = $supplierProduct->product;
                $warehouseProduct = $warehouseProducts->get($supplierProduct->product_id);

                return [
                    'supplier_product_id' => $supplierProduct->getKey(),
                    'product_id' => $supplierProduct->product_id,
                    'product_variation_id' => $warehouseProduct?->product_variation_id,
                    'product_name' => $product?->name,
                    'sku' => $product?->sku,
                    'barcode' => $product?->barcode,
                    'ordered_qty' => $supplierProduct->moq ?: 1,
                    'received_qty' => 0,
                    'rejected_qty' => 0,
                    'unit_cost' => $supplierProduct->purchase_price ?: $product?->cost_per_item ?: 0,
                    'uom' => null,
                    'note' => $supplierProduct->lead_time_days
                        ? trans('plugins/inventory::inventory.goods_receipt.lead_time_note', ['days' => $supplierProduct->lead_time_days])
                        : null,
                    'display_text' => $this->formatProductText($product),
                ];
            })
            ->values()
            ->all();
    }

    protected function prepareReceiptData(array $data, ?GoodsReceipt $goodsReceipt = null): array
    {
        return [
            'code' => $data['code'] ?: ($goodsReceipt?->code ?: $this->generateCode()),
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'],
            'receipt_date' => $data['receipt_date'],
            'status' => $data['status'] ?? GoodsReceiptStatusEnum::DRAFT->value,
            'reference_code' => Arr::get($data, 'reference_code'),
            'note' => Arr::get($data, 'note'),
            'discount_amount' => (float) Arr::get($data, 'discount_amount', 0),
            'tax_amount' => (float) Arr::get($data, 'tax_amount', 0),
        ];
    }

    protected function normalizeItems(array $data): array
    {
        $rawItems = collect(Arr::get($data, 'items', []))
            ->filter(fn (array $item): bool => ! empty($item['product_id']))
            ->values();

        if ($rawItems->isEmpty()) {
            return [];
        }

        $products = Product::query()
            ->withoutGlobalScopes()
            ->whereIn('id', $rawItems->pluck('product_id')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        $variations = ProductVariation::query()
            ->whereIn('id', $rawItems->pluck('product_variation_id')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        $supplierProducts = $this->supplierProductsForItems($data, $rawItems);

        return $rawItems
            ->map(function (array $item) use ($products, $variations, $supplierProducts): array {
                $productId = (int) $item['product_id'];
                $product = $products->get($productId);
                $variationId = ! empty($item['product_variation_id']) ? (int) $item['product_variation_id'] : null;
                $variation = $variationId ? $variations->get($variationId) : null;
                $supplierProduct = $this->resolveSupplierProduct($item, $productId, $supplierProducts);
                $this->ensureItemAllowedInWarehouse((int) Arr::get($data, 'warehouse_id'), $productId, $variation?->getKey() ?: $variationId);
                $orderedQty = (float) Arr::get($item, 'ordered_qty', 0);
                $receivedQty = (float) Arr::get($item, 'received_qty', 0);
                $rejectedQty = (float) Arr::get($item, 'rejected_qty', 0);
                $unitCost = (float) (Arr::get($item, 'unit_cost') ?: $supplierProduct?->purchase_price ?: $product?->cost_per_item ?: 0);
                $lineQty = $receivedQty > 0 ? $receivedQty : $orderedQty;

                return [
                    'product_id' => $productId,
                    'product_variation_id' => $variation?->getKey() ?: $variationId,
                    'supplier_product_id' => $supplierProduct?->getKey() ?: Arr::get($item, 'supplier_product_id'),
                    'product_name' => Arr::get($item, 'product_name') ?: $product?->name ?: '',
                    'sku' => Arr::get($item, 'sku') ?: $product?->sku,
                    'barcode' => Arr::get($item, 'barcode') ?: $product?->barcode,
                    'ordered_qty' => $orderedQty,
                    'received_qty' => $receivedQty,
                    'rejected_qty' => $rejectedQty,
                    'unit_cost' => $unitCost,
                    'line_total' => round($lineQty * $unitCost, 4),
                    'uom' => Arr::get($item, 'uom'),
                    'note' => Arr::get($item, 'note'),
                ];
            })
            ->values()
            ->all();
    }

    protected function supplierProductsForItems(array $data, Collection $rawItems): Collection
    {
        $supplierId = Arr::get($data, 'supplier_id');

        if (! $supplierId) {
            return collect();
        }

        return SupplierProduct::query()
            ->where('supplier_id', $supplierId)
            ->whereIn('product_id', $rawItems->pluck('product_id')->filter()->unique()->all())
            ->get()
            ->keyBy('product_id');
    }

    protected function resolveSupplierProduct(array $item, int $productId, Collection $supplierProducts): ?SupplierProduct
    {
        $supplierProductId = Arr::get($item, 'supplier_product_id');

        if ($supplierProductId) {
            $matched = $supplierProducts->first(fn (SupplierProduct $supplierProduct): bool => $supplierProduct->getKey() === $supplierProductId);

            if ($matched) {
                return $matched;
            }
        }

        return $supplierProducts->get($productId);
    }

    protected function ensureItemAllowedInWarehouse(int $warehouseId, int $productId, ?int $productVariationId): void
    {
        $query = WarehouseProduct::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('is_active', true);

        if ($productVariationId) {
            $query->where('product_variation_id', $productVariationId);
        } else {
            $query->whereNull('product_variation_id');
        }

        if (! $query->exists()) {
            throw ValidationException::withMessages([
                'items' => trans('plugins/inventory::inventory.goods_receipt.product_not_in_warehouse'),
            ]);
        }
    }

    protected function calculateTotals(array $items, array $data): array
    {
        $subtotal = collect($items)->sum('line_total');
        $discountAmount = (float) Arr::get($data, 'discount_amount', 0);
        $taxAmount = (float) Arr::get($data, 'tax_amount', 0);

        return [
            'subtotal' => round($subtotal, 4),
            'discount_amount' => round($discountAmount, 4),
            'tax_amount' => round($taxAmount, 4),
            'total_amount' => round(max($subtotal - $discountAmount + $taxAmount, 0), 4),
        ];
    }

    protected function syncItems(GoodsReceipt $goodsReceipt, array $items, bool $replace = false): void
    {
        if ($replace) {
            $goodsReceipt->items()->delete();
        }

        foreach ($items as $item) {
            $goodsReceipt->items()->create($item);
        }
    }

    protected function generateCode(): string
    {
        $lastCode = GoodsReceipt::query()
            ->where('code', 'like', 'PNK%')
            ->latest('created_at')
            ->value('code');

        $number = $lastCode && preg_match('/^PNK(\d+)$/', $lastCode, $matches)
            ? (int) $matches[1] + 1
            : 1;

        do {
            $code = 'PNK' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
            $number++;
        } while (GoodsReceipt::query()->where('code', $code)->exists());

        return $code;
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
