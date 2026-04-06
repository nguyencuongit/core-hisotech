<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Logistics\Http\Controllers\LogisticsController;
use Botble\Logistics\Http\Controllers\ShippingProviderController;
use Botble\Logistics\Http\Controllers\AddressShippingController;
use Botble\Logistics\Http\Controllers\CreateOrderShippingController;
use Botble\Logistics\Http\Controllers\GetAddressController;
use Botble\Logistics\Http\Controllers\ShippingFeeController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'logistics', 'as' => 'logistics.'], function () {
        Route::resource('', LogisticsController::class)->parameters(['' => 'logistics']);
        Route::resource('/shipping-providers', ShippingProviderController::class)->names('providers')->parameters(['shipping-providers' => 'provider']);
        Route::get('/shipping-address/provicen/{code}', [AddressShippingController::class, 'provincenID'])->name('provicen');
        Route::get('/shipping-address/district/{code}', [AddressShippingController::class, 'DistrictID'])->name('district');
        Route::post('/shipping-address/address-admin', [AddressShippingController::class, 'addressAdmin'])->name('address.admin');

        Route::resource('/shipping-create', CreateOrderShippingController::class)->names('shipping.order')->parameters(['shipping-order' => 'orderShipping']);
        Route::get('/shipping-create/factories/{id}', [CreateOrderShippingController::class, 'factories'])->name('shipping.order.factories');

        
    });
});


Route::get('/ajax/districts', [GetAddressController::class, 'getDistricts']);
Route::get('/ajax/ward', [GetAddressController::class, 'getWard']);
Route::get('/ajax/shipping-fee', [ShippingFeeController::class, 'shippingFee']);

