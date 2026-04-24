<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_stock_transactions')) {
            Schema::create('inv_stock_transactions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('transaction_code', 50)->unique();
                $table->string('type', 50);
                $table->string('reference_type', 100);
                $table->uuid('reference_id');
                $table->uuid('reference_item_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variation_id')->nullable();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('warehouse_location_id')->nullable();
                $table->unsignedBigInteger('pallet_id')->nullable();
                $table->uuid('storage_item_id')->nullable();
                $table->uuid('goods_receipt_id')->nullable();
                $table->uuid('goods_receipt_item_id')->nullable();
                $table->uuid('goods_receipt_batch_id')->nullable();
                $table->uuid('batch_id')->nullable();
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('unit_cost', 15, 4)->default(0);
                $table->decimal('before_qty', 15, 4)->default(0);
                $table->decimal('after_qty', 15, 4)->default(0);
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('type');
                $table->index(['reference_type', 'reference_id']);
                $table->index('reference_item_id');
                $table->index('product_id');
                $table->index('product_variation_id');
                $table->index('warehouse_id');
                $table->index('warehouse_location_id');
                $table->index('pallet_id');
                $table->index('storage_item_id');
                $table->index('goods_receipt_id');
                $table->index('goods_receipt_item_id');
                $table->index('goods_receipt_batch_id');
                $table->index('created_at');
            });

            return;
        }

        Schema::table('inv_stock_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_stock_transactions', 'pallet_id')) {
                $table->unsignedBigInteger('pallet_id')->nullable()->after('warehouse_location_id');
            }

            if (! Schema::hasColumn('inv_stock_transactions', 'storage_item_id')) {
                $table->uuid('storage_item_id')->nullable()->after('pallet_id');
            }

            if (! Schema::hasColumn('inv_stock_transactions', 'goods_receipt_id')) {
                $table->uuid('goods_receipt_id')->nullable()->after('storage_item_id');
            }

            if (! Schema::hasColumn('inv_stock_transactions', 'goods_receipt_item_id')) {
                $table->uuid('goods_receipt_item_id')->nullable()->after('goods_receipt_id');
            }

            if (! Schema::hasColumn('inv_stock_transactions', 'goods_receipt_batch_id')) {
                $table->uuid('goods_receipt_batch_id')->nullable()->after('goods_receipt_item_id');
            }

            if (! Schema::hasColumn('inv_stock_transactions', 'note')) {
                $table->text('note')->nullable()->after('after_qty');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inv_stock_transactions')) {
            return;
        }

        Schema::table('inv_stock_transactions', function (Blueprint $table): void {
            foreach (['pallet_id', 'storage_item_id', 'goods_receipt_id', 'goods_receipt_item_id', 'goods_receipt_batch_id', 'note'] as $column) {
                if (Schema::hasColumn('inv_stock_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
