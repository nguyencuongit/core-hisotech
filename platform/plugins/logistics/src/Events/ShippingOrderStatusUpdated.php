<?php
namespace Botble\Logistics\Events;

use Botble\Logistics\Models\Shipment;
use Botble\Logistics\Enums\ShipmentStatus;

class ShippingOrderStatusUpdated
{
    public function __construct(
        public Shipment $order,
        public ShipmentStatus $status,
    ) {}
}