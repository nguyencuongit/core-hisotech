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
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'ward')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('ward', 60)->nullable()->after('city');
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
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'ward')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('ward');
                });
            }
        }
    }
};