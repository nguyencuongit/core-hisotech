<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\ShippingProvinceMappingInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class ShippingProvinceMappingRepository extends RepositoriesAbstract implements ShippingProvinceMappingInterface
{
  
    public function findByStateId($id, $code){
        return $this->model
        ->where('state_id', $id)
        ->where('provider', $code)
        ->first();
    }
}
