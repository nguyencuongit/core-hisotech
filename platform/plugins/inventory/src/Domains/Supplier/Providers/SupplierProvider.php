<?php

namespace Botble\Inventory\Domains\Supplier\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;

class SupplierProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-suppliers',
                'priority' => 6,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.supplier.name',
                'icon' => 'ti ti-truck-delivery',
                'url' => route('inventory.suppliers.index'),
                'permissions' => ['inventory.suppliers.index'],
            ]);
        });
    }
}
