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
        Schema::create('inv_return_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('return_id')->index();

            // Product snapshot
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_code', 120)->nullable();
            $table->string('product_name', 255)->nullable();

            // Số lượng
            $table->decimal('quantity', 15, 2)->default(0);

            // Đơn vị
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('unit_name', 120)->nullable();

            // Tình trạng hàng
            // good | damaged | expired | lost
            $table->string('condition', 50)->nullable();

            // Lý do từng dòng (chi tiết hơn header)
            $table->string('reason', 255)->nullable();

            // Tham chiếu dòng gốc (cực kỳ quan trọng để trace)
            $table->unsignedBigInteger('reference_item_id')->nullable();

            // Giá trị (nếu cần tính tiền)
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);

            // Ghi chú
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_return_items');
    }
};
