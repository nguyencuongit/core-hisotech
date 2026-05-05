<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Inventory\Domains\GoodsReceipt\Http\Controllers\GoodsReceiptController;
use Botble\Inventory\Domains\Supplier\Http\Controllers\SupplierController;
use Botble\Inventory\Domains\Supplier\Permissions\SupplierPermissions;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\PalletController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseLocationController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseMapController;
use Botble\Inventory\Domains\WarehouseProduct\Http\Controllers\WarehouseProductCatalogController;
use Botble\Inventory\Domains\WarehouseProduct\Http\Controllers\WarehouseProductController;
use Botble\Inventory\Domains\WarehouseProduct\Http\Controllers\WarehouseProductPolicyController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseSettingController;
use Botble\Inventory\Domains\Warehouse\Http\Controllers\WarehouseTemplateController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehousePositionController;
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehouseStaffController;
use Botble\Inventory\Domains\Transactions\Http\Controllers\ExportController;
use Botble\Inventory\Domains\Transactions\Http\Controllers\ImportController;
use Botble\Inventory\Domains\Transactions\Http\Controllers\TransactionAjaxController;
use Botble\Inventory\Domains\Packing\Http\Controllers\PackingController;
use Botble\Inventory\Domains\Transfer\Http\Controllers\TransferController;
use Botble\Inventory\Domains\Return\Http\Controllers\ReturnController;
use Botble\Inventory\Domains\Stock\Http\Controllers\StockController;



