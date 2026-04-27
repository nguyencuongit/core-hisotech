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
        Schema::create('inv_packing_list_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('packing_list_id')->index();
            $table->unsignedBigInteger('packing_id')->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name')->nullable();
            // Số lượng thực tế đóng
            $table->decimal('packed_qty', 15, 2)->default(0);
            // Đơn vị
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('unit_name')->nullable();
            // Nếu cần tracking theo vị trí
            $table->unsignedBigInteger('warehouse_location_id')->nullable();
            // Ghi chú
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_packing_list_items');
    }
};
