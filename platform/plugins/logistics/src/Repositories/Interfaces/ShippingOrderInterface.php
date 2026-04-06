<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ShippingOrderInterface extends RepositoryInterface 
{
    public function findByOrderId($id);
}
