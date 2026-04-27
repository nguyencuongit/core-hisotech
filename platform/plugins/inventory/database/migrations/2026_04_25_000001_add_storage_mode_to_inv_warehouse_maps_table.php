<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inv_warehouse_maps') && ! Schema::hasColumn('inv_warehouse_maps', 'storage_mode')) {
            Schema::table('inv_warehouse_maps', function (Blueprint $table): void {
                $table->string('storage_mode', 30)->default('direct')->after('map_type');
                $table->index('storage_mode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inv_warehouse_maps') && Schema::hasColumn('inv_warehouse_maps', 'storage_mode')) {
            Schema::table('inv_warehouse_maps', function (Blueprint $table): void {
                $table->dropIndex(['storage_mode']);
                $table->dropColumn('storage_mode');
            });
        }
    }
};
