<?php

use Botble\Base\Facades\AdminHelper;
use Botble\ViettelPost\Http\Controllers\AddressController;
use Botble\ViettelPost\Http\Controllers\ViettelPostController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'viettel-post', 'as' => 'viettel-post.'], function () {
        Route::get('settings', [ViettelPostController::class, 'settings'])
            ->name('settings')
            ->permission('viettel-post.settings');

        Route::post('settings', [ViettelPostController::class, 'saveSettings'])
            ->name('settings.save')
            ->permission('shipping_methods.index');

        Route::get('get-districts', [ViettelPostController::class, 'getDistrictsByProvince'])
            ->name('get-districts');

        Route::get('get-provinces', [ViettelPostController::class, 'getProvincesFromApi'])
            ->name('get-provinces');
    });

    Route::group(['prefix' => 'viettelpost/inventory', 'as' => 'viettelpost.inventory.'], function () {
        Route::get('list', [ViettelPostController::class, 'listInventories'])->name('list');
        Route::post('register/{store_id}', [ViettelPostController::class, 'registerInventory'])->name('register');
        Route::post('link/{store_id}', [ViettelPostController::class, 'linkInventory'])->name('link');
    });
});

Route::group(['prefix' => 'api/viettel-post/address', 'as' => 'viettel-post.address.'], function () {
    Route::get('provinces', [AddressController::class, 'getProvinces'])->name('provinces');
    Route::get('districts/{province_id}', [AddressController::class, 'getDistricts'])->name('districts');
    Route::get('wards/{district_id}', [AddressController::class, 'getWards'])->name('wards');
    Route::get('search', [AddressController::class, 'search'])->name('search');
});