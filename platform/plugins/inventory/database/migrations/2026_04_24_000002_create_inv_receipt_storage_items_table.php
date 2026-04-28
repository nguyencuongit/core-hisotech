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
                $table->unsignedBigInteger('posted_by')->nullable();
                $table->text('note')->nullable();
                $table->json('meta_json')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['warehouse_id', 'status'], 'inv_rsi_wh_status_idx');
                $table->index(['goods_receipt_id', 'goods_receipt_item_id'], 'inv_rsi_receipt_item_idx');
                $table->index(['warehouse_id', 'warehouse_location_id'], 'inv_rsi_wh_location_idx');
                $table->index('goods_receipt_item_id', 'inv_rsi_item_id_idx');
                $table->index('goods_receipt_batch_id', 'inv_rsi_batch_id_idx');
                $table->index('warehouse_location_id', 'inv_rsi_location_id_idx');
                $table->index('pallet_id', 'inv_rsi_pallet_id_idx');
                $table->index('product_id', 'inv_rsi_product_id_idx');
                $table->index('product_variation_id', 'inv_rsi_variation_id_idx');
                $table->index('posted_at', 'inv_rsi_posted_at_idx');
                $table->index('posted_by', 'inv_rsi_posted_by_idx');
                $table->index('created_by', 'inv_rsi_created_by_idx');
            });

            return;
        }

        Schema::table('inv_receipt_storage_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_receipt_storage_items', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('closed_at');
            }

            if (! Schema::hasColumn('inv_receipt_storage_items', 'posted_by')) {
                $table->unsignedBigInteger('posted_by')->nullable()->after('posted_at');
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
                $table->index('goods_receipt_item_id', 'inv_rsi_item_id_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('goods_receipt_batch_id', 'inv_rsi_batch_id_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('warehouse_location_id', 'inv_rsi_location_id_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('pallet_id', 'inv_rsi_pallet_id_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('product_id', 'inv_rsi_product_id_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('product_variation_id', 'inv_rsi_variation_id_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('posted_by', 'inv_rsi_posted_by_idx');
            } catch (\Throwable) {
            }

            try {
                $table->index('created_by', 'inv_rsi_created_by_idx');
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
                $table->dropColumn('posted_by');
            }

            if (Schema::hasColumn('inv_receipt_storage_items', 'posted_at')) {
                $table->dropColumn('posted_at');
            }
        });
    }
};
