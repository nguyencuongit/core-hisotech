<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces;

use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProductPolicy;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface WarehouseProductPolicyInterface extends RepositoryInterface
{
    public function findOrFail($id, array $with = []): WarehouseProductPolicy;

    public function upsertForWarehouseProduct(WarehouseProduct $warehouseProduct, array $payload): WarehouseProductPolicy;

    public function belongsToWarehouseProduct(int $policyId, WarehouseProduct $warehouseProduct): bool;
}
