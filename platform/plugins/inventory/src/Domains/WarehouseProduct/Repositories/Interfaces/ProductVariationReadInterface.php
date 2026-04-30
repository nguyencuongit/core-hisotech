<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces;

use Botble\Ecommerce\Models\ProductVariation;
use Illuminate\Support\Collection;

interface ProductVariationReadInterface
{
    public function find(?int $id): ?ProductVariation;

    public function byProductIds(array $productIds): Collection;
}
