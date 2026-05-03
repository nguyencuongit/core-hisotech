<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_internal_transfer_items')) {
            return;
        }

        Schema::table('inv_internal_transfer_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_internal_transfer_items', 'stock_balance_id')) {
                $table->char('stock_balance_id', 36)->nullable()->after('transfer_id')->index();
            }

            if (! Schema::hasColumn('inv_internal_transfer_items', 'product_variation_id')) {
                $table->unsignedBigInteger('product_variation_id')->nullable()->after('product_id')->index();
            }

            if (! Schema::hasColumn('inv_internal_transfer_items', 'pallet_id')) {
                $table->unsignedBigInteger('pallet_id')->nullable()->after('to_location_id')->index();
            }

            if (! Schema::hasColumn('inv_internal_transfer_items', 'batch_id')) {
                $table->char('batch_id', 36)->nullable()->after('pallet_id')->index();
            }

            if (! Schema::hasColumn('inv_internal_transfer_items', 'goods_receipt_batch_id')) {
                $table->char('goods_receipt_batch_id', 36)->nullable()->after('batch_id')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inv_internal_transfer_items')) {
            return;
        }

        Schema::table('inv_internal_transfer_items', function (Blueprint $table): void {
            foreach ([
                'stock_balance_id',
                'product_variation_id',
                'pallet_id',
                'batch_id',
                'goods_receipt_batch_id',
            ] as $column) {
                if (Schema::hasColumn('inv_internal_transfer_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