use Botble\Inventory\Http\Controllers\InventoryController;
use Botble\Inventory\Http\Controllers\InventoryAjaxController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'inventories', 'as' => 'inventory.', 'middleware' => ['web', 'core', 'auth', 'inventory.context']], function () {
        Route::resource('', InventoryController::class)->parameters(['' => 'inventory']);

        Route::group(['prefix' => 'warehouse_positions', 'as' => 'warehouse-positions.'], function () {
            Route::match(['GET', 'POST'], '/', ['uses' => WarehousePositionController::class . '@index', 'as' => 'index', 'permission' => 'warehouse-positions.index']);
            Route::get('/create', ['uses' => WarehousePositionController::class . '@create', 'as' => 'create', 'permission' => 'warehouse-positions.create']);
            Route::post('/create', ['uses' => WarehousePositionController::class . '@store', 'as' => 'store', 'permission' => 'warehouse-positions.create']);
            Route::get('/edit/{warehousePosition}', ['uses' => WarehousePositionController::class . '@edit', 'as' => 'edit', 'permission' => 'warehouse-positions.edit']);
            Route::post('/edit/{warehousePosition}', ['uses' => WarehousePositionController::class . '@update', 'as' => 'update', 'permission' => 'warehouse-positions.edit']);
            Route::delete('/{warehousePosition}', ['uses' => WarehousePositionController::class . '@destroy', 'as' => 'destroy', 'permission' => 'warehouse-positions.destroy']);
        });

        Route::group(['prefix' => 'warehouse_staff', 'as' => 'warehouse-staff.'], function () {
            Route::match(['GET', 'POST'], '/', ['uses' => WarehouseStaffController::class . '@index', 'as' => 'index', 'permission' => 'warehouse-staff.index']);
            Route::get('/create', ['uses' => WarehouseStaffController::class . '@create', 'as' => 'create', 'permission' => 'warehouse-staff.create']);
            Route::post('/create', ['uses' => WarehouseStaffController::class . '@store', 'as' => 'store', 'permission' => 'warehouse-staff.create']);
            Route::get('/edit/{warehouseStaff}', ['uses' => WarehouseStaffController::class . '@edit', 'as' => 'edit', 'permission' => 'warehouse-staff.edit']);
            Route::post('/edit/{warehouseStaff}', ['uses' => WarehouseStaffController::class . '@update', 'as' => 'update', 'permission' => 'warehouse-staff.edit']);
            Route::delete('/{warehouseStaff}', ['uses' => WarehouseStaffController::class . '@destroy', 'as' => 'destroy', 'permission' => 'warehouse-staff.destroy']);
        });

        Route::group(['prefix' => 'warehouse-products', 'as' => 'warehouse-products.'], function () {
            Route::match(['GET', 'POST'], '/', ['uses' => WarehouseProductCatalogController::class . '@index', 'as' => 'index', 'permission' => 'warehouse.index']);
            Route::post('/assign', ['uses' => WarehouseProductCatalogController::class . '@assign', 'as' => 'assign', 'permission' => 'warehouse.products.manage']);
            Route::post('/toggle', ['uses' => WarehouseProductCatalogController::class . '@toggle', 'as' => 'toggle', 'permission' => 'warehouse.products.manage']);
        });

        Route::group(['prefix' => 'warehouse', 'as' => 'warehouse.'], function () {
            Route::match(['GET', 'POST'], '/', ['uses' => WarehouseController::class . '@index', 'as' => 'index', 'permission' => 'warehouse.index']);
            Route::get('/create', ['uses' => WarehouseController::class . '@create', 'as' => 'create', 'permission' => 'warehouse.create']);
            Route::get('/templates', ['uses' => WarehouseTemplateController::class . '@index', 'as' => 'templates.index', 'permission' => 'warehouse.locations.manage']);
            Route::post('/{warehouse}/templates/apply', ['uses' => WarehouseTemplateController::class . '@apply', 'as' => 'templates.apply', 'permission' => 'warehouse.locations.manage']);
            Route::post('/create', ['uses' => WarehouseController::class . '@store', 'as' => 'store', 'permission' => 'warehouse.create']);
            Route::get('/edit/{warehouse}', ['uses' => WarehouseController::class . '@edit', 'as' => 'edit', 'permission' => 'warehouse.edit']);
            Route::match(['POST', 'PUT'], '/edit/{warehouse}', ['uses' => WarehouseController::class . '@update', 'as' => 'update', 'permission' => 'warehouse.edit']);
            Route::get('/{warehouse}/products/search', ['uses' => WarehouseProductController::class . '@searchProducts', 'as' => 'products.search', 'permission' => 'warehouse.index']);
            Route::get('/{warehouse}/products/supplier-product', ['uses' => WarehouseProductController::class . '@supplierProduct', 'as' => 'products.supplier-product', 'permission' => 'warehouse.index']);
            Route::post('/{warehouse}/products', ['uses' => WarehouseProductController::class . '@store', 'as' => 'products.store', 'permission' => 'warehouse.products.manage']);
            Route::match(['POST', 'PUT'], '/{warehouse}/products/{warehouseProduct}', ['uses' => WarehouseProductController::class . '@update', 'as' => 'products.update', 'permission' => 'warehouse.products.manage']);
            Route::delete('/{warehouse}/products/{warehouseProduct}', ['uses' => WarehouseProductController::class . '@destroy', 'as' => 'products.destroy', 'permission' => 'warehouse.products.manage']);
            Route::post('/{warehouse}/products/{warehouseProduct}/policy', ['uses' => WarehouseProductPolicyController::class . '@store', 'as' => 'products.policy.store', 'permission' => 'warehouse.products.manage']);
            Route::match(['POST', 'PUT'], '/{warehouse}/products/{warehouseProduct}/policy/{warehouseProductPolicy}', ['uses' => WarehouseProductPolicyController::class . '@update', 'as' => 'products.policy.update', 'permission' => 'warehouse.products.manage']);
            Route::post('/{warehouse}/pallets', ['uses' => PalletController::class . '@store', 'as' => 'pallets.store', 'permission' => 'warehouse.products.manage']);
            Route::post('/{warehouse}/pallets/{pallet}/move', ['uses' => PalletController::class . '@move', 'as' => 'pallets.move', 'permission' => 'warehouse.products.manage']);
            Route::post('/{warehouse}/locations', ['uses' => WarehouseLocationController::class . '@store', 'as' => 'locations.store', 'permission' => 'warehouse.locations.manage']);
            Route::match(['POST', 'PUT'], '/{warehouse}/locations/{warehouseLocation}', ['uses' => WarehouseLocationController::class . '@update', 'as' => 'locations.update', 'permission' => 'warehouse.locations.manage']);
            Route::post('/{warehouse}/maps', ['uses' => WarehouseMapController::class . '@store', 'as' => 'maps.store', 'permission' => 'warehouse.maps.manage']);
            Route::post('/{warehouse}/maps/blueprint', ['uses' => WarehouseMapController::class . '@applyBlueprint', 'as' => 'maps.blueprint', 'permission' => 'warehouse.maps.manage']);
            Route::post('/{warehouse}/maps/{warehouseMap}/sync', ['uses' => WarehouseMapController::class . '@sync', 'as' => 'maps.sync', 'permission' => 'warehouse.maps.manage']);
            Route::post('/{warehouse}/settings', ['uses' => WarehouseSettingController::class . '@store', 'as' => 'settings.store', 'permission' => 'warehouse.edit']);
            Route::get('/{warehouse}', ['uses' => WarehouseController::class . '@show', 'as' => 'show', 'permission' => 'warehouse.show']);
            Route::delete('/{warehouse}', ['uses' => WarehouseController::class . '@destroy', 'as' => 'destroy', 'permission' => 'warehouse.destroy']);
        });

        Route::group(['prefix' => 'suppliers', 'as' => 'suppliers.'], function () {
            Route::match(['GET', 'POST'], '/', ['uses' => SupplierController::class . '@index', 'as' => 'index', 'permission' => SupplierPermissions::INDEX]);
            Route::get('/products/search', ['uses' => SupplierController::class . '@searchProducts', 'as' => 'products.search', 'permission' => SupplierPermissions::INDEX]);
            Route::get('/create', ['uses' => SupplierController::class . '@create', 'as' => 'create', 'permission' => SupplierPermissions::CREATE]);
            Route::post('/create', ['uses' => SupplierController::class . '@store', 'as' => 'store', 'permission' => SupplierPermissions::CREATE]);
            Route::get('/approval/{supplier}', ['uses' => SupplierController::class . '@approval', 'as' => 'approval', 'permission' => SupplierPermissions::SHOW]);
            Route::get('/edit/{supplier}', ['uses' => SupplierController::class . '@edit', 'as' => 'edit', 'permission' => SupplierPermissions::EDIT]);
            Route::match(['POST', 'PUT'], '/edit/{supplier}', ['uses' => SupplierController::class . '@update', 'as' => 'update', 'permission' => SupplierPermissions::EDIT]);
            Route::get('/{supplier}', ['uses' => SupplierController::class . '@show', 'as' => 'show', 'permission' => SupplierPermissions::SHOW]);
            Route::delete('/{supplier}', ['uses' => SupplierController::class . '@destroy', 'as' => 'destroy', 'permission' => SupplierPermissions::DESTROY]);
            Route::post('/{supplier}/submit', ['uses' => SupplierController::class . '@submit', 'as' => 'submit', 'permission' => SupplierPermissions::EDIT]);
            Route::post('/{supplier}/approve', ['uses' => SupplierController::class . '@approve', 'as' => 'approve', 'permission' => SupplierPermissions::EDIT]);
            Route::post('/{supplier}/reject', ['uses' => SupplierController::class . '@reject', 'as' => 'reject', 'permission' => SupplierPermissions::EDIT]);
        });

        Route::group(['prefix' => 'goods-receipts', 'as' => 'goods-receipts.'], function () {
            Route::match(['GET', 'POST'], '/', ['uses' => GoodsReceiptController::class . '@index', 'as' => 'index', 'permission' => 'inventory.goods-receipts.index']);
            Route::get('/products/search', ['uses' => GoodsReceiptController::class . '@searchProducts', 'as' => 'products.search', 'permission' => 'inventory.goods-receipts.index']);
            Route::get('/supplier-products', ['uses' => GoodsReceiptController::class . '@supplierProducts', 'as' => 'supplier-products', 'permission' => 'inventory.goods-receipts.index']);
            Route::get('/create', ['uses' => GoodsReceiptController::class . '@create', 'as' => 'create', 'permission' => 'inventory.goods-receipts.create']);
            Route::post('/create', ['uses' => GoodsReceiptController::class . '@store', 'as' => 'store', 'permission' => 'inventory.goods-receipts.create']);
            Route::get('/edit/{goodsReceipt}', ['uses' => GoodsReceiptController::class . '@edit', 'as' => 'edit', 'permission' => 'inventory.goods-receipts.edit']);
            Route::match(['POST', 'PUT'], '/edit/{goodsReceipt}', ['uses' => GoodsReceiptController::class . '@update', 'as' => 'update', 'permission' => 'inventory.goods-receipts.edit']);
            Route::post('/{goodsReceipt}/storage-items/generate', ['uses' => GoodsReceiptController::class . '@generateStorageItems', 'as' => 'storage-items.generate', 'permission' => 'inventory.goods-receipts.edit']);
            Route::match(['POST', 'PUT'], '/{goodsReceipt}/storage-items/{storageItem}', ['uses' => GoodsReceiptController::class . '@updateStorageItem', 'as' => 'storage-items.update', 'permission' => 'inventory.goods-receipts.edit']);
            Route::post('/{goodsReceipt}/storage-items/{storageItem}/post', ['uses' => GoodsReceiptController::class . '@postStorageItem', 'as' => 'storage-items.post', 'permission' => 'inventory.goods-receipts.edit']);
            Route::post('/{goodsReceipt}/storage-items/post-all', ['uses' => GoodsReceiptController::class . '@postAllStorageItems', 'as' => 'storage-items.post-all', 'permission' => 'inventory.goods-receipts.edit']);
            Route::get('/{goodsReceipt}', ['uses' => GoodsReceiptController::class . '@show', 'as' => 'show', 'permission' => 'inventory.goods-receipts.show']);
            Route::delete('/{goodsReceipt}', ['uses' => GoodsReceiptController::class . '@destroy', 'as' => 'destroy', 'permission' => 'inventory.goods-receipts.delete']);
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

        // packing
        Route::group([
            'prefix' => 'packing',
            'as' => 'packing.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => PackingController::class . '@index',
                'as' => 'index',
                'permission' => 'packing.index',
            ]);

            Route::get('/create', [
                'uses' => PackingController::class . '@create',
                'as' => 'create',
                'permission' => 'packing.create',
            ]);

            Route::post('/create', [
                'uses' => PackingController::class . '@store',
                'as' => 'store',
                'permission' => 'packing.create',
            ]);

            Route::get('/exports/{export}/preview', [
                'uses' => PackingController::class . '@exportPreview',
                'as' => 'export-preview',
                'permission' => 'packing.create',
            ]);

            Route::get('/edit/{packing}', [
                'uses' => PackingController::class . '@edit',
                'as' => 'edit',
                'permission' => 'packing.edit',
            ]);

            Route::post('/edit/{packing}', [
                'uses' => PackingController::class . '@update',
                'as' => 'update',
                'permission' => 'packing.edit',
            ]);

            Route::delete('/{packing}', [
                'uses' => PackingController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'packing.destroy',
            ]);
        });

        // transfer
        Route::group([
            'prefix' => 'transfer',
            'as' => 'transfer.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => TransferController::class . '@index',
                'as' => 'index',
                'permission' => 'transfer.index',
            ]);

            Route::get('/create', [
                'uses' => TransferController::class . '@create',
                'as' => 'create',
                'permission' => 'transfer.create',
            ]);

            Route::post('/create', [
                'uses' => TransferController::class . '@store',
                'as' => 'store',
                'permission' => 'transfer.create',
            ]);

            Route::get('/edit/{transfer}', [
                'uses' => TransferController::class . '@edit',
                'as' => 'edit',
                'permission' => 'transfer.edit',
            ]);

            Route::post('/edit/{transfer}', [
                'uses' => TransferController::class . '@update',
                'as' => 'update',
                'permission' => 'transfer.edit',
            ]);

            Route::delete('/{transfer}', [
                'uses' => TransferController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'transfer.destroy',
            ]);
        });

        // report
        Route::group([
            'prefix' => 'report',
            'as' => 'report.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => PackingController::class . '@index',
                'as' => 'index',
                'permission' => 'report.index',
            ]);

            Route::get('/create', [
                'uses' => PackingController::class . '@create',
                'as' => 'create',
                'permission' => 'report.create',
            ]);

            Route::post('/create', [
                'uses' => PackingController::class . '@store',
                'as' => 'store',
                'permission' => 'report.create',
            ]);

            Route::get('/edit/{report}', [
                'uses' => PackingController::class . '@edit',
                'as' => 'edit',
                'permission' => 'report.edit',
            ]);

            Route::post('/edit/{report}', [
                'uses' => PackingController::class . '@update',
                'as' => 'update',
                'permission' => 'report.edit',
            ]);

            Route::delete('/{report}', [
                'uses' => PackingController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'report.destroy',
            ]);
        });

        // return
        Route::group([
            'prefix' => 'return',
            'as' => 'return.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => ReturnController::class . '@index',
                'as' => 'index',
                'permission' => 'return.index',
            ]);

            Route::get('/create', [
                'uses' => ReturnController::class . '@create',
                'as' => 'create',
                'permission' => 'return.create',
            ]);

            Route::post('/create', [
                'uses' => ReturnController::class . '@store',
                'as' => 'store',
                'permission' => 'return.create',
            ]);

            Route::get('/edit/{return}', [
                'uses' => ReturnController::class . '@edit',
                'as' => 'edit',
                'permission' => 'return.edit',
            ]);

            Route::post('/edit/{return}', [
                'uses' => ReturnController::class . '@update',
                'as' => 'update',
                'permission' => 'return.edit',
            ]);

            Route::delete('/{return}', [
                'uses' => ReturnController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'return.destroy',
            ]);
        });


        // stock
        Route::group([
            'prefix' => 'stock',
            'as' => 'stock.',
        ], function () {
            Route::match(['GET', 'POST'], '/', [
                'uses' => StockController::class . '@index',
                'as' => 'index',
                'permission' => 'stock.index',
            ]);

            Route::get('/create', [
                'uses' => StockController::class . '@create',
                'as' => 'create',
                'permission' => 'stock.create',
            ]);

            Route::post('/create', [
                'uses' => StockController::class . '@store',
                'as' => 'store',
                'permission' => 'stock.create',
            ]);

            Route::get('/edit/{stock}', [
                'uses' => StockController::class . '@edit',
                'as' => 'edit',
                'permission' => 'stock.edit',
            ]);

            Route::post('/edit/{stock}', [
                'uses' => StockController::class . '@update',
                'as' => 'update',
                'permission' => 'stock.edit',
            ]);

            Route::delete('/{stock}', [
                'uses' => StockController::class . '@destroy',
                'as' => 'destroy',
                'permission' => 'stock.destroy',
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

Route::get('ajax/states/{state}/cities', [
    'uses' => InventoryAjaxController::class . '@getCitiesByState',
    'as' => 'ajax.states.cities',
]);
