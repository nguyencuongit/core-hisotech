<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inv_export_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('product_name')->nullable();
            $table->string('product_code')->nullable();

            $table->decimal('document_qty', 15, 2)->default(0);
            $table->decimal('shipped_qty', 15, 2)->default(0);

            //đơn vị
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('unit_name')->nullable();

            // vị trí trong kho
            $table->unsignedBigInteger('warehouse_location_id')->nullable();

            // Lô / hạn
            $table->string('lot_no')->nullable();
            $table->date('expiry_date')->nullable();

            // giá tiền nhập
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('export_id');
            $table->index('product_id');
            $table->index('unit_id');
            $table->index('warehouse_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_export_items');
    }
};
