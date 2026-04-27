<?php

namespace Botble\Inventory\Domains\Transfer\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;

// interface
//repository
//model

class TransferProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-transfer',
                    'priority' => 10,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.transfer.name',
                    'icon' => 'ti ti-arrows-left-right',
                    'url' => route('inventory.transfer.index'),
                    'permissions' => ['Transfer.index'],
                ]);
        });


        //  app('router')->aliasMiddleware(
        //     'warehouse.permission',
        //     \Botble\Inventory\Domains\WarehouseStaff\Http\Middleware\CheckWarehousePositionPermission::class
        // );
    }
}
