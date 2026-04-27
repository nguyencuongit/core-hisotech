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
        Schema::create('inv_packages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('packing_list_id')->index();

            // Mã kiện
            $table->string('package_code', 120)->unique();

            // Loại kiện: box | pallet | bag | crate...
            $table->string('package_type_id', 50)->nullable();

            // Kích thước kiện
            $table->decimal('length', 15, 2)->default(0); // dài
            $table->decimal('width', 15, 2)->default(0);  // rộng
            $table->decimal('height', 15, 2)->default(0); // cao

            // Trọng lượng
            $table->decimal('weight', 15, 2)->default(0);
            $table->string('weight_unit', 20)->default('kg');

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
        Schema::dropIfExists('inv_packages');
    }
};
