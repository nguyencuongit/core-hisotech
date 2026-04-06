<?php
namespace Botble\Logistics\DTO;
class ShippingCreateResponseDTO
{
    public function __construct(
        public readonly string $order_code,
        public readonly int $total_fee,
        public readonly string $expected_delivery_time,
    ) {}
}