<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ShippingDistrictMappingInterface extends RepositoryInterface
{
    public function findByCityId(int $id, string $code);
}
