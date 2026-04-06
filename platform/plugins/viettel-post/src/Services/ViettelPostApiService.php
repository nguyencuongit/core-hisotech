<?php
namespace Botble\ViettelPost\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
// use Botble\Marketplace\Models\Store;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViettelPostApiService
{
    protected string $baseUrl = 'https://partner.viettelpost.vn/v2';

    public function getToken(): ?string
    {
        $username = setting('viettel_post_username');
        $password = setting('viettel_post_password');

        if (! $username || ! $password) {
            return null;
        }

        return Cache::remember('viettel_post_token', now()->addHours(23), function () use ($username, $password) {
            try {
                $response = Http::post($this->baseUrl . '/user/login', [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ]);

                if ($response->successful() && isset($response->json()['data']['token'])) {
                    return $response->json()['data']['token'];
                }
            } catch (Exception $e) {
                Log::error('ViettelPost Login Error: ' . $e->getMessage());
            }

            return null;
        });
    }

    public function getServices(): array
    {
        $token = $this->getToken();
        if (! $token) {
            return [];
        }

        return Cache::remember('viettel_post_services', now()->addDay(), function () use ($token) {
            try {
                $response = Http::withHeaders(['Token' => $token])->get($this->baseUrl . '/categories/listService');
                if ($response->successful() && isset($response->json()['data'])) {
                    return $response->json()['data'];
                }
            } catch (Exception $e) {
                Log::error('ViettelPost Get Services Error: ' . $e->getMessage());
            }
            return [];
        });
    }

    public function calculateFee(array $data): float
    {
        $token = $this->getToken();
        if (! $token) {
            return 0;
        }

        try {
            $senderProvinceId = (int) ($data['sender_province_id'] ?? 0);
            $senderDistrictId = (int) ($data['sender_district_id'] ?? 0);

            if (! $senderProvinceId || ! $senderDistrictId) {
                $senderProvinceId = (int) setting('viettel_post_sender_province_id');
                $senderDistrictId = (int) setting('viettel_post_sender_district_id');
            }

            if (! $senderProvinceId || ! $senderDistrictId) {
                return 0;
            }

            $receiverProvinceId = (int) ($data['province_id'] ?? 0);
            $receiverDistrictId = (int) ($data['district_id'] ?? 0);
            $receiverWardId     = (int) ($data['ward_id'] ?? 0);

          
            $params = [
                'SENDER_PROVINCE'   => $senderProvinceId,
                'SENDER_DISTRICT'   => $senderDistrictId,
                'RECEIVER_PROVINCE' => $receiverProvinceId,
                'RECEIVER_DISTRICT' => $receiverDistrictId,
                'PRODUCT_WEIGHT'    => (int) ($data['weight'] ?? 1000),
                'PRODUCT_PRICE'     => (int) ($data['price'] ?? 0),
                'MONEY_COLLECTION'  => (int) ($data['price'] ?? 0),
                'TYPE'              => 1,
            ];


            $response = Http::withHeaders(['Token' => $token])->post($this->baseUrl . '/order/getPriceAll', $params);
            $services = $response->json(); 

            if (! $response->successful() || ! is_array($services) || empty($services)) {
                return 0;
            }

            foreach ($services as $service) {
                if (($service['MA_DV_CHINH'] ?? '') === 'SCN') {
                    $price = (float) ($service['GIA_CUOC'] ?? 0);
                    return $price;
                }
            }

            $price = (float) ($services[0]['GIA_CUOC'] ?? 0);
            return $price;

        } catch (Exception $e) {
        }

        return 0;
    }

    public function getProvinces(): array
    {
        return Cache::remember('viettel_post_provinces', now()->addWeek(), function () {
            try {
                $response = Http::get($this->baseUrl . '/categories/listProvince');
                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
            } catch (Exception $e) {
            }
            return [];
        });
    }

    public function getDistricts(int | string $provinceId): array
    {
        return Cache::remember('viettel_post_districts_' . $provinceId, now()->addWeek(), function () use ($provinceId) {
            try {
                $response = Http::get($this->baseUrl . '/categories/listDistrict', [
                    'provinceId' => $provinceId,
                ]);
                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
            } catch (Exception $e) {
            }
            return [];
        });
    }

    public function getWards(int | string $districtId): array
    {
        return Cache::remember('viettel_post_wards_' . $districtId, now()->addWeek(), function () use ($districtId) {
            try {
                $response = Http::get($this->baseUrl . '/categories/listWards', [
                    'districtId' => $districtId,
                ]);
                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
            } catch (Exception $e) {
            }
            return [];
        });
    }

    public function getProvinceIdByName(?string $name): int
    {
        if (! $name) {
            return 0;
        }

        $provinces = $this->getProvinces();

        $searchName = mb_strtoupper(trim($name), 'UTF-8');

        foreach ($provinces as $province) {
            $provinceName = mb_strtoupper($province['PROVINCE_NAME'] ?? '', 'UTF-8');

            if ($provinceName === $searchName) {
                return (int) ($province['PROVINCE_ID'] ?? 0);
            }
            if (mb_strpos($provinceName, $searchName) !== false ||
                mb_strpos($searchName, $provinceName) !== false) {
                return (int) ($province['PROVINCE_ID'] ?? 0);
            }
        }

        return 0;
    }

    public function getDistrictIdByName(?string $name, int $provinceId): int
    {
        if (! $name || ! $provinceId) {
            return 0;
        }

        $districts = $this->getDistricts($provinceId);

        $searchName = mb_strtoupper(trim($name), 'UTF-8');

        foreach ($districts as $district) {
            $districtName = mb_strtoupper($district['DISTRICT_NAME'] ?? '', 'UTF-8');

            if ($districtName === $searchName) {
                return (int) ($district['DISTRICT_ID'] ?? 0);
            }

            if (mb_strpos($districtName, $searchName) !== false ||
                mb_strpos($searchName, $districtName) !== false) {
                return (int) ($district['DISTRICT_ID'] ?? 0);
            }
        }

        return 0;
    }

   
    public function getWardIdByName(?string $name, int $districtId): int
    {
        if (! $name || ! $districtId) {
            return 0;
        }

        $wards = $this->getWards($districtId);
        foreach ($wards as $ward) {
            $wardName = $ward['WARDS_NAME'] ?? '';
            if (strcasecmp($wardName, $name) === 0 ||
                stripos($wardName, $name) !== false ||
                stripos($name, $wardName) !== false) {
                return (int) ($ward['WARDS_ID'] ?? 0);
            }
        }
        return 0;
    }

 
    public function getInventories(): array
    {
        $token = $this->getToken();
        if (! $token) {
            return [];
        }

        try {
            $response = Http::withHeaders(['Token' => $token])->get($this->baseUrl . '/user/listInventory');
            if ($response->successful()) {
                return $response->json()['data'] ?? $response->json() ?? [];
            }
        } catch (Exception $e) {
            Log::error('ViettelPost Get Inventories Error: ' . $e->getMessage());
        }
        return [];
    }

    public function registerInventory(array $data): array
    {
        $token = $this->getToken();
        if (! $token) {
            return ['error' => true, 'message' => 'Không có token xác thực'];
        }

        try {
            $wards    = $this->getWards($data['district_id']);
            $wardName = '';
            foreach ($wards as $ward) {
                if (($ward['WARDS_ID'] ?? '') == $data['ward_id']) {
                    $wardName = $ward['WARDS_NAME'] ?? '';
                    break;
                }
            }

            $params = [
                'PHONE'    => $data['phone'],
                'NAME'     => $data['name'],
                'ADDRESS'  => $data['address'],
                'WARDS_ID' => (int) ($data['ward_id'] ?? 0),
            ];


            $response = Http::withHeaders(['Token' => $token])->post($this->baseUrl . '/user/registerInventory', $params);


            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['status']) && $result['status'] == 200 && isset($result['data']) && is_array($result['data'])) {
                    $firstItem = $result['data'][0] ?? null;
                    if ($firstItem && isset($firstItem['groupaddressId'])) {
                        return ['GROUPADDRESS_ID' => $firstItem['groupaddressId'], 'name' => $firstItem['name']];
                    }
                }

                if (isset($result['data']['GROUPADDRESS_ID'])) {
                    return $result['data'];
                }
                if (isset($result['GROUPADDRESS_ID'])) {
                    return $result;
                }

                return ['error' => true, 'message' => $result['message'] ?? 'Đăng ký không thành công'];
            }
        } catch (Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }

        return ['error' => true, 'message' => 'Không thể kết nối đến Viettel Post'];
    }
}