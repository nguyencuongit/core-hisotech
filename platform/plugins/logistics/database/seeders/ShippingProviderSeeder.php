<?php

namespace Botble\Logistics\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        DB::table('shipping_providers')->insert([
            [
                'name' => 'Giao Hàng Nhanh',
                'code' => 'ghn',
                'is_active' => 1,
                'information' => json_encode([
                    'token' => '23213123213123123',
                    'shop_id' => '21312312',
                    'url_rovince' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_district' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_ward' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_fee' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_create' => 'https://online-gateway.ghn.vn/shiip/public-api',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Viettel Post',
                'code' => 'viettelpost',
                'is_active' => 1,
                'information' => json_encode([
                    'token' => '123123123123',
                    'shop_id' => '12312312321',
                    'url_rovince' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_district' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_ward' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_fee' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_create' => 'https://online-gateway.ghn.vn/shiip/public-api',
                    'url_token' => 'https://online-gateway.ghn.vn/shiip/public-api',

                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
