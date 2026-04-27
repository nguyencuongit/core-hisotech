<?php

namespace Botble\Inventory\Domains\Packing\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;

// interface
//repository
//model

class PackingProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-packing',
                    'priority' => 9,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.packing.name',
                    'icon' => 'ti ti-package',
                    'url' => route('inventory.packing.index'),
                    'permissions' => ['packing.index'],
                ]);
        });


        //  app('router')->aliasMiddleware(
        //     'warehouse.permission',
        //     \Botble\Inventory\Domains\WarehouseStaff\Http\Middleware\CheckWarehousePositionPermission::class
        // );
    }
}
