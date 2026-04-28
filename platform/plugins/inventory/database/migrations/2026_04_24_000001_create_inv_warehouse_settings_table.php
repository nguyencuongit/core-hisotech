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

                $table->index('default_receiving_location_id', 'inv_wh_settings_recv_loc_idx');
                $table->index('default_waiting_putaway_location_id', 'inv_wh_settings_wait_loc_idx');
                $table->index('default_qc_location_id', 'inv_wh_settings_qc_loc_idx');
                $table->index('default_damaged_location_id', 'inv_wh_settings_dmg_loc_idx');
                $table->index('default_rejected_location_id', 'inv_wh_settings_rjt_loc_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_warehouse_settings');
    }
};
