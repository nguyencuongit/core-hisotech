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
        Schema::create('inv_internal_transfers', function (Blueprint $table) {
            $table->id();

            // Mã phiếu chuyển kho
            $table->string('code', 50)->nullable()->index();

            // Trạng thái
            // draft | confirmed | exporting | importing | completed | cancelled
            $table->string('status', 50)->default('draft')->index();

            // Kho xuất
            $table->unsignedBigInteger('from_warehouse_id')->nullable()->index();

            // Kho nhập
            $table->unsignedBigInteger('to_warehouse_id')->nullable()->index();

            // Người yêu cầu / duyệt / thực hiện
            $table->unsignedBigInteger('requested_by')->nullable()->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->unsignedBigInteger('exported_by')->nullable()->index();
            $table->unsignedBigInteger('imported_by')->nullable()->index();

            // Ngày nghiệp vụ
            $table->date('transfer_date')->nullable()->index();

            // Lý do chuyển kho
            $table->string('reason', 255)->nullable();

            // Ghi chú
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['from_warehouse_id', 'to_warehouse_id']);
            $table->index(['status', 'transfer_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_internal_transfers');
    }
};
