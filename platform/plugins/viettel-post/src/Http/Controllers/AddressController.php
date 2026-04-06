<?php
namespace Botble\ViettelPost\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function __construct(protected \Botble\ViettelPost\Services\ViettelPostApiService $apiService)
    {
    }

    public function getProvinces()
    {
        $data = $this->apiService->getProvinces();

        return collect($data)->map(function ($item) {
            return [
                'id'   => $item['PROVINCE_ID'] ?? $item['id'] ?? null,
                'name' => $item['PROVINCE_NAME'] ?? $item['name'] ?? '',
            ];
        })->filter(fn($i) => $i['id'])->values();
    }

    public function getDistricts($provinceId)
    {
        $data = $this->apiService->getDistricts($provinceId);

        return collect($data)->map(function ($item) {
            return [
                'id'   => $item['DISTRICT_ID'] ?? $item['id'] ?? null,
                'name' => $item['DISTRICT_NAME'] ?? $item['name'] ?? '',
            ];
        })->filter(fn($i) => $i['id'])->values();
    }

    // public function getWards($districtId)
    // {
    //     $data = $this->apiService->getWards($districtId);

    //     return collect($data)->map(function ($item) {
    //         return [
    //             'id'   => $item['WARDS_ID'] ?? $item['id'] ?? null,
    //             'name' => $item['WARDS_NAME'] ?? $item['name'] ?? '',
    //         ];
    //     })->filter(fn($i) => $i['id'])->values();
    // }

    public function search(Request $request)
    {
        $q = $request->get('q');
        // Logic tìm kiếm địa chỉ
        return [];
    }
}