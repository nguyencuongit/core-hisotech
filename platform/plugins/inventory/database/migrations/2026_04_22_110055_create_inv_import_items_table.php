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
        Schema::create('inv_import_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('product_name')->nullable();
            $table->string('product_code')->nullable();

            $table->decimal('document_qty', 15, 2)->default(0);
            $table->decimal('received_qty', 15, 2)->default(0);

            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('unit_name')->nullable();

            $table->unsignedBigInteger('warehouse_location_id')->nullable();

            $table->decimal('amount', 15, 2)->default(0);

            // Lô / hạn
            $table->string('lot_no')->nullable();
            $table->date('expiry_date')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('import_id');
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
        Schema::dropIfExists('inv_import_items');
    }
};
