<?php

namespace Botble\Logistics\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Logistics\Enums\ShippingStatus;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Logistics\Models\shippingOrder;
use Carbon\CarbonPeriod;

class DeliveredShippingCard extends Card
{
    public function getOptions(): array
    {
        $data = [];
        
        $data = shippingOrder::query()
        ->selectRaw('DATE(created_at) as date, COUNT(*) as revenue')
        ->whereBetween('created_at', [$this->startDate, $this->endDate])
        ->where('status', ShippingStatus::DELIVERED)
        ->groupBy('date')
        ->pluck('revenue')
        ->toArray();

        return [
            'series' => [
                [
                    'data' => $data,
                ],
            ],
        ];
    }

    public function getViewData(): array
    {
        $total = shippingOrder::query()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', ShippingStatus::DELIVERED)
            ->count();

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/logistics::admin.reports.widgets.delivered-shipping-card',
                [
                    'revenue' => $total,
                    'result' => 0,
                ]
            )->render(),
        ]);
    }

    public function getLabel(): string
    {
        return trans('plugins/logistics::logistics.reports.delivered');
    }
}
