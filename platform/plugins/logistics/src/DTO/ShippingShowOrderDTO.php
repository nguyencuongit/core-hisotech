<?php
namespace Botble\Logistics\DTO;
class ShippingShowOrderDTO
{
    public function __construct(
        public string $provider,
        public int $order_id,
        // from
        public string $sender_name,
        public string $sender_address,
        public string $sender_phone,
        public string $sender_email,

        public string $sender_province,
        public string $sender_district,

        //to
        public string $receiver_name,
        public string $receiver_address,
        public string $receiver_phone,
        public string $receiver_email,

        public string $receiver_province,
        public string $receiver_district,

        //
        public int $weight,
        public int $length,
        public int $width,
        public int $height,


        public int $cod_amount,
        public int $code,

        
        // items
        public array $list_items,
        
    ) {}
}