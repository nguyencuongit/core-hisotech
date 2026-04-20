<?php

namespace Botble\Logistics\Usecase;
use Botble\Logistics\DTO\ShippingCreateDTO;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\Services\Mappers\ProvinceMapper;
use Botble\Logistics\Services\Mappers\DistrictMapper;
use Botble\Logistics\Services\Mappers\ShippingOrderInformationMapper;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInformationInterface;
use Botble\Logistics\Repositories\Interfaces\OrderShippingInterface;
use Botble\Logistics\Exceptions\ShippingException;
use Botble\Logistics\Enums\ShippingStatus;
use Botble\Logistics\Events\ShippingOrderStatusUpdated;
use Botble\Logistics\Enums\ShipmentStatus;
use Illuminate\Support\Facades\DB;

class CreateShippingUsecase 
{
    public function __construct( 
        private ProvinceMapper $provinceMapper,
        private DistrictMapper $districtMapper,
        private ShippingOrderInterface $shippingOrderInterface,
        private ShippingOrderInformationInterface $shippingOrderInformationInterface,
        private OrderShippingInterface $orderShippingInterface,
    ){}
    public function createShipping(ShippingCreateDTO $data)
    {
            $this->checkCreateOrderShipping($data->order_id, $data->provider);

            $this->mapAddress($data);

            $response = $this->callProviderAPI($data);

            $order = $this->saveOrder($data, $response);

            $this->saveOrderInformation($data, $order);

            $this->evenUpdateShipment($data->order_id);

            return $order;
    }

    private function mapAddress(ShippingCreateDTO $data): void
    {
        $data->sender_province = $this->provinceMapper->map(
            $data->provider,
            $data->sender_province
        );

        $data->receiver_province = $this->provinceMapper->map(
            $data->provider,
            $data->receiver_province
        );

        $data->sender_district = $this->districtMapper->map(
            $data->provider,
            $data->sender_district
        );

        $data->receiver_district = $this->districtMapper->map(
            $data->provider,
            $data->receiver_district
        );
    }

    private function callProviderAPI(ShippingCreateDTO $data)
    {
        $shipping = ShippingFactory::make($data->provider);

        return $shipping->ShippingCreate($data);
    }

    private function saveOrder(ShippingCreateDTO $data, $response)
    {
        return $this->shippingOrderInterface->create([
            "order_id" => $data->order_id,
            "provider" => $data->provider,
            "status" => ShippingStatus::CREATED,
            "code" => $response->order_code,
            "error" => null,
            "total_fee" => $response->total_fee,
        ]);
    }

    private function saveOrderInformation(ShippingCreateDTO $data, $order): void
    {
        if (!$order) return;
        $this->shippingOrderInformationInterface->create(
            ShippingOrderInformationMapper::toModel($data, $order->id)
        );
    }

    private function checkCreateOrderShipping($order_id, $provider){
        $existsOrderId = $this->shippingOrderInterface->existsOrderId($order_id);
        if($existsOrderId){
            throw new ShippingException(
                message: 'Không thể tạo đơn'.$provider.', Đơn ship đã tồn tại',
                provider: $provider
            );
        }
        return true;
    }


    private function evenUpdateShipment($order_id){
        $shipment = $this->orderShippingInterface->findOrderId($order_id);
        event(new ShippingOrderStatusUpdated(
                    $shipment,
                    ShipmentStatus::READY_TO_SHIP
                ));
    }
}