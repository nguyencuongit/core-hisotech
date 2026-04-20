<?php

namespace Botble\Logistics\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Logistics\Enums\ShippingStatus;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Logistics\Models\shippingOrder;
use Carbon\CarbonPeriod;

class FeeShippingCard extends Card
{
    public function getColumns(): int
    {
        return 6; 
    }
    public function getOptions(): array
    {
        $data = [];
        $data = shippingOrder::query()
        ->selectRaw('DATE(created_at) as date, SUM(total_fee) as revenue')
        ->whereBetween('created_at', [$this->startDate, $this->endDate])
        ->where('status', '!=', ShippingStatus::CANCEL) 
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
        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;

        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $days = $currentPeriod->count();

        $previousStartDate = (clone $startDate)->subDays($days);
        $previousEndDate = (clone $endDate)->subDays($days);

        $currentRevenue = shippingOrder::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', ShippingStatus::CANCEL)
            ->sum('total_fee');

        $previousRevenue = shippingOrder::query()
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where('status', '!=', ShippingStatus::CANCEL)
            ->sum('total_fee');

        if ($previousRevenue > 0) {
            $result = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
        } elseif ($currentRevenue > 0) {
            $result = 100;
        } elseif ($previousRevenue > 0) {
            $result = -100;
        } else {
            $result = 0;
        }

        $this->chartColor = $result > 0 ? '#4ade80' : '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/logistics::admin.reports.widgets.fee-shipping-card',
                [
                    'revenue' => $currentRevenue,
                    'result' => round($result, 2),
                ]
            )->render(),
        ]);
    }

    public function getLabel(): string
    {
        return trans('plugins/logistics::logistics.reports.fee');
    }
}
