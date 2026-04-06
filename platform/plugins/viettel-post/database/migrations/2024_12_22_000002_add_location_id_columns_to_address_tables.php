<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'ec_customer_addresses',
            'ec_order_addresses',
            'ec_store_locators',
            'mp_stores',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    if (! Schema::hasColumn($table, 'state_id')) {
                        $blueprint->unsignedInteger('state_id')->nullable()->after('state');
                    }
                    if (! Schema::hasColumn($table, 'city_id')) {
                        $blueprint->unsignedInteger('city_id')->nullable()->after('city');
                    }
                    if (! Schema::hasColumn($table, 'ward_id')) {
                        $blueprint->unsignedInteger('ward_id')->nullable()->after('ward');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'ec_customer_addresses',
            'ec_order_addresses',
            'ec_store_locators',
            'mp_stores',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    if (Schema::hasColumn($table, 'state_id')) {
                        $blueprint->dropColumn('state_id');
                    }
                    if (Schema::hasColumn($table, 'city_id')) {
                        $blueprint->dropColumn('city_id');
                    }
                    if (Schema::hasColumn($table, 'ward_id')) {
                        $blueprint->dropColumn('ward_id');
                    }
                });
            }
        }
    }
};