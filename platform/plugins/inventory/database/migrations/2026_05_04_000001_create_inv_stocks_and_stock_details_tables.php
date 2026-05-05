<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_stocks')) {
            Schema::create('inv_stocks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('reserved_qty', 15, 4)->default(0);
                $table->decimal('available_qty', 15, 4)->default(0);
                $table->text('note')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('unit_name', 120)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->softDeletes();

                $table->index('warehouse_id');
                $table->index('product_id');
                $table->index('unit_id');
                $table->index('deleted_at');
                $table->unique(['warehouse_id', 'product_id'], 'inv_stocks_warehouse_product_unit_unique');
            });
        }

        if (! Schema::hasTable('inv_stock_details')) {
            Schema::create('inv_stock_details', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('stock_id');
                $table->string('lot_no', 120)->nullable();
                $table->date('expiry_date')->nullable();
                $table->unsignedBigInteger('warehouse_location_id')->nullable();
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('reserved_qty', 15, 4)->default(0);
                $table->decimal('available_qty', 15, 4)->default(0);
                $table->text('note')->nullable();
                $table->decimal('avg_cost', 15, 4)->default(0);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->softDeletes();

                $table->index('stock_id');
                $table->index('lot_no');
                $table->index('expiry_date');
                $table->index('warehouse_location_id');
                $table->index('deleted_at');
                $table->index(['stock_id', 'warehouse_location_id'], 'inv_stock_details_stock_location_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_stock_details');
        Schema::dropIfExists('inv_stocks');
    }
};
