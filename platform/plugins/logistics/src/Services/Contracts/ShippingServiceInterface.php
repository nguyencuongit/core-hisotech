<?php
namespace Botble\Logistics\Services\Contracts;
use Botble\Logistics\DTO\ShippingData;
use Botble\Logistics\DTO\ShippingCreateDTO;
use Botble\Logistics\DTO\ShippingCreateResponseDTO;


interface ShippingServiceInterface
{
    public function calculateFee(ShippingData $data): float;
    public function ShippingCreate(ShippingCreateDTO $data): ShippingCreateResponseDTO;
}