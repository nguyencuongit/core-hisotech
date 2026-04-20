<?php

namespace Botble\Logistics\Widgets;

use Botble\Base\Widgets\Chart;
use Botble\Logistics\Models\shippingOrder;
use Illuminate\Support\Facades\DB;
use Botble\Logistics\Enums\ShippingStatus;


class ShippingProviderOrdersChart extends Chart
{
    protected int $columns = 6;

    protected string $type = 'donut';

    public function getLabel(): string
    {
        return trans('plugins/logistics::logistics.reports.shipping_provider_order');
    }

    public function getOptions(): array
    {
        $orders = shippingOrder::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->where('status', '!=', ShippingStatus::CANCEL)
            ->select([
                'provider',
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('provider')
            ->orderByDesc('total')
            ->get();

        $series = [];
        $labels = [];
        $colors = ['#4ade80', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];

        foreach ($orders as $order) {
            $provider = $order->provider ?: 'Unknown';

            $series[] = (int) $order->total;
            $labels[] = $provider . ' (' . $order->total . ')';
        }

        return [
            'series' => $series,
            'chart' => [
                'height' => 350,
                'type' => 'donut',
            ],
            'colors' => $colors,
            'labels' => $labels,
            'legend' => [
                'position' => 'bottom',
            ],
        ];
    }
}