<?php

namespace Botble\Inventory\Domains\Transactions\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\Filter;
use Botble\Inventory\Domains\Transactions\Repositories\Eloquent\ExportShipmentRepository;
use Botble\Inventory\Domains\Transactions\Repositories\Interfaces\ExportShipmentInterface;

class TransactionProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(ExportShipmentInterface::class, ExportShipmentRepository::class);
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-transactions-import',
                    'priority' => 7,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.transactions.import.name',
                    'icon' => 'ti ti-download',
                    'url' => route('inventory.transactions-import.index'),
                    'permissions' => ['transactions-import.index'],
                ]);
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-transactions-export',
                    'priority' => 8,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.transactions.export.name',
                    'icon' => 'ti ti-upload',
                    'url' => route('inventory.transactions-export.index'),
                    'permissions' => ['transactions-export.index'],
                ]);
        });
        
    }
}
