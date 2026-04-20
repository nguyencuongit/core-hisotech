<?php

namespace Botble\Logistics\Listeners;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Logistics\Facades\LogisticHelper;

use Botble\Logistics\Widgets\CreatedShippingCard;
use Botble\Logistics\Widgets\DeliveredShippingCard;
use Botble\Logistics\Widgets\ShippingCard;
use Botble\Logistics\Widgets\FailedShippingCard;
use Botble\Logistics\Widgets\FeeShippingCard;
use Botble\Logistics\Widgets\ShippingProviderCard;
use Botble\Logistics\Widgets\ShippingProviderOrdersChart;
use Botble\Logistics\Widgets\ShippingProviderFeeChart;
use Illuminate\Support\Facades\Auth;

class RegisterLogisticWidget
{
    public function handle(RenderingAdminWidgetEvent $event): void
    {
        $allWidgets = [
            // Financial Metrics (Top Row)
            CreatedShippingCard::class,
            ShippingCard::class,
            DeliveredShippingCard::class,
            FailedShippingCard::class,

            FeeShippingCard::class,
            ShippingProviderCard::class,

            ShippingProviderOrdersChart::class,
            ShippingProviderFeeChart::class,
           
        ];

        // Filter widgets based on user preferences
        $enabledWidgets = $this->getEnabledWidgets($allWidgets);

        $event->widget->register($enabledWidgets, 'logistics');
    }

    protected function getEnabledWidgets(array $allWidgets): array
    {
        if (! Auth::check()) {
            return $allWidgets;
        }

        $userId = Auth::id();
        $settingKey = "ecommerce_report_widgets_user_{$userId}";

        $userPreferences = setting($settingKey);

        if (is_string($userPreferences)) {
            $userPreferences = json_decode($userPreferences, true) ?: [];
        }

        if (empty($userPreferences)) {
            return $allWidgets;
        }

        return array_filter($allWidgets, function ($widget) use ($userPreferences) {
            return in_array($widget, $userPreferences);
        });
    }
}
