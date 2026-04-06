<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\OrderAddressInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class OrderAddressRepository extends RepositoriesAbstract implements OrderAddressInterface
{
    public function findByOrderID($id){
        return $this->model
        ->where('order_id', $id)
        ->first();
    }
}
