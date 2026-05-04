<?php

namespace Botble\Inventory\Services;
use Illuminate\Support\Facades\DB;
class ProductFormService
{
    public function showProductForm()
    {
        $warehouseIds = inventory_warehouse_ids();

        $query = DB::table('inv_warehouse_products');

        if (!inventory_is_super_admin() && !empty($warehouseIds)) {
            $query->whereIn('warehouse_id', $warehouseIds);
        }
        $productIds = $query->pluck('product_id')->toArray();
        $products = [];

       if (!empty($productIds)) {
            $products = DB::table('ec_products')
                ->whereIn('id', $productIds)
                ->pluck('name', 'id')
                ->toArray();
        }
        
        return $products;
    }
}   