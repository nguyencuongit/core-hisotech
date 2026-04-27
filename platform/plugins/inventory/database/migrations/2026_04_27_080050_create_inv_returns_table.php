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
        Schema::create('inv_returns', function (Blueprint $table) {
            $table->id();

            // Mã phiếu
            $table->string('code', 50)->nullable()->index();

            // Loại return
            // customer_return: khách trả về kho
            // supplier_return: trả NCC
            $table->string('type', 50)->index();

            // Trạng thái
            // draft | confirmed | completed | cancelled
            $table->string('status', 50)->default('draft')->index();

            // Kho
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();

            // Đối tượng
            $table->string('partner_type', 50)->nullable(); // customer | supplier
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->string('partner_code', 120)->nullable();
            $table->string('partner_name', 255)->nullable();
            $table->string('partner_phone', 30)->nullable();

            // Tham chiếu chứng từ gốc (rất quan trọng)
            $table->string('reference_type', 50)->nullable(); // export | import | order
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_code', 120)->nullable();

            // Lý do chung
            $table->string('reason', 255)->nullable();

            // Thông tin người xử lý
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            // Ghi chú
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index quan trọng
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_returns');
    }
};
