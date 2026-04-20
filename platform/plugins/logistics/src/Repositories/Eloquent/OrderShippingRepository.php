<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Botble\Logistics\Repositories\Interfaces\OrderShippingInterface;
use Botble\Logistics\Events\ShippingOrderStatusUpdated;
use Botble\Logistics\Enums\OrderShippingStatus;

class OrderShippingRepository extends RepositoriesAbstract implements OrderShippingInterface 
{
    public function findOrderId($order_id){
        return $this->model->where('order_id',$order_id)->first();
    }
}
