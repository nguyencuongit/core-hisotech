<?php

namespace Botble\Logistics\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Logistics\Models\Logistics;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\ShippingMethodEnum;


// interface
use Botble\Logistics\Repositories\Interfaces\OrderAddressInterface;
use Botble\Logistics\Repositories\Interfaces\OrderInterface;
use Botble\Logistics\Repositories\Interfaces\StoreInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingProviderInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingDistrictMappingInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingProvinceMappingInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingOrderInformationInterface;
use Botble\Logistics\Repositories\Interfaces\OrderShippingInterface;


// repository
use Botble\Logistics\Repositories\Eloquent\OrderAddressRepository;
use Botble\Logistics\Repositories\Eloquent\OrderRepository;
use Botble\Logistics\Repositories\Eloquent\StoreRepository;
use Botble\Logistics\Repositories\Eloquent\ShippingProviderRepository;
use Botble\Logistics\Repositories\Eloquent\ShippingDistrictMappingRepository;
use Botble\Logistics\Repositories\Eloquent\ShippingProvinceMappingRepository;
use Botble\Logistics\Repositories\Eloquent\ShippingOrderRepository;
use Botble\Logistics\Repositories\Eloquent\ShippingOrderInformationRepository;
use Botble\Logistics\Repositories\Eloquent\OrderShippingRepository;

//model
use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Store;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Logistics\Models\shippingProvider;
use Botble\Logistics\Models\shippingDistrictMapping;
use Botble\Logistics\Models\shippingProvinceMapping;
use Botble\Logistics\Models\shippingOrder;
use Botble\Logistics\Models\shippingOrderInformation;
use Botble\Logistics\Models\Shipment;

//provider
use Botble\Logistics\Providers\EventServiceProvider;


class LogisticsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(OrderInterface::class, function () {
            return new OrderRepository(new Order());
        });
        $this->app->bind(StoreInterface::class, function () {
            return new StoreRepository(new Store());
        });
        $this->app->bind(OrderAddressInterface::class, function () {
            return new OrderAddressRepository(new OrderAddress());
        });
        $this->app->bind(ShippingProviderInterface::class, function () {
            return new ShippingProviderRepository(new shippingProvider());
        });
        $this->app->bind(ShippingDistrictMappingInterface::class, function () {
            return new ShippingDistrictMappingRepository(new shippingDistrictMapping());
        });
        $this->app->bind(ShippingProvinceMappingInterface::class, function () {
            return new ShippingProvinceMappingRepository(new shippingProvinceMapping());
        });
        $this->app->bind(ShippingOrderInterface::class, function () {
            return new ShippingOrderRepository(new shippingOrder());
        });
        $this->app->bind(ShippingOrderInformationInterface::class, function () {
            return new ShippingOrderInformationRepository(new shippingOrderInformation());
        });
        $this->app->bind(OrderShippingInterface::class, function () {
            return new OrderShippingRepository(new Shipment());
        });

    }

    public function boot(): void    
    {

        $this
            ->setNamespace('plugins/logistics')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadMigrations();
            
            if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Logistics::class, [
                    'name',
                ]);
            }
            
            DashboardMenu::default()->beforeRetrieving(function () {
                DashboardMenu::registerItem([
                    'id' => 'cms-plugins-logistics',
                    'priority' => 5,
                    'parent_id' => null,
                    'name' => 'plugins/logistics::logistics.name',
                    'icon' => 'ti ti-box',
                    'url' => route('logistics.index'),
                    'permissions' => ['logistics.index'],
                ]);
                DashboardMenu::registerItem([
                    'id' => 'cms-plugins-logistics-dashboard',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-logistics',
                    'name' => 'plugins/logistics::logistics.dashboard',
                    'icon' => 'ti ti-box',
                    'url' => route('logistics.report.index'),
                    'permissions' => ['logistics.index'],
                ]);
                DashboardMenu::registerItem([
                    'id' => 'cms-plugins-logistics-providers',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-logistics',
                    'name' => 'plugins/logistics::logistics.providers',
                    'icon' => 'ti ti-box',
                    'url' => route('logistics.providers.index'),
                    'permissions' => ['logistics.index'],
                ]);
                DashboardMenu::registerItem([
                    'id' => 'cms-plugins-logistics-create-order',
                    'priority' => 7,
                    'parent_id' => 'cms-plugins-logistics',
                    'name' => 'plugins/logistics::logistics.order',
                    'icon' => 'ti ti-box',
                    'url' => route('logistics.shipping.order.index'),
                    'permissions' => ['logistics.shipping.order'],
                ]);
               
            });
        $this->app->booted(function () {
            if (Cache::get('logistics_seeded_states')) {
                return;
            }
            $exists = DB::table('settings')
                ->where('key', 'logistics_seeded_states')
                ->exists();

            if ($exists) {
                 Cache::put('logistics_seeded_states', true, now()->addHours(24));
                return; 
            }
            $response = Http::withHeaders([
                'Cookie' => 'SERVERID=2; SERVERID=2',
            ])->get('https://partnerdev.viettelpost.vn/v3/categories/listProvinceNew');

            if ($response->failed()) {
                Log::error('Viettel API error', [
                    'body' => $response->body()
                ]);
                return;
            }
            $data = $response->json();

            foreach ($data['data'] ?? [] as $item) {
                $slug = Str::slug($item['PROVINCE_NAME']); 
                DB::table('states')->updateOrInsert(
                    ['slug' => $slug], 
                    [
                        'name' => $item['PROVINCE_NAME'],
                        'abbreviation' => $item['PROVINCE_CODE'],
                        'country_id' => 1,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            DB::table('settings')->updateOrInsert(
                ['key' => 'logistics_seeded_states'],
                [
                    'value' => 1,   
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });


        $this->app->register(EventServiceProvider::class);

        // chạy js location.js ở global
        $this->publishes([
            __DIR__ . '/../../public' => public_path('vendor/core/plugins/logistics'),
        ], 'public');
        Assets::addScriptsDirectly([
            'vendor/core/plugins/logistics/js/location.js',
        ]);
        Assets::addScriptsDirectly([
            'vendor/core/plugins/logistics/js/shipping-fee.js',
        ]);
       

        config([
            'logging.channels.logistics_webhook' => [
                'driver' => 'daily',
                'path' => storage_path('logs/logistics-webhook.log'),
                'level' => 'debug',
                'days' => 14,
            ],
        ]); 

        //
        add_filter('handle_shipping_fee',[$this, 'handleShippingFee'],12,2);
        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == ShippingMethodEnum::class) {
                $values['LOGISTICS'] = 'logistics';
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == ShippingMethodEnum::class && $value == 'logistics') {
                return 'logistics';
            }

            return $value;
        }, 2, 2);
    }
    public function handleShippingFee($result, array $data): array
    {
        $useCase = app(\Botble\Logistics\Usecase\ShippingFeeUsecase::class);
        return $useCase->calculateCheckout($data);
    }
}
