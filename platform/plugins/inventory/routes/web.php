<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Inventory\Http\Controllers\InventoryController;
// warehouse staff
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehouseStaffController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehousePositionController;


use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'inventories', 'as' => 'inventory.'], function () {
        Route::resource('', InventoryController::class)->parameters(['' => 'inventory']);
        Route::resource('warehouse-staff', WarehouseStaffController::class)->parameters(['' => 'warehouseStaff'])->names('warehouse-staff');
        Route::resource('warehouse_positions', WarehousePositionController::class)->parameters(['' => 'warehousePositions'])->names('warehouse-positions');

    });
});
