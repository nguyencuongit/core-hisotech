<?php

namespace Botble\Logistics\Usecase;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInterface;
use Botble\Logistics\Exceptions\ShippingException;
use Botble\Logistics\Enums\ShippingStatus;
use Botble\Logistics\Repositories\Interfaces\OrderShippingInterface;
use Botble\Logistics\Events\ShippingOrderStatusUpdated;
use Botble\Logistics\Enums\ShipmentStatus;


class CancelShippingOrderUsecase
{

    public function __construct( 
        private ShippingOrderInterface $shippingOrderInterface,
        private OrderShippingInterface $orderShippingInterface,
    ){}
    public function cancelOrder($provider, $code, $order_id){
        $shipping = ShippingFactory::make($provider);

        $cancel_shipping = $shipping->cancelOrderShipping($code);

        if($cancel_shipping->success){
           $this->statusOrderShipping($code, $provider);
           $this->evenUpdateShipment($order_id);
        }
        return $cancel_shipping;
    }

    public function statusOrderShipping($code,$provider){
        $status = $this->shippingOrderInterface->updateStatus($code, ShippingStatus::CANCEL);
        if(!$status){
            throw new ShippingException(
                message: 'Không cập nhập được trạng thái CANCEL của đơn ship',
                provider: $provider
            );
        }
        return $status;
    }     
    private function evenUpdateShipment($order_id){
        $shipment = $this->orderShippingInterface->findOrderId($order_id);
        event(new ShippingOrderStatusUpdated(
                    $shipment,
                    ShipmentStatus::CANCELED
                ));
    }  
} 
