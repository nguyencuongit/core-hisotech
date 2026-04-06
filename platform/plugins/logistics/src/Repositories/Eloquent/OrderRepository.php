<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\OrderInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class OrderRepository extends RepositoriesAbstract implements OrderInterface 
{
    
    public function find($id){
        return $this->findById($id);
    }
}
