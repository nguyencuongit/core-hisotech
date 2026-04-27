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
        Schema::create('inv_packing_lists', function (Blueprint $table) {
            $table->id();

            // Liên kết chứng từ
            $table->unsignedBigInteger('export_id')->index();

            // Kho
            $table->unsignedBigInteger('warehouse_id')->nullable();

            // Mã packing list
            $table->string('code')->unique();

            // Trạng thái
            // draft | packing | packed | shipped | cancelled
            $table->string('status', 50)->default('draft')->index();

            // Người đóng gói
            $table->unsignedBigInteger('packer_id')->nullable();
 
            // Thời gian đóng
            $table->timestamp('packed_at')->nullable();

            // Tổng số kiện (optional nếu tách bảng package)
            $table->integer('total_packages')->default(1);

            // Tổng trọng lượng
            $table->decimal('total_weight', 15, 2)->default(0);

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
        Schema::dropIfExists('inv_packing_lists');
    }
};
