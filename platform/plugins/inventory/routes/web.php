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
        Route::group(['prefix' => 'warehouse_positions', 'as' => 'warehouse-positions.',  'namespace' => 'Botble\Inventory\Domains\WarehouseStaff\Http\Controllers',], function () {
            Route::get('/', [
                'uses' => 'WarehousePositionController@index',
                'as' => 'index',
                'permission' => 'warehouse-positions.index',
            ]);

            Route::get('/create', [
                'uses' => 'WarehousePositionController@create',
                'as' => 'create',
                'permission' => 'warehouse-positions.create',
            ]);

            Route::post('/create', [
                'uses' => 'WarehousePositionController@store',
                'as' => 'store',
                'permission' => 'warehouse-positions.create',
            ]);

            Route::get('/edit/{warehousePositions}', [
                'uses' => 'WarehousePositionController@edit',
                'as' => 'edit',
                'permission' => 'warehouse-positions.edit',
            ]);

            Route::put('/edit/{warehousePositions}', [
                'uses' => 'WarehousePositionController@update',
                'as' => 'update',
                'permission' => 'warehouse-positions.edit',
            ]);

            Route::delete('/items/{warehousePositions}', [
                'uses' => 'WarehousePositionController@destroy',
                'as' => 'destroy',
                'permission' => 'warehouse-positions.destroy',
            ]);
        });
    });
});

