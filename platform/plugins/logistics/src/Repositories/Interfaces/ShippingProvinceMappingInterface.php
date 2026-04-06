<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ShippingProvinceMappingInterface extends RepositoryInterface
{
    public function findByStateId(int $id, string $code);
}
