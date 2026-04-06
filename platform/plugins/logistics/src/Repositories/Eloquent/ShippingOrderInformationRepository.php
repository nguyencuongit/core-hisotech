<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\ShippingOrderInformationInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class ShippingOrderInformationRepository extends RepositoriesAbstract implements ShippingOrderInformationInterface
{
    public function findOrderShippingId($id){
        return $this->model
            ->where('shipping_order_id', $id)->first();
    }
}
