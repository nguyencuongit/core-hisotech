<?php
namespace Botble\ViettelPost\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\StoreLocator;
use Botble\Marketplace\Models\Store;
use Botble\ViettelPost\Services\ViettelPostApiService;
use Illuminate\Support\ServiceProvider;

class ViettelPostServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('plugins/viettel-post')->loadHelpers();
        $this->app->register(HookServiceProvider::class);
    }

    public function boot(): void
    {
        $this->setNamespace('plugins/viettel-post')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadMigrations()
            ->loadRoutes();

        $models = [OrderAddress::class, StoreLocator::class];

        if (class_exists(Address::class)) {
            $models[] = Address::class;
        }

        if (class_exists(Store::class)) {
            $models[] = Store::class;
        }

        foreach ($models as $model) {
            $model::saving(function ($item) {
                if (request()->has('ward')) {
                    $item->ward = request()->input('ward');
                }
                if (request()->has('address.ward')) {
                    $item->ward = request()->input('address.ward');
                }

                $idFields = ['state_id', 'city_id', 'ward_id'];
                foreach ($idFields as $field) {
                    if (request()->has($field)) {
                        $item->$field = request()->input($field);
                    }
                    if (request()->has("address.$field")) {
                        $item->$field = request()->input("address.$field");
                    }
                }

                $locationFields = ['state', 'city', 'ward'];
                foreach ($locationFields as $field) {
                    if (! empty($item->$field)) {
                        $item->$field = mb_convert_case(mb_strtolower($item->$field, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    }
                }

                if (setting('viettel_post_status') == 1) {
                    /** @var ViettelPostApiService $apiService */
                    $apiService = app(ViettelPostApiService::class);

                    if (empty($item->state_id) && ! empty($item->state)) {
                        $item->state_id = $apiService->getProvinceIdByName($item->state);
                    }

                    if (empty($item->city_id) && ! empty($item->city) && ! empty($item->state_id)) {
                        $item->city_id = $apiService->getDistrictIdByName($item->city, $item->state_id);
                    }

                    if (empty($item->ward_id) && ! empty($item->ward) && ! empty($item->city_id)) {
                        $item->ward_id = $apiService->getWardIdByName($item->ward, $item->city_id);
                    }
                }
            });
        }

        DashboardMenu::beforeRetrieving(function () {
            DashboardMenu::make()
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-viettel-post')
                        ->priority(5)
                        ->parentId('cms-plugins-ecommerce')
                        ->name('Viettel Post')
                        ->icon('ti ti-truck-delivery')
                        ->route('viettel-post.settings')
                        ->permissions(['viettel-post.settings'])
                );
        });
    }
}
