<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface OrderAddressInterface extends RepositoryInterface
{
    public function findByOrderID(int $id);
}
