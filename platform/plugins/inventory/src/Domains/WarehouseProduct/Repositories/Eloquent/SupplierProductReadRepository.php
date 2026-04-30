<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent;

use Botble\Inventory\Domains\Supplier\Models\SupplierProduct;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\SupplierProductReadInterface;

class SupplierProductReadRepository implements SupplierProductReadInterface
{
    public function find(?string $id): ?SupplierProduct
    {
        if (! $id) {
            return null;
        }

        return SupplierProduct::query()->find($id);
    }

    public function findBySupplierAndProduct(?string $supplierId, ?int $productId): ?SupplierProduct
    {
        if (! $supplierId || ! $productId) {
            return null;
        }

        return SupplierProduct::query()
            ->with('product')
            ->where('supplier_id', $supplierId)
            ->where('product_id', $productId)
            ->first();
    }
}
