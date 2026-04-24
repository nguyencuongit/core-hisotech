<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Inventory\Domains\GoodsReceipt\Http\Controllers\GoodsReceiptController;
use Botble\Inventory\Domains\Supplier\Http\Controllers\SupplierController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseProductController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehousePositionController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehouseStaffController;
use Botble\Inventory\Domains\Transactions\Http\Controllers\ExportController;
use Botble\Inventory\Domains\Transactions\Http\Controllers\ImportController;
use Botble\Inventory\Domains\Transactions\Http\Controllers\TransactionAjaxController;
use Botble\Inventory\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

//
AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'inventories', 'as' => 'inventory.','middleware' => ['web', 'core', 'auth', 'inventory.context']], function () {
        Route::resource('', InventoryController::class)->parameters(['' => 'inventory']);

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

            Route::get('/{warehouse}/products/search', [
                'uses' => WarehouseProductController::class . '@searchProducts',
                'as' => 'products.search',
                'permission' => 'warehouse.index',
            ]);

            Route::get('/{warehouse}/products/supplier-product', [
                'uses' => WarehouseProductController::class . '@supplierProduct',
                'as' => 'products.supplier-product',
                'permission' => 'warehouse.index',
            ]);

            Route::post('/{warehouse}/products', [
                'uses' => WarehouseProductController::class . '@store',
                'as' => 'products.store',
                'permission' => 'warehouse.products.manage',
            ]);

            Route::match(['POST', 'PUT'], '/{warehouse}/products/{warehouseProduct}', [
                'uses' => WarehouseProductController::class . '@update',
                'as' => 'products.update',
                'permission' => 'warehouse.products.manage',
            ]);

            Route::delete('/{warehouse}/products/{warehouseProduct}', [
                'uses' => WarehouseProductController::class . '@destroy',
                'as' => 'products.destroy',
                'permission' => 'warehouse.products.manage',
            ]);

            Route::get('/{warehouse}', [
                'uses' => WarehouseController::class . '@show',
                'as' => 'show',
                'permission' => 'warehouse.show',
            ]);

            Route::delete('/{warehouse}', [
                'uses' => WarehouseController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'warehouse.destroy',
            ]);
        });

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

            Route::get('/approval/{supplier}', [
                'uses' => SupplierController::class . '@approval',
                'as' => 'approval',
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

            Route::get('/{supplier}', [
                'uses' => SupplierController::class . '@show',
                'as' => 'show',
                'permission' => 'inventory.suppliers.show',
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
                'permission' => 'inventory.suppliers.edit',
            ]);

            Route::post('/{supplier}/reject', [
                'uses' => SupplierController::class . '@reject',
                'as' => 'reject',
                'permission' => 'inventory.suppliers.edit',
            ]);

            Route::get('/products/search', [
                'uses' => SupplierController::class . '@searchProducts',
                'as' => 'products.search',
                'permission' => 'inventory.suppliers.index',
            ]);
        });

        Route::group([
            'prefix' => 'goods-receipts',
            'as' => 'goods-receipts.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => GoodsReceiptController::class . '@index',
                'as' => 'index',
                'permission' => 'inventory.goods-receipts.index',
            ]);

            Route::get('/products/search', [
                'uses' => GoodsReceiptController::class . '@searchProducts',
                'as' => 'products.search',
                'permission' => 'inventory.goods-receipts.index',
            ]);

            Route::get('/supplier-products', [
                'uses' => GoodsReceiptController::class . '@supplierProducts',
                'as' => 'supplier-products',
                'permission' => 'inventory.goods-receipts.index',
            ]);

            Route::get('/create', [
                'uses' => GoodsReceiptController::class . '@create',
                'as' => 'create',
                'permission' => 'inventory.goods-receipts.create',
            ]);

            Route::post('/create', [
                'uses' => GoodsReceiptController::class . '@store',
                'as' => 'store',
                'permission' => 'inventory.goods-receipts.create',
            ]);

            Route::get('/edit/{goodsReceipt}', [
                'uses' => GoodsReceiptController::class . '@edit',
                'as' => 'edit',
                'permission' => 'inventory.goods-receipts.edit',
            ]);

            Route::match(['POST', 'PUT'], '/edit/{goodsReceipt}', [
                'uses' => GoodsReceiptController::class . '@update',
                'as' => 'update',
                'permission' => 'inventory.goods-receipts.edit',
            ]);

            Route::get('/{goodsReceipt}', [
                'uses' => GoodsReceiptController::class . '@show',
                'as' => 'show',
                'permission' => 'inventory.goods-receipts.show',
            ]);

            Route::delete('/{goodsReceipt}', [
                'uses' => GoodsReceiptController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'inventory.goods-receipts.delete',
            ]);
        });

        // transaction import
        Route::group([
            'prefix' => 'transactions-import',
            'as' => 'transactions-import.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => ImportController::class . '@index',
                'as' => 'index',
                'permission' => 'transactions-import.index',
            ]);

            Route::get('/create', [
                'uses' => ImportController::class . '@create',
                'as' => 'create',
                'permission' => 'transactions-import.create',
            ]);

            Route::post('/create', [
                'uses' => ImportController::class . '@store',
                'as' => 'store',
                'permission' => 'transactions-import.create',
            ]);

            Route::get('/edit/{import}', [
                'uses' => ImportController::class . '@edit',
                'as' => 'edit',
                'permission' => 'transactions-import.edit',
            ]);

            Route::post('/edit/{import}', [
                'uses' => ImportController::class . '@update',
                'as' => 'update',
                'permission' => 'transactions-import.edit',
            ]);

            Route::delete('/{import}', [
                'uses' => ImportController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'transactions-import.destroy',
            ]);
        });
        // transaction export
        Route::group([
            'prefix' => 'transactions-export',
            'as' => 'transactions-export.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => ExportController::class . '@index',
                'as' => 'index',
                'permission' => 'transactions-export.index',
            ]);

            Route::get('/create', [
                'uses' => ExportController::class . '@create',
                'as' => 'create',
                'permission' => 'transactions-export.create',
            ]);

            Route::post('/create', [
                'uses' => ExportController::class . '@store',
                'as' => 'store',
                'permission' => 'transactions-export.create',
            ]);

            Route::get('/edit/{export}', [
                'uses' => ExportController::class . '@edit',
                'as' => 'edit',
                'permission' => 'transactions-export.edit',
            ]);

            Route::post('/edit/{export}', [
                'uses' => ExportController::class . '@update',
                'as' => 'update',
                'permission' => 'transactions-export.edit',
            ]);

            Route::delete('/{export}', [
                'uses' => ExportController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'transactions-export.destroy',
            ]);
        });
    });
});

Route::get('ajax/warehouses/{warehouse}/staff', [
    'uses' => TransactionAjaxController::class . '@getStaffByWarehouse',
    'as' => 'ajax.warehouses.staff',
]);

Route::get('ajax/partner-type/{type}', [
    'uses' => TransactionAjaxController::class . '@getMenber',
    'as' => 'ajax.partner-type',
]);
