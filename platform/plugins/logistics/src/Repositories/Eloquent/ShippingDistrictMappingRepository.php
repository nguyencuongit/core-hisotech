<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\ShippingDistrictMappingInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class ShippingDistrictMappingRepository extends RepositoriesAbstract implements ShippingDistrictMappingInterface
{
  
    public function findByCityId($id, $code){
        return $this->model
        ->where('city_id', $id)
        ->where('provider', $code)
        ->first();
    }
}
