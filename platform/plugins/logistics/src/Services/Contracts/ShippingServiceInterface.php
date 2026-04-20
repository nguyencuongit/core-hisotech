<?php
namespace Botble\Logistics\Services\Contracts;
use Botble\Logistics\DTO\ShippingData;
use Botble\Logistics\DTO\ShippingCreateDTO;
use Botble\Logistics\DTO\ShippingCreateResponseDTO;
use Botble\Logistics\DTO\CancelOrderShippingDTO;


interface ShippingServiceInterface
{
    public function calculateFee(ShippingData $data): float;
    public function ShippingCreate(ShippingCreateDTO $data): ShippingCreateResponseDTO;
    public function cancelOrderShipping(string $code): CancelOrderShippingDTO;
    // public function getToken();
    public function getProvince();
    public function getDistrict($id_province);
}