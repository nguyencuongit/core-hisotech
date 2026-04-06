<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\ShippingProviderInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class ShippingProviderRepository extends RepositoriesAbstract implements ShippingProviderInterface
{
    public function findCode(string $code){
        return $this->model->where('code', $code)->first();
    }
}
