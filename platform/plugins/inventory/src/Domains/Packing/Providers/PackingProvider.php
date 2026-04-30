<?php

namespace Botble\Inventory\Domains\Packing\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Domains\Packing\Repositories\Eloquent\PackingRepository;
use Botble\Inventory\Domains\Packing\Repositories\Interfaces\PackingInterface;

class PackingProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(PackingInterface::class, PackingRepository::class);
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
    }
}
