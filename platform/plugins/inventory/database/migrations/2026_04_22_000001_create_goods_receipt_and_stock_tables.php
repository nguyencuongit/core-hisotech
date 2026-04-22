<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_goods_receipts')) {
            Schema::create('inv_goods_receipts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('code', 50)->unique();
                $table->uuid('supplier_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->date('receipt_date')->nullable();
                $table->string('status', 50)->default('draft');
                $table->string('reference_code', 100)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('note')->nullable();
                $table->decimal('subtotal', 15, 4)->default(0);
                $table->decimal('discount_amount', 15, 4)->default(0);
                $table->decimal('tax_amount', 15, 4)->default(0);
                $table->decimal('total_amount', 15, 4)->default(0);
                $table->timestamps();

                $table->index('supplier_id');
                $table->index('warehouse_id');
                $table->index('receipt_date');
                $table->index('status');
                $table->index('reference_code');

                $table->foreign('supplier_id')->references('id')->on('inv_suppliers')->restrictOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('inv_warehouses')->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('inv_goods_receipt_items')) {
            Schema::create('inv_goods_receipt_items', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('goods_receipt_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variation_id')->nullable();
                $table->uuid('supplier_product_id')->nullable();
                $table->string('product_name');
                $table->string('sku', 100)->nullable();
                $table->string('barcode', 100)->nullable();
                $table->decimal('ordered_qty', 15, 4)->default(0);
                $table->decimal('received_qty', 15, 4)->default(0);
                $table->decimal('rejected_qty', 15, 4)->default(0);
                $table->decimal('unit_cost', 15, 4)->default(0);
                $table->decimal('line_total', 15, 4)->default(0);
                $table->string('uom', 50)->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index('goods_receipt_id');
                $table->index('product_id');
                $table->index('product_variation_id');
                $table->index('supplier_product_id');
                $table->index('sku');
                $table->index('barcode');

                $table->foreign('goods_receipt_id')->references('id')->on('inv_goods_receipts')->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('ec_products')->restrictOnDelete();
                $table->foreign('product_variation_id')->references('id')->on('ec_product_variations')->nullOnDelete();
                $table->foreign('supplier_product_id')->references('id')->on('inv_supplier_products')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('inv_goods_receipt_batches')) {
            Schema::create('inv_goods_receipt_batches', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('goods_receipt_item_id');
                $table->string('batch_no', 100)->nullable();
                $table->string('serial_no', 100)->nullable();
                $table->timestamp('manufactured_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->decimal('received_qty', 15, 4)->default(0);
                $table->decimal('unit_cost', 15, 4)->default(0);
                $table->unsignedBigInteger('warehouse_location_id')->nullable();
                $table->string('status', 50)->default('received');
                $table->timestamps();

                $table->index('goods_receipt_item_id');
                $table->index('batch_no');
                $table->index('serial_no');
                $table->index('expired_at');
                $table->index('warehouse_location_id');
                $table->index('status');

                $table->foreign('goods_receipt_item_id')->references('id')->on('inv_goods_receipt_items')->cascadeOnDelete();
                $table->foreign('warehouse_location_id')->references('id')->on('inv_warehouse_locations')->nullOnDelete();
            });
        }

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
                $table->uuid('batch_id')->nullable();
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('unit_cost', 15, 4)->default(0);
                $table->decimal('before_qty', 15, 4)->default(0);
                $table->decimal('after_qty', 15, 4)->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('type');
                $table->index(['reference_type', 'reference_id']);
                $table->index('reference_item_id');
                $table->index('product_id');
                $table->index('product_variation_id');
                $table->index('warehouse_id');
                $table->index('warehouse_location_id');
                $table->index('batch_id');
                $table->index('created_at');

                $table->foreign('product_id')->references('id')->on('ec_products')->restrictOnDelete();
                $table->foreign('product_variation_id')->references('id')->on('ec_product_variations')->nullOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('inv_warehouses')->restrictOnDelete();
                $table->foreign('warehouse_location_id')->references('id')->on('inv_warehouse_locations')->nullOnDelete();
                $table->foreign('batch_id')->references('id')->on('inv_goods_receipt_batches')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('inv_stock_balances')) {
            Schema::create('inv_stock_balances', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variation_id')->nullable();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('warehouse_location_id')->nullable();
                $table->uuid('batch_id')->nullable();
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('reserved_qty', 15, 4)->default(0);
                $table->decimal('available_qty', 15, 4)->default(0);
                $table->decimal('average_cost', 15, 4)->default(0);
                $table->decimal('last_unit_cost', 15, 4)->default(0);
                $table->timestamp('updated_at')->nullable();

                $table->index('product_id');
                $table->index('product_variation_id');
                $table->index('warehouse_id');
                $table->index('warehouse_location_id');
                $table->index('batch_id');
                $table->index('updated_at');
                $table->unique([
                    'product_id',
                    'product_variation_id',
                    'warehouse_id',
                    'warehouse_location_id',
                    'batch_id',
                ], 'inv_stock_balances_unique_stock_dimension');

                $table->foreign('product_id')->references('id')->on('ec_products')->restrictOnDelete();
                $table->foreign('product_variation_id')->references('id')->on('ec_product_variations')->nullOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('inv_warehouses')->restrictOnDelete();
                $table->foreign('warehouse_location_id')->references('id')->on('inv_warehouse_locations')->nullOnDelete();
                $table->foreign('batch_id')->references('id')->on('inv_goods_receipt_batches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_stock_balances');
        Schema::dropIfExists('inv_stock_transactions');
        Schema::dropIfExists('inv_goods_receipt_batches');
        Schema::dropIfExists('inv_goods_receipt_items');
        Schema::dropIfExists('inv_goods_receipts');
    }
};
