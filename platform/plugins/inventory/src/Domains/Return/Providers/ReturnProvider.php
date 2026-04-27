<?php

namespace Botble\Inventory\Domains\Return\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;

// interface
//repository
//model

class ReturnProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-return',
                    'priority' => 10,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.return.name',
                    'icon' => 'ti ti-arrow-back-up',
                    'url' => route('inventory.return.index'),
                    'permissions' => ['return.index'],
                ]);
        });


        //  app('router')->aliasMiddleware(
        //     'warehouse.permission',
        //     \Botble\Inventory\Domains\WarehouseStaff\Http\Middleware\CheckWarehousePositionPermission::class
        // );
    }
}
