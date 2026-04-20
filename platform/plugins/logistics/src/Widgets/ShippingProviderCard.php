<?php

namespace Botble\Logistics\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Logistics\Models\shippingProvider;
use Carbon\CarbonPeriod;

class ShippingProviderCard extends Card
{
    public function getColumns(): int
    {
        return 6; 
    }

    public function getOptions(): array
    {
        $data = shippingProvider::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as revenue')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
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

    $total = shippingProvider::query()
        ->whereBetween('created_at', [$this->startDate, $this->endDate])
        ->count();

    $previousTotal = shippingProvider::query()
        ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
        ->count();

    $result = $total - $previousTotal;

    $names = shippingProvider::query()
        ->whereBetween('created_at', [$this->startDate, $this->endDate])
        ->pluck('name')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    $this->chartColor = $result > 0 ? '#4ade80' : '#ff5b5b';

    return array_merge(parent::getViewData(), [
        'content' => view(
            'plugins/logistics::admin.reports.widgets.shipping-provider-card',
            [
                'revenue' => $total,
                'result' => $result,
                'names' => $names,
            ]
        )->render(),
    ]);
}

    public function getLabel(): string
    {
        return trans('plugins/logistics::logistics.reports.provider');
    }
}