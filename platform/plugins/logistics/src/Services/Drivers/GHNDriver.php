<?php
namespace Botble\Logistics\Services\Drivers;

use Botble\Logistics\Services\Contracts\ShippingServiceInterface;
use Botble\Logistics\DTO\ShippingData;
use Botble\Logistics\DTO\ShippingCreateDTO;
use Illuminate\Support\Facades\Http;
use Botble\Logistics\DTO\ShippingCreateResponse;


class GHNDriver implements ShippingServiceInterface
{
    protected string $token="a2cf9c0b-28bb-11f1-adaf-eae2af87b828";
    protected string $shopId="6347225";
    protected string $baseUrl = "https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee";
    //  public function __construct()
    // {
    //     $this->token = config('services.ghn.token');
    //     $this->shopId = config('services.ghn.shop_id');
    //     $this->baseUrl = config('services.ghn.url');
    // }

    public function calculateFee(ShippingData $data): float
    {
        $data_raw =[
            "from_district_id"=>(int) $data->fromDistrictID,
            "from_ward_code"=>$data->fromWardID,
            "service_id"=>53321,
            "service_type_id"=>3,
            "to_district_id"=>(int) $data->toDistrictID,
            "to_ward_code"=>$data->toWardID,
            "height"=>(int) $data->height,
            "length"=>(int) $data->length,
            "weight"=>(int) $data->weight,
            "width"=>(int) $data->width,
        ];
        $response = Http::withHeaders([
            'Token' => $this->token,
            'ShopId' => $this->shopId,
        ])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', $data_raw);
        if ($response->failed()) {
            throw new \Exception('GHN API error: ' . $response->json()['message']);
        }
        $ree = $response->json();
        return $ree['data']['total'];
    }
    public function ShippingCreate(ShippingCreateDTO $data): ShippingCreateResponse
    {
        $data_raw = [
            "payment_type_id" => 2,
            "note" => "Tintest 123",
            "required_note" => "KHONGCHOXEMHANG",

            "from_name" => "TinTest124",
            "from_phone" => "0987654321",
            "from_address" => "72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh, Vietnam",
            "from_ward_name" => "Phường 14",
            "from_district_name" => "Quận 10",
            "from_province_name" => "HCM",

            "return_phone" => "0332190444",
            "return_address" => "39 NTT",
            "return_district_id" => null,
            "return_ward_code" => "",

            "client_order_code" => "",

            "to_name" => "TinTest124",
            "to_phone" => "0987654321",
            "to_address" => "72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh, Vietnam",
            "to_ward_code" => "20308",
            "to_district_id" => 1444,

            "cod_amount" => 200000,  // tiền cần thu.
            // "content" => "Theo New York Times",
            "weight" => 200,
            "length" => 1,
            "width" => 19,
            "height" => 10,
            // "pick_station_id" => 1444,   
            "deliver_station_id" => null,
            "insurance_value" => 10000000, // tiền tổng đơn hàng
            "service_id" => 0,
            "service_type_id" => 2,
            "coupon" => null,
            "pick_shift" => [2],
            "items" => [
                'name' => "Áo Polo",
                'code' => "Polo123",
                'quantity' => 1,
                'price' => 10000,
                'length' => 2,
                'width' => 2,
                'height' => 2,
                'weight' => 2,
            ],
        ];

        $response = Http::withHeaders([
            'Token' => $this->token,
            'ShopId' => $this->shopId,
        ])->post("https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create", $data_raw);

        if ($response->failed()) {
            throw new \Exception('GHN API error: ' . $response->json()['message']);
        }

        $data = $response->json()['data'];

        
        return [
            'order_code' => $data->order_code,
            'total_fee' => $data->total_fee,
            'expected_delivery_time' => $data->expected_delivery_time,
        ]; 
    }

    public function getProvince(){
        $response = Http::withHeaders([
            'Token' => $this->token,
        ])->post("https://online-gateway.ghn.vn/shiip/public-api/master-data/province");
        if ($response->failed()) {
            throw new \Exception('GHN API error: ' . $response->json()['message']);
        }
        $res =  $response->json()['data'];
        $data=[];
        foreach($res as $items){
            $data[] = [
                "ProvinceID" => $items['ProvinceID'],
                "ProvinceName" => $items['ProvinceName'],
            ];
        }
        return $data;
    }

    public function getDistrict($id){
        $provinceId = (int) $id;
        $response = Http::withHeaders([
            'Token' => $this->token,
        ])->post("https://online-gateway.ghn.vn/shiip/public-api/master-data/district",["province_id"=>$provinceId]);
        $res = $response->json()['data'];
        
        $data = [];
        foreach($res as $items){
            $data[] = [
                "DistrictID" => $items['DistrictID'],
                "DistrictName" => $items['DistrictName'],
            ];
        }
        return $data;
    }

    public function getWard($id){
        $district_id = (int) $id;
        $response = Http::withHeaders([
            'Token' => $this->token,
        ])->post("https://online-gateway.ghn.vn/shiip/public-api/master-data/ward?district_id",["district_id"=>$district_id]);
        $res = $response->json()['data'];
        $data = [];
        foreach($res as $items){
            $data[] = [
                "WARDS_ID" => $items['WardCode'],
                "WARDS_NAME" => $items['WardName'],
            ];
        }
        return $data;
    }
}