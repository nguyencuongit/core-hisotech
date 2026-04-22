<?php

namespace Botble\Inventory\Domains\Warehouse\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;

class WarehouseProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

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
            // DashboardMenu::registerItem([
            //         'id' => 'cms-plugins-inventory-warehouse_positions',
            //         'priority' => 6,
            //         'parent_id' => 'cms-plugins-inventory',
            //         'name' => 'plugins/inventory::inventory.warehouse_positions.name',
            //         'icon' => 'ti ti-briefcase',
            //         'url' => route('inventory.warehouse-positions.index'),
            //         'permissions' => ['warehouse-positions.index'],
            //     ]);
        });


        
    }
}
