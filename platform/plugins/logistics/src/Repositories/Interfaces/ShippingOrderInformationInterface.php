<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ShippingOrderInformationInterface extends RepositoryInterface 
{
    public function findOrderShippingId($id);
}
