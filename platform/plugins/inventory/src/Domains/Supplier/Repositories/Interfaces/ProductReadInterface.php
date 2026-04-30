<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface ProductReadInterface
{
    public function searchForSupplier(string $query, int $limit = 20): Collection;
}
