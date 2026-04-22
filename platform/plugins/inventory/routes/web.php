<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Inventory\Http\Controllers\InventoryController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehouseStaffController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehousePositionController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'inventories', 'as' => 'inventory.'], function () {
        Route::resource('', InventoryController::class)->parameters(['' => 'inventory']);

        // positions
        Route::group([
            'prefix' => 'warehouse_positions',
            'as' => 'warehouse-positions.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => WarehousePositionController::class . '@index',
                'as' => 'index',
                'permission' => 'warehouse-positions.index',
            ]);

            Route::get('/create', [
                'uses' => WarehousePositionController::class . '@create',
                'as' => 'create',
                'permission' => 'warehouse-positions.create',
            ]);

            Route::post('/create', [
                'uses' => WarehousePositionController::class . '@store',
                'as' => 'store',
                'permission' => 'warehouse-positions.create',
            ]);

            Route::get('/edit/{warehousePosition}', [
                'uses' => WarehousePositionController::class . '@edit',
                'as' => 'edit',
                'permission' => 'warehouse-positions.edit',
            ]);

            Route::post('/edit/{warehousePosition}', [
                'uses' => WarehousePositionController::class . '@update',
                'as' => 'update',
                'permission' => 'warehouse-positions.edit',
            ]);

            Route::delete('/{warehousePosition}', [
                'uses' => WarehousePositionController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'warehouse-positions.delete',
            ]);
        });

        // staff
        Route::group([
            'prefix' => 'warehouse_staff',
            'as' => 'warehouse-staff.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => WarehouseStaffController::class . '@index',
                'as' => 'index',
                'permission' => 'warehouse-staff.index',
            ]);

            Route::get('/create', [
                'uses' => WarehouseStaffController::class . '@create',
                'as' => 'create',
                'permission' => 'warehouse-staff.create',
            ]);

            Route::post('/create', [
                'uses' => WarehouseStaffController::class . '@store',
                'as' => 'store',
                'permission' => 'warehouse-staff.create',
            ]);

            Route::get('/edit/{warehouseStaff}', [
                'uses' => WarehouseStaffController::class . '@edit',
                'as' => 'edit',
                'permission' => 'warehouse-staff.edit',
            ]);

            Route::post('/edit/{warehouseStaff}', [
                'uses' => WarehouseStaffController::class . '@update',
                'as' => 'update',
                'permission' => 'warehouse-staff.edit',
            ]);

            Route::delete('/{warehouseStaff}', [
                'uses' => WarehouseStaffController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'warehouse-staff.destroy',
            ]);
        });

        // warehouse
        Route::group([
            'prefix' => 'warehouse',
            'as' => 'warehouse.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => WarehouseController::class . '@index',
                'as' => 'index',
                'permission' => 'warehouse.index',
            ]);

            Route::get('/create', [
                'uses' => WarehouseController::class . '@create',
                'as' => 'create',
                'permission' => 'warehouse.create',
            ]);

            Route::post('/create', [
                'uses' => WarehouseController::class . '@store',
                'as' => 'store',
                'permission' => 'warehouse.create',
            ]);

            Route::get('/edit/{warehouse}', [
                'uses' => WarehouseController::class . '@edit',
                'as' => 'edit',
                'permission' => 'warehouse.edit',
            ]);

            Route::post('/edit/{warehouse}', [
                'uses' => WarehouseController::class . '@update',
                'as' => 'update',
                'permission' => 'warehouse.edit',
            ]);

            Route::delete('/{warehouse}', [
                'uses' => WarehouseController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'warehouse.destroy',
            ]);

            Route::get('/show', [
                'uses' => WarehouseController::class . '@show',
                'as' => 'show',
                'permission' => 'warehouse.show',
            ]);
        });
    });
});