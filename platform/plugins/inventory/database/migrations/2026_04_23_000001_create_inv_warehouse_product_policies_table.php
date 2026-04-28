<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_warehouse_product_policies')) {
            Schema::create('inv_warehouse_product_policies', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('warehouse_product_id');
                $table->enum('tracking_type', ['none', 'batch', 'serial'])->default('none');
                $table->boolean('is_expirable')->default(false);
                $table->boolean('require_mfg_date')->default(false);
                $table->boolean('require_expiry_date')->default(false);
                $table->boolean('allow_pallet')->default(true);
                $table->boolean('require_pallet')->default(false);
                $table->boolean('require_qc')->default(false);
                $table->enum('placement_mode', ['assigned_on_receipt', 'putaway_after_receipt'])->default('putaway_after_receipt');
                $table->boolean('allow_mixed_batch_on_pallet')->default(false);
                $table->boolean('allow_receive_without_location')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique('warehouse_product_id', 'inv_wh_prod_policies_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_warehouse_product_policies');
    }
};
