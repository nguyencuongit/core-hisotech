<?php
namespace Botble\ViettelPost\Shipping;

use Botble\Ecommerce\Models\Order;
use Botble\ViettelPost\Services\ViettelPostShippingService;

class ViettelPostShipping
{
    protected ViettelPostShippingService $shippingService;

    public function __construct()
    {
        $this->shippingService = app(ViettelPostShippingService::class);
    }

    
    public function getName(): string
    {
        return trans('plugins/viettel-post::viettel-post.shipping_method_name');
    }

   
    public function getDescription(): string
    {
        return trans('plugins/viettel-post::viettel-post.shipping_method_description');
    }

   
    public function getIcon(): string
    {
        return 'ti ti-truck-delivery';
    }

   
    public function isAvailable(): bool
    {
        return (bool) setting('viettel_post_status', false);
    }

   
    public function calculate($data): float
    {
        return $this->shippingService->calculateFee($data);
    }

    
    public function calculateForOrder(Order $order): float
    {
        return $this->shippingService->calculateFee($order);
    }

   
    public function getServices(): array
    {
        return $this->shippingService->getServices();
    }

  
    public function getSettings(): array
    {
        return [
            'partner_code'         => setting('viettel_post_partner_code'),
            'api_key'              => setting('viettel_post_api_key'),
            'shop_id'              => setting('viettel_post_shop_id'),
            'default_service'      => setting('viettel_post_default_service', 'VCN'),
            'auto_create_shipment' => setting('viettel_post_auto_create_shipment', false),
        ];
    }
}