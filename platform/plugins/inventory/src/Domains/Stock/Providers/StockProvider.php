<?php

namespace Botble\Inventory\Domains\Stock\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;

// interface
//repository
//model

class StockProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-stock',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.stock.name',
                    'icon' => 'ti ti-stack',
                    'url' => route('inventory.stock.index'),
                    'permissions' => ['stock.index'],
                ]);
        });
    }
}
