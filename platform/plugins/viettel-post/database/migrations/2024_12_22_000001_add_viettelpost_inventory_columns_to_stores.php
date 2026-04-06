<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mp_stores')) {
            Schema::table('mp_stores', function (Blueprint $table) {
                if (! Schema::hasColumn('mp_stores', 'viettelpost_groupaddress_id')) {
                    $table->string('viettelpost_groupaddress_id', 50)->nullable();
                }
                if (! Schema::hasColumn('mp_stores', 'viettelpost_inventory_name')) {
                    $table->string('viettelpost_inventory_name', 255)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mp_stores')) {
            Schema::table('mp_stores', function (Blueprint $table) {
                if (Schema::hasColumn('mp_stores', 'viettelpost_groupaddress_id')) {
                    $table->dropColumn('viettelpost_groupaddress_id');
                }
                if (Schema::hasColumn('mp_stores', 'viettelpost_inventory_name')) {
                    $table->dropColumn('viettelpost_inventory_name');
                }
            });
        }
    }
};
