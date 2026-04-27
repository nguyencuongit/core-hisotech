<?php

namespace Botble\Inventory\Domains\Report\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;

// interface
//repository
//model

class ReportProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-report',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.report.name',
                    'icon' => 'ti ti-report-analytics',
                    'url' => route('inventory.report.index'),
                    'permissions' => ['report.index'],
                ]);
        });


        //  app('router')->aliasMiddleware(
        //     'warehouse.permission',
        //     \Botble\Inventory\Domains\WarehouseStaff\Http\Middleware\CheckWarehousePositionPermission::class
        // );
    }
}
