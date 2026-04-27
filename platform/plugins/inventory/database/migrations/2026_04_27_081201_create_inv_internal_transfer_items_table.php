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
        Schema::create('inv_internal_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('transfer_id')->index();

            // Product snapshot
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_code', 120)->nullable();
            $table->string('product_name', 255)->nullable();

            // Số lượng yêu cầu chuyển
            $table->decimal('requested_qty', 15, 2)->default(0);

            // Đơn vị
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('unit_name', 120)->nullable();

            // Vị trí xuất / nhập theo từng dòng
            $table->unsignedBigInteger('from_location_id')->nullable()->index();
            $table->unsignedBigInteger('to_location_id')->nullable()->index();

            // Lô / hạn nếu quản lý batch
            $table->string('lot_no', 120)->nullable();
            $table->date('expiry_date')->nullable();

            // Giá trị tham khảo nếu cần báo cáo
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);

            // Ghi chú
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['transfer_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_internal_transfer_items');
    }
};
