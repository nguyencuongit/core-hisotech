<?php
namespace Botble\Logistics\Usecase;

use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInterface;
use Botble\Logistics\Repositories\Interfaces\OrderShippingInterface;

use Illuminate\Support\Facades\Log;
use Botble\Logistics\Enums\ShipmentStatus;
use Botble\Logistics\Events\ShippingOrderStatusUpdated;

class WebhookUsecase
{
    public function __construct( 
        private ShippingOrderInterface $shippingOrderInterface,
        private OrderShippingInterface $orderShippingInterface,

    ){}
    public function webhook(array $payload, $provider){
        $shipping = ShippingFactory::make($provider);
        $data_webhook = $shipping->webhook($payload);
        if (!$data_webhook) {
            Log::channel('logistics_webhook')->debug('Webhook skipped (status not handled)', [
                'provider' => $provider,
                'payload'  => $payload,
            ]);
            return;
        }

        if (empty($data_webhook->orderCode)) {
            Log::channel('logistics_webhook')->error('Webhook missing orderCode', [
                'provider' => $provider,
                'data_webhook' => $data_webhook,
                'payload' => $payload,
            ]);
            return;
        }
        $exist_order_code = $this->shippingOrderInterface->existsOrderCode($data_webhook->orderCode);
        if(!$exist_order_code){
            Log::channel('logistics_webhook')->warning('Webhook order_code not found', [
                'provider'   => $provider,
                'order_code' => $data_webhook->orderCode ?? null,
                'payload'    => $payload,
            ]);
            return;
        }

        $order_shipping = $this->shippingOrderInterface->updateStatusWebhook($data_webhook);
        Log::channel('logistics_webhook')->info('Webhook processed successfully', [
            'provider'   => $provider,
            'order_code' => $data_webhook->orderCode,
            'status'     => $data_webhook->status ?? null,
        ]);

        $order_id = $order_shipping->order_id;

        $this->evenUpdateShipment($order_shipping->order_id, $data_webhook->status);

    }
    private function evenUpdateShipment($order_id, $status)
    {
        $status = match ($status) {
            'created' => ShipmentStatus::READY_TO_SHIP,
            'picked' => ShipmentStatus::PICKED,
            'shipping' => ShipmentStatus::DELIVERING,
            'delivered' => ShipmentStatus::DELIVERED,
            'canceled', 'failed' => ShipmentStatus::CANCELED,
        };
        $shipment = $this->orderShippingInterface->findOrderId($order_id);
        event(new ShippingOrderStatusUpdated(
                    $shipment,
                    $status
                ));
    }
}