<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Logistics\Http\Controllers\LogisticsController;
use Botble\Logistics\Http\Controllers\ShippingProviderController;
use Botble\Logistics\Http\Controllers\AddressShippingController;
use Botble\Logistics\Http\Controllers\CreateOrderShippingController;
use Botble\Logistics\Http\Controllers\GetAddressController;
use Botble\Logistics\Http\Controllers\ShippingFeeController;
use Botble\Logistics\Http\Controllers\WebhookController;
use Botble\Logistics\Http\Controllers\DashboardController;
use Botble\Logistics\Http\Controllers\ReportWidgetConfigController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'logistics', 'as' => 'logistics.'], function () {
        // Route::resource('/dashboard', DashboardController::class)->names('report')->parameters(['report' => 'report']);

        Route::group(['prefix' => 'dashboard', 'as' => 'report.'], function (): void {
            Route::get('/', [DashboardController::class, 'index'])
                    ->name('index');

            // Route::post('top-selling-products', [
            //     'as' => 'top-selling-products',
            //     'uses' => 'DashboardController@getTopSellingProducts',
            //     'permission' => 'ecommerce.report.index',
            // ]);

            // Route::post('recent-orders', [
            //     'as' => 'recent-orders',
            //     'uses' => 'DashboardController@getRecentOrders',
            //     'permission' => 'ecommerce.report.index',
            // ]);

            // Route::post('trending-products', [
            //     'as' => 'trending-products',
            //     'uses' => 'DashboardController@getTrendingProducts',
            //     'permission' => 'ecommerce.report.index',
            // ]);

            // Route::get('dashboard-general-report', [
            //     'as' => 'dashboard-widget.general',
            //     'uses' => 'DashboardController@getDashboardWidgetGeneral',
            //     'permission' => 'ecommerce.report.index',
            // ]);

            Route::group(['prefix' => 'widget-config', 'as' => 'widget-config.'], function (): void {
                Route::get('/', [ReportWidgetConfigController::class, 'index'])
                    ->name('index');

                Route::get('save', [ReportWidgetConfigController::class, 'store'])
                    ->name('save');
                
                Route::get('get', [ReportWidgetConfigController::class, 'getConfiguration'])
                    ->name('get');
            });
        });

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
Route::post('/webhook/shipping/{provider}', [WebhookController::class, 'webhook']);


// phí ship trang thanh toán
Route::post('/ajax/shipping-fee-checkout', [ShippingFeeController::class, 'shippingFeeCheckout'])->name('checkout.shipping.fee');
