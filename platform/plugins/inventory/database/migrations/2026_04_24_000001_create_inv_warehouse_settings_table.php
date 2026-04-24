<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_warehouse_settings')) {
            Schema::create('inv_warehouse_settings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('warehouse_id')->unique();
                $table->enum('warehouse_mode', ['simple', 'advanced'])->default('simple');
                $table->boolean('use_pallet')->default(false);
                $table->boolean('require_pallet')->default(false);
                $table->boolean('use_qc')->default(false);
                $table->boolean('use_batch')->default(false);
                $table->boolean('use_serial')->default(false);
                $table->boolean('use_map')->default(false);
                $table->unsignedBigInteger('default_receiving_location_id')->nullable();
                $table->unsignedBigInteger('default_waiting_putaway_location_id')->nullable();
                $table->unsignedBigInteger('default_qc_location_id')->nullable();
                $table->unsignedBigInteger('default_damaged_location_id')->nullable();
                $table->unsignedBigInteger('default_rejected_location_id')->nullable();
                $table->timestamps();

                $table->foreign('warehouse_id', 'fk_inv_wh_settings_warehouse')
                    ->references('id')
                    ->on('inv_warehouses')
                    ->cascadeOnDelete();

                $table->foreign('default_receiving_location_id', 'fk_inv_wh_settings_recv_loc')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();

                $table->foreign('default_waiting_putaway_location_id', 'fk_inv_wh_settings_wait_loc')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();

                $table->foreign('default_qc_location_id', 'fk_inv_wh_settings_qc_loc')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();

                $table->foreign('default_damaged_location_id', 'fk_inv_wh_settings_dmg_loc')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();

                $table->foreign('default_rejected_location_id', 'fk_inv_wh_settings_rjt_loc')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_warehouse_settings');
    }
};
