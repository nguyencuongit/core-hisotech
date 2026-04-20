<?php
namespace Botble\Logistics\DTO;
class ShippingData
{
    public function __construct(
        public string $fromProvinceID,
        public string $fromDistrictID,
        public string $toProvinceID,
        public string $toDistrictID,
        public int $height,
        public int $length,
        public int $weight,
        public int $width,
    ) {}
}



