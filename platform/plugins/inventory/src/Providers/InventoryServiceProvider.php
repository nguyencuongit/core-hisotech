<?php

namespace Botble\Inventory\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Inventory\Models\Inventory;
use Botble\Inventory\Support\InventoryContext;
use Botble\Inventory\Http\Middleware\InventoryContextMiddleware;
use Botble\Inventory\Domains\Supplier\Providers\SupplierProvider;
use Botble\Inventory\Domains\Warehouse\Providers\WarehouseProvider;
use Botble\Inventory\Domains\WarehouseProduct\Providers\WarehouseProductProvider;
use Botble\Inventory\Domains\WarehouseStaff\Providers\WarehouseStaffProvider;
use Botble\Inventory\Domains\Transactions\Providers\TransactionProvider;
use Botble\Inventory\Domains\Packing\Providers\PackingProvider;
use Botble\Inventory\Domains\Transfer\Providers\TransferProvider;
use Botble\Inventory\Domains\Report\Providers\ReportProvider;
use Botble\Inventory\Domains\Return\Providers\ReturnProvider;
use Botble\Inventory\Domains\Stock\Providers\StockProvider;

use Illuminate\Routing\Router;

class InventoryServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        require_once __DIR__ . '/../Helpers/common.php';
        $this->app->singleton(InventoryContext::class, function () {
            return new InventoryContext();
        });        

    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/inventory')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadMigrations();

        if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Inventory::class, ['name']);
        }

        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory',
                'priority' => 5,
                'parent_id' => null,
                'name' => 'plugins/inventory::inventory.name',
                'icon' => 'ti ti-building-warehouse',
                'url' => route('inventory.index'),
                'permissions' => ['inventory'],
            ]);
            // DashboardMenu::registerItem([
            //     'id' => 'cms-plugins-inventory-suppliers',
            //     'priority' => 6,
            //     'parent_id' => 'cms-plugins-inventory',
            //     'name' => 'plugins/inventory::inventory.supplier.name',
            //     'icon' => 'ti ti-truck-delivery',
            //     'url' => route('inventory.suppliers.index'),
            //     'permissions' => ['inventory'],
            // ]);
            // DashboardMenu::registerItem([
            //     'id' => 'cms-plugins-inventory-warehouse-staff',
            //     'priority' => 7,
            //     'parent_id' => 'cms-plugins-inventory',
            //     'name' => 'plugins/inventory::inventory.warehouse-staff.name',
            //     'icon' => 'ti ti-user-cog',
            //     'url' => route('inventory.warehouse-staff.index'),
            //     'permissions' => ['inventory.warehouse-staff.index'],
            // ]);
            // DashboardMenu::registerItem([
            //     'id' => 'cms-plugins-inventory-warehouse_positions',
            //     'priority' => 8,
            //     'parent_id' => 'cms-plugins-inventory',
            //     'name' => 'plugins/inventory::inventory.warehouse_positions.name',
            //     'icon' => 'ti ti-briefcase',
            //     'url' => route('inventory.warehouse-positions.index'),
            //     'permissions' => ['inventory.warehouse_positions.index'],
            // ]);
        });

        $this->app->register(SupplierProvider::class);
        $this->app->register(WarehouseStaffProvider::class);
        $this->app->register(WarehouseProvider::class);
        $this->app->register(WarehouseProductProvider::class);
        $this->app->register(TransactionProvider::class);
        $this->app->register(PackingProvider::class);
        $this->app->register(TransferProvider::class);
        $this->app->register(ReportProvider::class);
        $this->app->register(ReturnProvider::class);
        $this->app->register(StockProvider::class);

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('inventory.context', InventoryContextMiddleware::class);
    }
}
