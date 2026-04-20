<?php
namespace Botble\Logistics\Services\Mappers;
use Botble\Logistics\Models\shippingDistrictMapping;
use Botble\Logistics\DTO\DistrictDTO;

class DistrictMapper
{
    public function map(string $provider, int $localId): int
    {
        return shippingDistrictMapping::where([
            'provider' => $provider,
            'city_id' => $localId
        ])->value('district_id');
    }

    public function mapToCity(string $provider, $districtId)
    {
        return shippingDistrictMapping::query()
        ->join('cities', 'cities.id', '=', 'shipping_district_mappings.city_id')
        ->where('shipping_district_mappings.provider', $provider)
        ->where('shipping_district_mappings.district_id', $districtId)
        ->select([
            'cities.name as name'
        ])
        ->first();
    }
    public static function mapViettelPost($data): DistrictDTO
    {
        return new DistrictDTO(
            id: $data['WARDS_ID'],
            name: $data['WARDS_NAME'],
        );
    }
}