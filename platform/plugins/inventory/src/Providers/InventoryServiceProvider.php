<?php

namespace Botble\Inventory\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;
use Botble\Inventory\Domains\WarehouseStaff\Providers\WarehouseStaffProvider;

class InventoryServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/inventory')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadMigrations();
            
            if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Inventory::class, [
                    'name',
                ]);
            }
            
            DashboardMenu::default()->beforeRetrieving(function () {
                DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory',
                    'priority' => 5,
                    'parent_id' => null,
                    'name' => 'plugins/inventory::inventory.name',
                    'icon' => 'ti ti-building-warehouse',
                    // 'url' => route('inventory.index'),
                    'permissions' => ['inventory.index'],
                ]);
            });

        $this->app->register(WarehouseStaffProvider::class);
    }
}
