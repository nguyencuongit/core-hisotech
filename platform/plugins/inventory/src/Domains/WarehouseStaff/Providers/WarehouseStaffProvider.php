<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Inventory\Models\Inventory;

// interface
use Botble\Inventory\Domains\WarehouseStaff\Repositories\Interfaces\WarehouseStaffAssignmentInterface;
//repository
use Botble\Inventory\Domains\WarehouseStaff\Repositories\Eloquent\WarehouseStaffAssignmentRepository;
//model
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaffAssignments;

class WarehouseStaffProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(WarehouseStaffAssignmentInterface::class, function () {
            return new WarehouseStaffAssignmentRepository(new WarehouseStaffAssignments());
        });
        
        
    }

    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-warehouse-staff',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.warehouse-staff.name',
                    'icon' => 'ti ti-user-cog',
                    'url' => route('inventory.warehouse-staff.index'),
                    'permissions' => ['warehouse-staff.index'],
                ]);
            DashboardMenu::registerItem([
                    'id' => 'cms-plugins-inventory-warehouse_positions',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-inventory',
                    'name' => 'plugins/inventory::inventory.warehouse_positions.name',
                    'icon' => 'ti ti-briefcase',
                    'url' => route('inventory.warehouse-positions.index'),
                    'permissions' => ['warehouse-positions.index'],
                ]);
        });


        //  app('router')->aliasMiddleware(
        //     'warehouse.permission',
        //     \Botble\Inventory\Domains\WarehouseStaff\Http\Middleware\CheckWarehousePositionPermission::class
        // );
    }
}
