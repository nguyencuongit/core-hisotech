<?php
namespace Botble\Logistics\Services\Drivers;

use Botble\Logistics\Services\Contracts\ShippingServiceInterface;
use Botble\Logistics\DTO\ShippingData;
use Botble\Logistics\DTO\ShippingCreateDTO;
use Botble\Logistics\DTO\CancelOrderShippingDTO;
use Botble\Logistics\DTO\ShippingCreateResponseDTO;
use Botble\Logistics\DTO\WebhookDataDTO;
use Botble\Logistics\Exceptions\ShippingException;
use Illuminate\Support\Facades\Http;
use Botble\Logistics\Services\Mappers\ProvinceMapper;
use Botble\Logistics\Services\Mappers\DistrictMapper;
use Illuminate\Support\Facades\Cache;
use Botble\Logistics\Repositories\Interfaces\ShippingProviderInterface;
use Botble\Logistics\Enums\ShippingStatus;

class ViettelPostDriver implements ShippingServiceInterface
{

    public function __construct(
        private ShippingProviderInterface $shippingProviderInterface,
    ){}

    public function calculateFee(ShippingData $data): float
    {
        $config = $this->getProviderConfig();
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
            $config['url_fee'],
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
        $config = $this->getProviderConfig();
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
        $response = Http::withHeaders(['TOKEN' => $this->getToken(),])
        ->post($config['url_create'],$data_raw);
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

    public function cancelOrderShipping(string $code): CancelOrderShippingDTO
    {
        $config = $this->getProviderConfig();
        $data_raw = [
            "TYPE" => 4,
            "ORDER_NUMBER" => $code,
            "NOTE" => "Khách hàng hủy đơn"
        ];
        $response = Http::withHeaders([
            'TOKEN' => $this->getToken(),
            'accept' => '*/*',
            'Content-Type' => 'application/json',
            'Cookie' => 'SERVERID=2',
        ])->post(
            $config['url_cancel'],
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

    public function getToken()
    {
        return Cache::remember('viettelpost:token', 300, function () {
            $config = $this->getProviderConfig();
            $response = Http::post(
                $config['url_token'],
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
        });
    }

    public function getProvince()
    {
        $config = $this->getProviderConfig();
        $response = Http::withHeaders([
            'Cookie' => 'SERVERID=2; SERVERID=2',
        ])->get($config['url_province']);
        if ($response->failed()) {
            throw new \Exception('GHN API error: ' . $response->json()['message']);
        }
        $res = $response->json()['data'];
        $data=collect($res)
            ->map(fn ($item) => ProvinceMapper::mapViettelPost($item))
            ->toArray();
        return $data;
    }

    public function getDistrict($id_province)
    {
        $config = $this->getProviderConfig();
        $response = Http::withHeaders([
        'Cookie' => 'SERVERID=2; SERVERID=2',
        ])->get(
            $config['url_district'],
            [
                'provinceId' =>$id_province
            ]
        );
        $res = $response->json()['data'];
        $data = [];
        $data=collect($res)
            ->map(fn ($item) => DistrictMapper::mapViettelPost($item))
            ->toArray();
        return $data;
    }

    private function getProviderConfig(): array
    {
        return Cache::remember('provider:viettelpost', now()->addHours(24), function () {
            $provider = $this->shippingProviderInterface
                ->findCode('viettelpost');
            return $provider->information;
        });
    }

    public function webhook(array $payload): WebhookDataDTO
    {
        $data= $payload['DATA'];

        $status = match ( (int) $data['ORDER_STATUS']) {
            103, 104 => ShippingStatus::CREATED,

            200 => ShippingStatus::PICKED,

            300, 400, 500, 508, 550, 507 => ShippingStatus::SHIPPING,

            501 => ShippingStatus::DELIVERED,

            101, 107, 201, 503 => ShippingStatus::CANCEL,

            506, 515, 504 => ShippingStatus::FAILED,
        };

        if (!$status) {
            return null;
        }
        return new WebhookDataDTO(
            orderCode: $data['ORDER_NUMBER'],
            status: $status->value,
            statusName: $data['STATUS_NAME'],
            localionCurrenty: $data['LOCALION_CURRENTLY'],
        );
    }
}