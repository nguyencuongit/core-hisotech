<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\ShippingOrderInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class ShippingOrderRepository extends RepositoriesAbstract implements ShippingOrderInterface 
{
    public function findByOrderId($id){
        return $this->model
            ->where('order_id', $id)
            ->first();
    }
}
