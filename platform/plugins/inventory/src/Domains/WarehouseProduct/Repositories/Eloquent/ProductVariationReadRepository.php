<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent;

use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\ProductVariationReadInterface;
use Illuminate\Support\Collection;

class ProductVariationReadRepository implements ProductVariationReadInterface
{
    public function find(?int $id): ?ProductVariation
    {
        if (! $id) {
            return null;
        }

        return ProductVariation::query()->find($id);
    }

    public function byProductIds(array $productIds): Collection
    {
        return ProductVariation::query()
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');
    }
}
