<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent;

use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductUsageReadInterface;
use Illuminate\Support\Facades\DB;

class WarehouseProductUsageReadRepository implements WarehouseProductUsageReadInterface
{
    public function hasUsage(WarehouseProduct $warehouseProduct): bool
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
}
