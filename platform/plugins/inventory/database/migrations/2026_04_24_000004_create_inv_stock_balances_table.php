<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_stock_balances')) {
            Schema::create('inv_stock_balances', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variation_id')->nullable();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('warehouse_location_id')->nullable();
                $table->unsignedBigInteger('pallet_id')->nullable();
                $table->uuid('batch_id')->nullable();
                $table->uuid('goods_receipt_batch_id')->nullable();
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('reserved_qty', 15, 4)->default(0);
                $table->decimal('available_qty', 15, 4)->default(0);
                $table->decimal('qc_hold_qty', 15, 4)->default(0);
                $table->decimal('damaged_qty', 15, 4)->default(0);
                $table->decimal('rejected_qty', 15, 4)->default(0);
                $table->decimal('average_cost', 15, 4)->default(0);
                $table->decimal('last_unit_cost', 15, 4)->default(0);
                $table->timestamp('updated_at')->nullable();

                $table->index('product_id');
                $table->index('product_variation_id');
                $table->index('warehouse_id');
                $table->index('warehouse_location_id');
                $table->index('pallet_id');
                $table->index('goods_receipt_batch_id');
                $table->index('updated_at');
                $table->unique([
                    'product_id',
                    'product_variation_id',
                    'warehouse_id',
                    'warehouse_location_id',
                    'pallet_id',
                    'goods_receipt_batch_id',
                ], 'inv_stock_balances_unique_dimension_v2');
            });

            return;
        }

        Schema::table('inv_stock_balances', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_stock_balances', 'pallet_id')) {
                $table->unsignedBigInteger('pallet_id')->nullable()->after('warehouse_location_id');
            }

            if (! Schema::hasColumn('inv_stock_balances', 'goods_receipt_batch_id')) {
                $table->uuid('goods_receipt_batch_id')->nullable()->after('batch_id');
            }

            if (! Schema::hasColumn('inv_stock_balances', 'qc_hold_qty')) {
                $table->decimal('qc_hold_qty', 15, 4)->default(0)->after('available_qty');
            }

            if (! Schema::hasColumn('inv_stock_balances', 'damaged_qty')) {
                $table->decimal('damaged_qty', 15, 4)->default(0)->after('qc_hold_qty');
            }

            if (! Schema::hasColumn('inv_stock_balances', 'rejected_qty')) {
                $table->decimal('rejected_qty', 15, 4)->default(0)->after('damaged_qty');
            }
        });

        Schema::table('inv_stock_balances', function (Blueprint $table): void {
            try {
                $table->dropUnique('inv_stock_balances_unique_stock_dimension');
            } catch (\Throwable) {
            }

            try {
                $table->unique([
                    'product_id',
                    'product_variation_id',
                    'warehouse_id',
                    'warehouse_location_id',
                    'pallet_id',
                    'goods_receipt_batch_id',
                ], 'inv_stock_balances_unique_dimension_v2');
            } catch (\Throwable) {
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inv_stock_balances')) {
            return;
        }

        Schema::table('inv_stock_balances', function (Blueprint $table): void {
            try {
                $table->dropUnique('inv_stock_balances_unique_dimension_v2');
            } catch (\Throwable) {
            }

            foreach (['pallet_id', 'goods_receipt_batch_id', 'qc_hold_qty', 'damaged_qty', 'rejected_qty'] as $column) {
                if (Schema::hasColumn('inv_stock_balances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
