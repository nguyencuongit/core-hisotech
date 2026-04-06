<?php
namespace Botble\Logistics\Services\Mappers;
use Botble\Logistics\Models\shippingDistrictMapping;
use Botble\Logistics\DTO\ShippingCreateDTO;

class ShippingOrderInformationMapper
{
    public static function toModel(ShippingCreateDTO $dto,  int $orderId): array
    {
       return [
            'shipping_order_id' => $orderId, 
            "from_name"=> $dto->sender_name,
            "from_address"=> $dto->sender_address,
            "from_phone"=> $dto->sender_phone,
            "sender_email"=> $dto->sender_email,

            "from_province"=> $dto->sender_province,
            "from_district"=> $dto->sender_district,

            "to_name"=> $dto->receiver_name,
            "to_address"=> $dto->receiver_address,
            "to_phone"=> $dto->receiver_phone,
            "receiver_email"=> $dto->receiver_email,

            "to_province"=> $dto->receiver_province,
            "to_district"=> $dto->receiver_district,
            
            "cod_amount"=>$dto->cod_amount,
            "weight"=>$dto->weight,
            "length"=>$dto->length,
            "width"=>$dto->width,
            "height"=>$dto->height,
        ];
    }
}