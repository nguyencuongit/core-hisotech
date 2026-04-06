<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\StoreInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class StoreRepository extends RepositoriesAbstract implements StoreInterface
{
  
    public function findByCustomerId($id){
        return $this->model
        ->where('customer_id', $id)
        ->first();
    }
}
