<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Support\Collection;

interface WarehouseReadInterface
{
    public function findOrFail(int $id): Warehouse;

    public function findSummary(?int $id): ?Warehouse;

    public function findWithSetting(int $id): Warehouse;

    public function listForScope(bool $isSuperAdmin, array $allowedWarehouseIds): Collection;
}
