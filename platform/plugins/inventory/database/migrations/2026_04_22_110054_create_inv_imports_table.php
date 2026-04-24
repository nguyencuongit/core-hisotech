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
        Schema::create('inv_imports', function (Blueprint $table) {
            $table->id();
            // Loại phiếu nhập: purchase | transfer | return | manual
            $table->string('type', 50);

            // Kho nhập
            $table->unsignedBigInteger('warehouse_id')->nullable();

            // Đối tượng liên quan: supplier | warehouse | customer
            $table->string('partner_type', 50)->nullable();

            $table->unsignedBigInteger('partner_id')->nullable();
            $table->string('partner_code', 120)->nullable();
            $table->string('partner_name', 255)->nullable();
            $table->string('partner_phone', 30)->nullable();
            $table->string('partner_email', 120)->nullable();
            $table->string('partner_address', 500)->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();

            // người yêu cầu tạo phiếu
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->string('requested_by_name', 255)->nullable();   

            // Chứng từ
            $table->string('doc_code', 120)->unique();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_code', 120)->nullable()->index();

            // Ngày
            $table->date('posting_date')->nullable();   // ngày hạch toán
            $table->date('document_date')->nullable();  // ngày chứng từ
            $table->dateTime('received_at')->nullable(); // ngày / giờ nhập kho thực tế
            
            // Người nhận
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->string('receiver_name', 255)->nullable();
            $table->string('receiver_phone', 30)->nullable();

            // Trạng thái: draft | confirmed | completed | cancelled
            $table->string('status', 50)->default('draft')->index();

            // Ghi chú
            $table->text('note')->nullable();

            // Lô / hạn
            $table->string('lot_no')->nullable();
            $table->date('expiry_date')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index gợi ý
            $table->index('warehouse_id');
            $table->index('partner_id');
            $table->index(['type', 'partner_type']);
            $table->index(['warehouse_id', 'status']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_imports');
    }
};
