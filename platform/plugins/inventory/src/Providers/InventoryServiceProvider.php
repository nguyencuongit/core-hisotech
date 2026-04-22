<?php

namespace Botble\Inventory\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Inventory\Models\Inventory;
use Botble\Inventory\Models\Supplier;
use Botble\Inventory\Models\SupplierAddress;
use Botble\Inventory\Models\SupplierBank;
use Botble\Inventory\Models\SupplierContact;
use Botble\Inventory\Models\SupplierProduct;
use Botble\Inventory\Repositories\Eloquent\SupplierAddressRepository;
use Botble\Inventory\Repositories\Eloquent\SupplierBankRepository;
use Botble\Inventory\Repositories\Eloquent\SupplierContactRepository;
use Botble\Inventory\Repositories\Eloquent\SupplierProductRepository;
use Botble\Inventory\Repositories\Eloquent\SupplierRepository;
use Botble\Inventory\Repositories\Interfaces\SupplierAddressInterface;
use Botble\Inventory\Repositories\Interfaces\SupplierBankInterface;
use Botble\Inventory\Repositories\Interfaces\SupplierContactInterface;
use Botble\Inventory\Repositories\Interfaces\SupplierInterface;
use Botble\Inventory\Repositories\Interfaces\SupplierProductInterface;
use Botble\Inventory\Domains\WarehouseStaff\Providers\WarehouseStaffProvider;
use Botble\Inventory\Domains\Warehouse\Providers\WarehouseProvider;

class InventoryServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(SupplierInterface::class, fn () => new SupplierRepository(new Supplier()));
        $this->app->bind(SupplierContactInterface::class, fn () => new SupplierContactRepository(new SupplierContact()));
        $this->app->bind(SupplierAddressInterface::class, fn () => new SupplierAddressRepository(new SupplierAddress()));
        $this->app->bind(SupplierBankInterface::class, fn () => new SupplierBankRepository(new SupplierBank()));
        $this->app->bind(SupplierProductInterface::class, fn () => new SupplierProductRepository(new SupplierProduct()));
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
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-suppliers',
                'priority' => 6,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.supplier.name',
                'icon' => 'ti ti-truck-delivery',
                'url' => route('inventory.suppliers.index'),
                'permissions' => ['inventory'],
            ]);
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-warehouse-staff',
                'priority' => 7,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.warehouse-staff.name',
                'icon' => 'ti ti-user-cog',
                'url' => route('inventory.warehouse-staff.index'),
                'permissions' => ['inventory.warehouse-staff.index'],
            ]);
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-warehouse_positions',
                'priority' => 8,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.warehouse_positions.name',
                'icon' => 'ti ti-briefcase',
                'url' => route('inventory.warehouse-positions.index'),
                'permissions' => ['inventory.warehouse_positions.index'],
            ]);
        });

        $this->app->register(WarehouseStaffProvider::class);
        $this->app->register(WarehouseProvider::class);
    }
}
