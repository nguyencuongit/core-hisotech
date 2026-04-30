<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent\ProductReadRepository;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent\ProductVariationReadRepository;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent\SupplierProductReadRepository;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent\WarehouseProductPolicyRepository;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent\WarehouseProductRepository;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent\WarehouseProductUsageReadRepository;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Eloquent\WarehouseReadRepository;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\ProductReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\ProductVariationReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\SupplierProductReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductPolicyInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductUsageReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;

class WarehouseProductProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(WarehouseProductInterface::class, WarehouseProductRepository::class);
        $this->app->bind(WarehouseProductPolicyInterface::class, WarehouseProductPolicyRepository::class);
        $this->app->bind(ProductReadInterface::class, ProductReadRepository::class);
        $this->app->bind(ProductVariationReadInterface::class, ProductVariationReadRepository::class);
        $this->app->bind(SupplierProductReadInterface::class, SupplierProductReadRepository::class);
        $this->app->bind(WarehouseReadInterface::class, WarehouseReadRepository::class);
        $this->app->bind(WarehouseProductUsageReadInterface::class, WarehouseProductUsageReadRepository::class);
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-warehouse-products',
                'priority' => 7,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.warehouse_product.name',
                'icon' => 'ti ti-packages',
                'url' => route('inventory.warehouse-products.index'),
                'permissions' => ['warehouse.index'],
            ]);
        });
    }
}
