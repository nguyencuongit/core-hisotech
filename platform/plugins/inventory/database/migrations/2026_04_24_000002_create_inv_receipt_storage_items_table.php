<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_receipt_storage_items')) {
            Schema::create('inv_receipt_storage_items', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('goods_receipt_id');
                $table->uuid('goods_receipt_item_id');
                $table->uuid('goods_receipt_batch_id')->nullable();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('warehouse_location_id')->nullable();
                $table->unsignedBigInteger('pallet_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variation_id')->nullable();
                $table->string('tracking_type', 20)->default('none');
                $table->string('status', 30)->default('receiving');
                $table->decimal('received_qty', 18, 4)->default(0);
                $table->decimal('available_qty', 18, 4)->default(0);
                $table->decimal('qc_hold_qty', 18, 4)->default(0);
                $table->decimal('damaged_qty', 18, 4)->default(0);
                $table->decimal('rejected_qty', 18, 4)->default(0);
                $table->timestamp('received_at')->nullable();
                $table->timestamp('qc_at')->nullable();
                $table->timestamp('putaway_at')->nullable();
                $table->timestamp('stored_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamp('posted_at')->nullable();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->json('meta_json')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['warehouse_id', 'status'], 'inv_rsi_wh_status_idx');
                $table->index(['goods_receipt_id', 'goods_receipt_item_id'], 'inv_rsi_receipt_item_idx');
                $table->index(['warehouse_id', 'warehouse_location_id'], 'inv_rsi_wh_location_idx');
                $table->index('posted_at', 'inv_rsi_posted_at_idx');

                $table->foreign('goods_receipt_id', 'fk_inv_rsi_receipt')
                    ->references('id')
                    ->on('inv_goods_receipts')
                    ->cascadeOnDelete();
                $table->foreign('goods_receipt_item_id', 'fk_inv_rsi_item')
                    ->references('id')
                    ->on('inv_goods_receipt_items')
                    ->cascadeOnDelete();
                $table->foreign('goods_receipt_batch_id', 'fk_inv_rsi_batch')
                    ->references('id')
                    ->on('inv_goods_receipt_batches')
                    ->nullOnDelete();
                $table->foreign('warehouse_id', 'fk_inv_rsi_warehouse')
                    ->references('id')
                    ->on('inv_warehouses')
                    ->cascadeOnDelete();
                $table->foreign('warehouse_location_id', 'fk_inv_rsi_location')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();
                $table->foreign('pallet_id', 'fk_inv_rsi_pallet')
                    ->references('id')
                    ->on('inv_pallets')
                    ->nullOnDelete();
                $table->foreign('product_id', 'fk_inv_rsi_product')
                    ->references('id')
                    ->on('ec_products')
                    ->cascadeOnDelete();
                $table->foreign('product_variation_id', 'fk_inv_rsi_variation')
                    ->references('id')
                    ->on('ec_product_variations')
                    ->nullOnDelete();
            });

            return;
        }

        Schema::table('inv_receipt_storage_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_receipt_storage_items', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('closed_at');
            }

            if (! Schema::hasColumn('inv_receipt_storage_items', 'posted_by')) {
                $table->foreignId('posted_by')->nullable()->after('posted_at')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('inv_receipt_storage_items', function (Blueprint $table): void {
            try {
                $table->index(['goods_receipt_id', 'goods_receipt_item_id'], 'inv_rsi_receipt_item_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index(['warehouse_id', 'warehouse_location_id'], 'inv_rsi_wh_location_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('posted_at', 'inv_rsi_posted_at_idx');
            } catch (\Throwable) {
            }

            try {
                $table->foreign('goods_receipt_id', 'fk_inv_rsi_receipt')
                    ->references('id')
                    ->on('inv_goods_receipts')
                    ->cascadeOnDelete();
            } catch (\Throwable) {
            }

            try {
                $table->foreign('goods_receipt_item_id', 'fk_inv_rsi_item')
                    ->references('id')
                    ->on('inv_goods_receipt_items')
                    ->cascadeOnDelete();
            } catch (\Throwable) {
            }

            try {
                $table->foreign('goods_receipt_batch_id', 'fk_inv_rsi_batch')
                    ->references('id')
                    ->on('inv_goods_receipt_batches')
                    ->nullOnDelete();
            } catch (\Throwable) {
            }

            try {
                $table->foreign('warehouse_id', 'fk_inv_rsi_warehouse')
                    ->references('id')
                    ->on('inv_warehouses')
                    ->cascadeOnDelete();
            } catch (\Throwable) {
            }

            try {
                $table->foreign('warehouse_location_id', 'fk_inv_rsi_location')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();
            } catch (\Throwable) {
            }

            try {
                $table->foreign('pallet_id', 'fk_inv_rsi_pallet')
                    ->references('id')
                    ->on('inv_pallets')
                    ->nullOnDelete();
            } catch (\Throwable) {
            }

            try {
                $table->foreign('product_id', 'fk_inv_rsi_product')
                    ->references('id')
                    ->on('ec_products')
                    ->cascadeOnDelete();
            } catch (\Throwable) {
            }

            try {
                $table->foreign('product_variation_id', 'fk_inv_rsi_variation')
                    ->references('id')
                    ->on('ec_product_variations')
                    ->nullOnDelete();
            } catch (\Throwable) {
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inv_receipt_storage_items')) {
            return;
        }

        Schema::table('inv_receipt_storage_items', function (Blueprint $table): void {
            if (Schema::hasColumn('inv_receipt_storage_items', 'posted_by')) {
                $table->dropConstrainedForeignId('posted_by');
            }

            if (Schema::hasColumn('inv_receipt_storage_items', 'posted_at')) {
                $table->dropColumn('posted_at');
            }
        });
    }
};
