<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Inventory\Http\Controllers\InventoryController;
use Botble\Inventory\Http\Controllers\SupplierController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehouseStaffController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehousePositionController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'inventories', 'as' => 'inventory.'], function () {
        Route::resource('', InventoryController::class)->parameters(['' => 'inventory']);

        Route::group([
            'prefix' => 'suppliers',
            'as' => 'suppliers.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => SupplierController::class . '@index',
                'as' => 'index',
                'permission' => 'inventory.suppliers.index',
            ]);

            Route::get('/create', [
                'uses' => SupplierController::class . '@create',
                'as' => 'create',
                'permission' => 'inventory.suppliers.create',
            ]);

            Route::post('/create', [
                'uses' => SupplierController::class . '@store',
                'as' => 'store',
                'permission' => 'inventory.suppliers.create',
            ]);

            Route::get('/products/search', [
                'uses' => SupplierController::class . '@searchProducts',
                'as' => 'products.search',
                'permission' => ['inventory.suppliers.index', 'inventory.suppliers.create', 'inventory.suppliers.edit'],
            ]);

            Route::get('/approval/{supplier}', [
                'uses' => SupplierController::class . '@approval',
                'as' => 'approval',
                'permission' => 'superuser',
            ]);

            Route::get('/{supplier}', [
                'uses' => SupplierController::class . '@show',
                'as' => 'show',
                'permission' => 'inventory.suppliers.show',
            ]);

            Route::get('/edit/{supplier}', [
                'uses' => SupplierController::class . '@edit',
                'as' => 'edit',
                'permission' => 'inventory.suppliers.edit',
            ]);

            Route::match(['POST', 'PUT'], '/edit/{supplier}', [
                'uses' => SupplierController::class . '@update',
                'as' => 'update',
                'permission' => 'inventory.suppliers.edit',
            ]);

            Route::delete('/{supplier}', [
                'uses' => SupplierController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'inventory.suppliers.delete',
            ]);

            Route::post('/{supplier}/submit', [
                'uses' => SupplierController::class . '@submit',
                'as' => 'submit',
                'permission' => 'inventory.suppliers.edit',
            ]);

            Route::post('/{supplier}/approve', [
                'uses' => SupplierController::class . '@approve',
                'as' => 'approve',
                'permission' => 'superuser',
            ]);

            Route::post('/{supplier}/reject', [
                'uses' => SupplierController::class . '@reject',
                'as' => 'reject',
                'permission' => 'superuser',
            ]);

        });

        Route::resource('warehouse-staff', WarehouseStaffController::class)->parameters(['warehouse-staff' => 'warehouseStaff'])->names('warehouse-staff');

        Route::group(['prefix' => 'warehouse_positions', 'as' => 'warehouse-positions.', 'namespace' => 'Botble\\Inventory\\Domains\\WarehouseStaff\\Http\\Controllers'], function () {
            Route::get('/', ['uses' => 'WarehousePositionController@index', 'as' => 'index', 'permission' => 'warehouse-positions.index']);
            Route::get('/create', ['uses' => 'WarehousePositionController@create', 'as' => 'create', 'permission' => 'warehouse-positions.create']);
            Route::post('/create', ['uses' => 'WarehousePositionController@store', 'as' => 'store', 'permission' => 'warehouse-positions.create']);
            Route::get('/edit/{warehousePositions}', ['uses' => 'WarehousePositionController@edit', 'as' => 'edit', 'permission' => 'warehouse-positions.edit']);
            Route::put('/edit/{warehousePositions}', ['uses' => 'WarehousePositionController@update', 'as' => 'update', 'permission' => 'warehouse-positions.edit']);
            Route::delete('/items/{warehousePositions}', ['uses' => 'WarehousePositionController@destroy', 'as' => 'destroy', 'permission' => 'warehouse-positions.destroy']);
        });
    });
});
