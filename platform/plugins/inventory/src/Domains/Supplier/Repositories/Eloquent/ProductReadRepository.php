<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Eloquent;

use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\ProductReadInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductReadRepository implements ProductReadInterface
{
    public function searchForSupplier(string $query, int $limit = 20): Collection
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'name', 'sku', 'image', 'price'])
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%')
                        ->orWhere('id', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
