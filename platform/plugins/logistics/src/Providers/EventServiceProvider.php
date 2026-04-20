<?php

namespace Botble\Logistics\Providers;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Botble\Logistics\Listeners\RegisterLogisticWidget;
use Botble\Base\Events\RenderingAdminWidgetEvent;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Botble\Logistics\Events\ShippingOrderStatusUpdated::class => [
            \Botble\Logistics\Listeners\UpdateOrderStatusListener::class,
        ],

        RenderingAdminWidgetEvent::class => [
            RegisterLogisticWidget::class,
        ],
    ];
}