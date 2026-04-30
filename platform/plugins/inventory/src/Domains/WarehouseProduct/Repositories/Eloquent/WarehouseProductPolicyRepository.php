<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent;

use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProductPolicy;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductPolicyInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class WarehouseProductPolicyRepository extends RepositoriesAbstract implements WarehouseProductPolicyInterface
{
    public function __construct(WarehouseProductPolicy $model)
    {
        parent::__construct($model);
    }

    public function findOrFail($id, array $with = []): WarehouseProductPolicy
    {
        return $this->model->newQuery()->with($with)->findOrFail($id);
    }

    public function upsertForWarehouseProduct(WarehouseProduct $warehouseProduct, array $payload): WarehouseProductPolicy
    {
        return $this->model->newQuery()->updateOrCreate(
            ['warehouse_product_id' => $warehouseProduct->getKey()],
            array_merge($payload, ['warehouse_product_id' => $warehouseProduct->getKey()])
        );
    }

    public function belongsToWarehouseProduct(int $policyId, WarehouseProduct $warehouseProduct): bool
    {
        return $this->model->newQuery()
            ->whereKey($policyId)
            ->where('warehouse_product_id', $warehouseProduct->getKey())
            ->exists();
    }
}
