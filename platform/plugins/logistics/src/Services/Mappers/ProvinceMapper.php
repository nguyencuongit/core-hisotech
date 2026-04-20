<?php
namespace Botble\Logistics\Services\Mappers;
use Botble\Logistics\Models\shippingProvinceMapping;
use Botble\Logistics\DTO\ProvinceDTO;

class ProvinceMapper
{
    public function map(string $provider, int $localId): int
    {
        return shippingProvinceMapping::where([
            'provider' => $provider,
            'state_id' => $localId
        ])->value('province_id');
    }

    public function mapToState(string $provider, $provinceId)
    {
        return ShippingProvinceMapping::query()
        ->join('states', 'states.id', '=', 'shipping_province_mappings.state_id')
        ->where('shipping_province_mappings.provider', $provider)
        ->where('shipping_province_mappings.province_id', $provinceId)
        ->select([
            'states.name as name'
        ])
        ->first();
    }

    public static function mapViettelPost($data): ProvinceDTO
    {
        return new ProvinceDTO(
            id: $data['PROVINCE_ID'],
            name: $data['PROVINCE_NAME']
        );
    }

}