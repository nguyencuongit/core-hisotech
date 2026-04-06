<?php

namespace Botble\Logistics\Usecase;
use Botble\Logistics\Services\Factories\ShippingFactory;


class CancelShippingOrder
{
    public function cancelOrder($provider, $code){
        $shipping = ShippingFactory::make($provider);
        return $shipping->cancelOrderShipping($code);
    }
} 
