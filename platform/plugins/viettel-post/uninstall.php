<?php

use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

Schema::dropIfExists('wards');
Schema::dropIfExists('districts');
Schema::dropIfExists('provinces');

$tables = [
    'ec_customer_addresses',
    'ec_order_addresses',
    'ec_store_locators',
];

foreach ($tables as $table) {
    if (Schema::hasTable($table) && Schema::hasColumn($table, 'ward')) {
        Schema::table($table, function ($table) {
            $table->dropColumn('ward');
        });
    }
}

Setting::delete([
    'viettel_post_status',
    'viettel_post_username',
    'viettel_post_password',
    'viettel_post_partner_code',
    'viettel_post_api_key',
    'viettel_post_shop_id',
    'viettel_post_customer_id',
    'viettel_post_default_service',
    'viettel_post_sender_name',
    'viettel_post_sender_phone',
    'viettel_post_sender_address',
    'viettel_post_sender_province_id',
    'viettel_post_sender_district_id',
    'viettel_post_sender_ward_id',
    'viettel_post_auto_create_shipment',
    'viettel_post_default_fee',
]);

Cache::forget('viettel_post_token');
Cache::forget('viettel_post_services');
Cache::forget('viettel_post_provinces');