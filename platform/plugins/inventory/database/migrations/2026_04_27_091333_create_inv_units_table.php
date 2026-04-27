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
        Schema::create('inv_units', function (Blueprint $table) {
            $table->id();

            // Mã đơn vị
            $table->string('code', 50)->unique();

            // Tên đơn vị
            $table->string('name', 120);

            // Ký hiệu
            $table->string('symbol', 50)->nullable(); // cái, box, kg...

            // Mô tả
            $table->string('description', 255)->nullable();

            // Trạng thái
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_units');
    }
};
