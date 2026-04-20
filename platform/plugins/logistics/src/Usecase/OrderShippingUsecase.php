<?php

namespace Botble\Logistics\Usecase;

use Botble\Logistics\Repositories\Interfaces\OrderInterface;
use Botble\Logistics\Repositories\Interfaces\StoreInterface;
use Botble\Logistics\Repositories\Interfaces\OrderAddressInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingProviderInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInformationInterface;
use Botble\Logistics\DTO\infAddressDTO;
use Illuminate\Support\Facades\DB;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\DTO\ShippingShowOrderDTO;
use Botble\Logistics\Services\Mappers\ProvinceMapper;
use Botble\Logistics\Services\Mappers\DistrictMapper;
use Botble\Logistics\Exceptions\ShippingException;

class OrderShippingUsecase 
{
    public function __construct( 
        private OrderInterface $OrderInterface,
        private StoreInterface $StoreInterface,
        private OrderAddressInterface $OrderAddressInterface,
        private ShippingProviderInterface $ShippingProviderInterface,
        private ShippingOrderInterface $shippingOrderInterface,
        private ShippingOrderInformationInterface $shippingOrderInformationInterface,
        private ProvinceMapper $provinceMapper,
        private DistrictMapper $districtMapper,
         )
        {}

    public function informationFrom($id) : infAddressDTO
    {
        $order = $this->OrderInterface->find($id);
        if($order->store_id !== null ){
            $store = $this->StoreInterface->findByCustomerId($order->store_id);
        }else{
            $store = DB::table('settings')
                ->where('key', 'like', 'ecommerce_store_%')
                ->pluck('value', 'key')
                ->mapWithKeys(function ($value, $key) {
                    $newKey = str_replace('ecommerce_store_', '', $key);
                    return [$newKey => $value];
                });
            $store = (object) $store->toArray();
        }
        return new infAddressDTO(
            name : $store->name,
            phone : $store->phone,
            address : $store->address,
            state_id : $store->state,
            city_id : $store->city,
            // ward_id : $store->ward_id,
        );
    }
    public function informationTo($id) : infAddressDTO
    {
        $orderAddress = $this->OrderAddressInterface->findByOrderID($id);
        return new infAddressDTO(
            name: $orderAddress->name,
            phone: $orderAddress->phone,
            address: $orderAddress->address,
            state_id: $orderAddress->state,
            city_id: $orderAddress->city,
            // ward_id: $orderAddress->ward_id,
        );
    }
    public function shippingProvider()
    {
        return $this->ShippingProviderInterface->all();
    }

    public function products($id){
        $product_order = DB::table('ec_order_product')->where('order_id', $id)->get();
        $data=[];
        foreach($product_order as $items){
            $product = DB::table('ec_products')->where('id',$items->product_id)->first();
            $data[] = [
                'name' => $items->product_name,
                'image' => $items->product_image,
                'qty' => $items->qty,
                'price' => $items->price,
                'height' => $product->height,
                'length' => $product->length,
                'weight' => $product->weight,
                'width' => $product->wide,
            ];
        }
        return $data;
    }

    public function informationOrderShipping($id): ShippingShowOrderDTO
    {
        $order_shipping = $this->shippingOrderInterface->findByOrderId($id);
        if(!$order_shipping){
            throw new ShippingException(
                message: 'không tồn tại đơn ship',
                provider: ""
            );
        }
        $order_shipping_info = $this->shippingOrderInformationInterface->findOrderShippingId($order_shipping->id);
        $list = $this->products($id);

        // name province
        $sender_province = $this->provinceMapper->mapToState($order_shipping->provider,$order_shipping_info->from_province);
        $receiver_province = $this->provinceMapper->mapToState($order_shipping->provider,$order_shipping_info->to_province);

        // name district
        $sender_district = $this->districtMapper->mapToCity($order_shipping->provider,$order_shipping_info->from_district);
        $receiver_district = $this->districtMapper->mapToCity($order_shipping->provider,$order_shipping_info->to_district);
       

        $shipping = ShippingFactory::make($order_shipping->provider);


        $provider =  $this->ShippingProviderInterface->findCode($order_shipping->provider);
        return new ShippingShowOrderDTO(
            provider: $provider->name,
            provider_code: $order_shipping->provider,
            order_id: $order_shipping->order_id,
            total_fee: $order_shipping->total_fee,
            status: $order_shipping->status->value,
            status_name: $order_shipping->status_name,
            localion_currenty: $order_shipping->localion_currenty,

            sender_name: $order_shipping_info->from_name,
            sender_address: $order_shipping_info->from_address,
            sender_phone: $order_shipping_info->from_phone,
            sender_email: "",
            sender_province: $sender_province->name,
            sender_district: $sender_district->name,

            receiver_name: $order_shipping_info->to_name,
            receiver_address: $order_shipping_info->to_address,
            receiver_phone: $order_shipping_info->to_phone,
            receiver_email: "",
            receiver_province: $receiver_province->name,
            receiver_district: $receiver_district->name,

            weight: $order_shipping_info->weight,
            length: $order_shipping_info->length,
            width: $order_shipping_info->width,
            height: $order_shipping_info->height,

            cod_amount: $order_shipping_info->cod_amount,
            code: $order_shipping->code,
            list_items: $list,
        );
    }

    public function shippingUnit($id)
    {
        $order = $this->OrderInterface->find($id);
        return $order->shipping_option;
    }
}