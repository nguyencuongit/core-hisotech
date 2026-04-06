<?php
namespace Botble\Logistics\DTO;
use Botble\Logistics\Http\Requests\CreateOrderShippingRequest;
class ShippingCreateDTO
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

        
        // items
        public array $list_items,
        
    ) {}

    public static function fromRequest(CreateOrderShippingRequest $request): self
    {
       
        $data = $request->validated();
        return new self(
            provider: $data['provider'],
            order_id: $data['order_id'],

            sender_name: $data['from_name'],
            sender_address: $data['from_address'],
            sender_phone: $data['from_phone'],
            sender_email: '',

            sender_province: $data['from_province'],
            sender_district: $data['from_district'],

            receiver_name: $data['to_name'],
            receiver_address: $data['to_address'],
            receiver_phone: $data['to_phone'],
            receiver_email: '',

            receiver_province: $data['to_province'],
            receiver_district: $data['to_district'],

            weight: $data['weight'],
            length: $data['length'],
            width: $data['width'],
            height: $data['height'],

            cod_amount: $data['cod_amount'],

            list_items: $data['products'] ?? [],
        );
    }
}