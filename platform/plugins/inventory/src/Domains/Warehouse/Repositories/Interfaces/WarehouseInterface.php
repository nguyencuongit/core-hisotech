<?php

namespace Botble\Inventory\Domains\Warehouse\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface WarehouseInterface extends RepositoryInterface
{
    public function query(): Builder;

    public function test(): Collection;
}
