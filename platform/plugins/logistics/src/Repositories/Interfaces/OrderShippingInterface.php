<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface OrderShippingInterface extends RepositoryInterface 
{
    public function findOrderId($order_id);
}
