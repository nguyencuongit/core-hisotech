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
        Schema::create('inv_exports', function (Blueprint $table) {
            $table->id();

            // Loại xuất
            // sale: bán hàng
            // transfer: chuyển kho
            // return: trả NCC
            // adjustment: điều chỉnh
            // manual: khác
            $table->string('type', 50);

            // Trạng thái
            // draft | confirmed | picking | shipping | completed | cancelled
            $table->string('status', 50)->default('draft')->index();

            // Kho xuất
            $table->unsignedBigInteger('warehouse_id')->nullable();

            // Đối tượng (customer | warehouse | supplier)
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

            // Mã phiếu
            $table->string('code', 120)->unique();

            // Tham chiếu
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_code', 120)->nullable()->index();

            // Ngày
            $table->date('document_date')->nullable();
            $table->date('posting_date')->nullable();
            $table->dateTime('shipped_at')->nullable(); // ngày xuất thực tế

            // Người nhận (khách / kho khác)
            $table->string('receiver_name', 255)->nullable();
            $table->string('receiver_phone', 30)->nullable();
            $table->string('receiver_address', 500)->nullable();

            // Người giao hàng đi
            $table->string('delivery_name', 255)->nullable();
            $table->string('delivery_phone', 30)->nullable();

            // Thông tin vận chuyển
            $table->string('shipping_unit')->nullable(); // GHN, GHTK...
            $table->string('tracking_code')->nullable();
            $table->decimal('shipping_fee', 15, 2)->default(0);

            // Ghi chú
            $table->text('note')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->unsignedBigInteger('completed_by')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
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
        Schema::dropIfExists('inv_exports');
    }
};
