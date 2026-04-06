<?php 
namespace Botble\Logistics\DTO;

class infAddressDTO
{
    public function __construct(
        public ?string $name,
        public ?string $phone,
        public ?string $address,
        public ?int $state_id,
        public ?int $city_id,
    ) {}
}