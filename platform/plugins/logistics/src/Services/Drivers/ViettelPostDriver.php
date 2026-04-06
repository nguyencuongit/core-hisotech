<?php
namespace Botble\Logistics\Services\Drivers;

use Botble\Logistics\Services\Contracts\ShippingServiceInterface;
use Botble\Logistics\DTO\ShippingData;
use Botble\Logistics\DTO\ShippingCreateDTO;
use Botble\Logistics\DTO\CancelOrderShippingDTO;
use Botble\Logistics\DTO\ShippingCreateResponseDTO;
use Botble\Logistics\Exceptions\ShippingException;
use Illuminate\Support\Facades\Http;


class ViettelPostDriver implements ShippingServiceInterface
{
    protected string $token;

    public function __construct()
    {
        $this->token = $this->getToken();
    }

    public function calculateFee(ShippingData $data): float
    {
        $data_raw =[
            "PRODUCT_WEIGHT"=>(int) $data->weight,
            "ORDER_SERVICE_ADD"=>"",
            "ORDER_SERVICE"=>"VCN",
            "SENDER_PROVINCE"=>(int) $data->fromProvinceID,
            "SENDER_WARD"=>(int) $data->fromDistrictID,
            "RECEIVER_PROVINCE"=>(int) $data->toProvinceID,
            "RECEIVER_WARD"=>(int) $data->toDistrictID,
            "PRODUCT_TYPE"=>"HH",
            "NATIONAL_TYPE"=>1,
           
        ];
         $response = Http::post(
            'https://partner.viettelpost.vn/v2/order/getPrice',
            $data_raw
        );
        if ($response->failed()) {
            throw new \Exception('GHN API error: ' . $response->json()['message']);
        }
        $ree = $response->json();
        return $ree['data']['MONEY_TOTAL'];
    }

    public function ShippingCreate(ShippingCreateDTO $data): ShippingCreateResponseDTO
    {
        $products = [];
        foreach ($data->list_items as $items){
            $products[] = [
                    "PRODUCT_NAME" => $items['name'],
                    "PRODUCT_PRICE" =>(int) $items['price'],
                    "PRODUCT_WEIGHT" =>(int) $items['weight'],
                    "PRODUCT_QUANTITY" =>(int) $items['qty'],
                    "PRODUCT_TYPE" => "HH",
                ];
        }
        $data_raw = [
            "ORDER_NUMBER" => "12",
            "GROUPADDRESS_ID" => 10702213, //storeID
            "CUS_ID" => 722,
            "DELIVERY_DATE" => "11/10/2018 15:09:52",

            "SENDER_FULLNAME" => $data->sender_name,
            "SENDER_ADDRESS" => $data->sender_address,
            "SENDER_PHONE" => $data->sender_phone,
            "SENDER_EMAIL" => $data->sender_email,

            "SENDER_WARD" =>(int) $data->sender_district,
            "SENDER_PROVINCE" =>(int) $data->sender_province,

            "SENDER_LATITUDE" => 0,
            "SENDER_LONGITUDE" => 0,

            "RECEIVER_FULLNAME" => $data->receiver_name,
            "RECEIVER_ADDRESS" => $data->receiver_address,
            "RECEIVER_PHONE" => $data->receiver_phone,
            "RECEIVER_EMAIL" => $data->receiver_email,

            "RECEIVER_WARD" =>(int) $data->receiver_district,
            "RECEIVER_PROVINCE" =>(int) $data->receiver_province,

            "RECEIVER_LATITUDE" => 0,
            "RECEIVER_LONGITUDE" => 0,
            "PRODUCT_TYPE" => "HH",
            "ORDER_PAYMENT" => 3,
            "ORDER_SERVICE" => "VCN",
            "ORDER_SERVICE_ADD" => "",
            "ORDER_VOUCHER" => "",
            
            "ORDER_NOTE" => "cho xem hàng, không cho thử",

            "MONEY_COLLECTION" => 0,   // tiền thu hộ
            "MONEY_TOTALFEE" => 0,
            "MONEY_FEECOD" => 0,
            "MONEY_FEEVAS" => 0,
            "MONEY_FEEINSURRANCE" => 0,
            "MONEY_FEE" => 0,
            "MONEY_FEEOTHER" => 0,
            "MONEY_TOTALVAT" => 0,
            "MONEY_TOTAL" => 0,

            "LIST_ITEM" => $products,
        ];
        $response = Http::withHeaders(['TOKEN' => $this->token,])
        ->post('https://partner.viettelpost.vn/v2/order/createOrder',$data_raw);
        $data = $response->json();
        
        if (!isset($data['data'])) {
            throw new ShippingException(
                message: 'Không thể tạo đơn ViettelPost',
                rawMessage: $data['message'] ?? null,     
                provider: 'viettelpost'
            );
        }

        return new ShippingCreateResponseDTO(
            order_code: $data['data']['ORDER_NUMBER'],
            total_fee: $data['data']['MONEY_TOTAL'],
            expected_delivery_time: now(),
        );
    }

    public function cancelOrderShipping($code): CancelOrderShippingDTO
    {
        $data_raw = [
            "TYPE" => 4,
            "ORDER_NUMBER" => $code,
            "NOTE" => "Khách hàng hủy đơn"
        ];

        $response = Http::withHeaders([
            'TOKEN' => $this->token,
            'accept' => '*/*',
            'Content-Type' => 'application/json',
            'Cookie' => 'SERVERID=2',
        ])->post(
            'https://partner.viettelpost.vn/v2/order/UpdateOrder',
            $data_raw
        );

        if ($response->json()['error']) {
            throw new ShippingException(
                message: 'Không thể huỷ đơn ViettelPost',
                rawMessage: $data['message'] ?? null,     
                provider: 'viettelpost'
            );
        }
        return new CancelOrderShippingDTO(
            success: true,
            message: "Huỷ đơn hàng ViettelPost thành công",
        );
    }

    public function getToken(){
         $response = Http::post(
            'https://partner.viettelpost.vn/v2/user/Login',
            [
                'USERNAME' => '0348952451',
                'PASSWORD' => 'Mc12101997@',
            ]
        );
        if ($response->failed()) {
            throw new \Exception('GHN API error: ' . $response->json());
        }
        $token = $response->json()['data']['token'];
        return $token;
    }

    public function getProvince(){
        $response = Http::withHeaders([
            'Cookie' => 'SERVERID=2; SERVERID=2',
        ])->get('https://partnerdev.viettelpost.vn/v3/categories/listProvinceNew');
        if ($response->failed()) {
            throw new \Exception('GHN API error: ' . $response->json()['message']);
        }
        $res = $response->json()['data'];
        $data=[];
        foreach($res as $items){
            $data[] = [
                "ProvinceID" => $items['PROVINCE_ID'],
                "ProvinceName" => $items['PROVINCE_NAME'],
            ];
        }
        return $data;
    }

    public function getDistrict($id){
       $response = Http::withHeaders([
        'Cookie' => 'SERVERID=2; SERVERID=2',
        ])->get(
            'https://partnerdev.viettelpost.vn/v3/categories/listWardsNew',
            [
                'provinceId' =>$id
            ]
        );
        $res = $response->json()['data'];
        $data = [];
        foreach($res as $items){
            $data[] = [
                "DistrictID" => $items['WARDS_ID'],
                "DistrictName" => $items['WARDS_NAME'],
            ];
        }
        return $data;
    }
}