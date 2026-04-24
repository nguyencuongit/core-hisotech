<?php

namespace Botble\Inventory\Domains\Transactions\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\Filter;
// interface
//repository
//model

class TransactionProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        
        
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-transactions-import',
                    'priority' => 7,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.transactions.import.name',
                    'icon' => 'ti ti-user-cog',
                    'url' => route('inventory.transactions-import.index'),
                    'permissions' => ['transactions-import.index'],
                ]);
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-transactions-export',
                    'priority' => 8,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.transactions.export.name',
                    'icon' => 'ti ti-briefcase',
                    'url' => route('inventory.transactions-export.index'),
                    'permissions' => ['transactions-export.index'],
                ]);
        });
        

        //  app('router')->aliasMiddleware(
        //     'warehouse.permission',
        //     \Botble\Inventory\Domains\WarehouseStaff\Http\Middleware\CheckWarehousePositionPermission::class
        // );
    }
}
