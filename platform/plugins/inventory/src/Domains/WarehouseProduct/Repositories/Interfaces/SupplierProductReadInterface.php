<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces;

use Botble\Inventory\Domains\Supplier\Models\SupplierProduct;

interface SupplierProductReadInterface
{
    public function find(?string $id): ?SupplierProduct;

    public function findBySupplierAndProduct(?string $supplierId, ?int $productId): ?SupplierProduct;
}
