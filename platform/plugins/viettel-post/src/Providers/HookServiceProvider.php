<?php
namespace Botble\ViettelPost\Providers;

use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Currency;
use Botble\ViettelPost\Services\ViettelPostApiService;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(SHIPPING_METHODS_SETTINGS_PAGE, [$this, 'addSettings'], 3);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == ShippingMethodEnum::class) {
                $values['VIETTEL_POST'] = VIETTEL_POST_SHIPPING_METHOD_NAME;
            }

            return $values;
        }, 3, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == ShippingMethodEnum::class && $value == VIETTEL_POST_SHIPPING_METHOD_NAME) {
                return 'Viettel Post';
            }

            return $value;
        }, 3, 2);

        add_filter('handle_shipping_fee', [$this, 'handleShippingFee'], 12, 2);

        if (setting('viettel_post_status') == 1) {
            add_filter(
                'ecommerce_checkout_address_form_after_city_field',
                function ($content, $sessionCheckoutData) {
                    return $content . view('plugins/viettel-post::checkout.address-form-extra', compact('sessionCheckoutData'))->render();
                },
                11,
                2
            );

            add_filter(THEME_FRONT_FOOTER, function ($content) {
                return $content . view('plugins/viettel-post::partials.address-script-global')->render();
            }, 11);

            add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function ($content) {
                return $content . view('plugins/viettel-post::partials.address-script-global')->render();
            }, 11);

            add_filter('ecommerce_checkout_footer', function ($content) {
                return $content . view('plugins/viettel-post::partials.address-script-global')->render();
            }, 11);

            add_filter('marketplace_store_form_after_info', function ($content, $store) {
                return $content . view('plugins/viettel-post::partials.store-inventory', compact('store'))->render();
            }, 11, 2);
        }
    }

    public function addSettings(?string $settings): string
    {
        return $settings . view('plugins/viettel-post::settings.shipping-method')->render();
    }

    public function handleShippingFee(array $result, array $data): array
    {
        if (! $this->app->runningInConsole() && setting('viettel_post_status') == 1) {
            /** @var ViettelPostApiService $apiService */
            $apiService = $this->app->make(ViettelPostApiService::class);

            $weight = $data['weight'] ?? Cart::weight();
            if ($weight <= 0) {
                $weight = 1000;
            }

            $orderAmount = $data['order_total'] ?? Cart::rawTotal();

            $provinceId = (int) ($data['state_id'] ?? 0);
            $districtId = (int) ($data['city_id'] ?? 0);
            $wardId     = (int) ($data['ward_id'] ?? 0);

            if (! $provinceId && ! empty($data['state'])) {
                $provinceId = $apiService->getProvinceIdByName($data['state']);
            }
            if (! $districtId && ! empty($data['city']) && $provinceId) {
                $districtId = $apiService->getDistrictIdByName($data['city'], $provinceId);
            }
            if (! $wardId && ! empty($data['ward']) && $districtId) {
                $wardId = $apiService->getWardIdByName($data['ward'], $districtId);
            }

            $senderProvinceId = 0;
            $senderDistrictId = 0;

            $useStoreAddress = setting('viettel_post_use_store_address') == 1;
            $origin          = $data['origin'] ?? [];

            if ($useStoreAddress && ! empty($origin)) {
                $originState = $origin['state'] ?? null;
                $originCity  = $origin['city'] ?? null;

                if ($originState) {
                    $senderProvinceId = $apiService->getProvinceIdByName($originState);
                }
                if ($originCity && $senderProvinceId) {
                    $senderDistrictId = $apiService->getDistrictIdByName($originCity, $senderProvinceId);
                }
            }

            $feeInVnd = $apiService->calculateFee([
                'sender_province_id' => $senderProvinceId,
                'sender_district_id' => $senderDistrictId,
                'province_id'        => $provinceId,
                'district_id'        => $districtId,
                'ward_id'            => $wardId,
                'weight'             => $weight,
                'price'              => $orderAmount,
            ]);

            $vndCurrency       = Currency::where('title', 'VND')->first();
            $baseCurrencyPrice = $feeInVnd;

            if ($vndCurrency && $vndCurrency->exchange_rate > 0) {
                $baseCurrencyPrice = $feeInVnd / $vndCurrency->exchange_rate;
            }

            $result[VIETTEL_POST_SHIPPING_METHOD_NAME] = [
                VIETTEL_POST_SHIPPING_METHOD_NAME => [
                    'name'  => 'Viettel Post - Giao hàng nhanh',
                    'price' => $baseCurrencyPrice,
                ],
            ];
        }

        return $result;
    }
}