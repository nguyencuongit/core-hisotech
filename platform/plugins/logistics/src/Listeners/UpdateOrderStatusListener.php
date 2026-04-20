<?php
namespace Botble\Logistics\Listeners;

use Botble\Logistics\Events\ShippingOrderStatusUpdated;
use Botble\Ecommerce\Models\Order;
use Botble\Logistics\Enums\ShipmentStatus;

class UpdateOrderStatusListener
{
    public function handle(ShippingOrderStatusUpdated $event): void
    {
        $shippingOrder = $event->order;
        $shippingOrder->status = match ($event->status) {
            ShipmentStatus::READY_TO_SHIP => 'ready_to_be_shipped_out',
            ShipmentStatus::PICKED => 'picked',
            ShipmentStatus::DELIVERING => 'delivering',
            ShipmentStatus::DELIVERED => 'delivered',
            ShipmentStatus::CANCELED => 'canceled',
            default => $shippingOrder->status,
        };

        $shippingOrder->save();
    }
}