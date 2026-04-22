<?php

namespace Botble\Inventory\Domains\Warehouse\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Repositories\Eloquent\WarehouseRepository;
use Botble\Inventory\Domains\Warehouse\Repositories\Interfaces\WarehouseInterface;

class WarehouseProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(WarehouseInterface::class, function () {
            return new WarehouseRepository(new Warehouse());
        });
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-warehouse',
                'priority' => 6,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.warehouse.name',
                'icon' => 'ti ti-building-warehouse',
                'url' => route('inventory.warehouse.index'),
                'permissions' => ['warehouse.index'],
            ]);

            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-warehouse-products',
                'priority' => 7,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.warehouse_product.name',
                'icon' => 'ti ti-packages',
                'url' => route('inventory.warehouse-products.index'),
                'permissions' => ['warehouse.index'],
            ]);
        });
    }
}
