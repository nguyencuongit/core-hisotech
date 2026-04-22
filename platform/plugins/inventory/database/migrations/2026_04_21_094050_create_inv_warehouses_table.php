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
        Schema::create('inv_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');

            $table->string('type')->nullable();

            $table->unsignedBigInteger('manager_id')->nullable();

            $table->string('address')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->boolean('status')->default(true);

            $table->text('description')->nullable();
            $table->timestamps();

            // index (khuyên dùng)
            $table->index('manager_id');
            $table->index('province_id');
            $table->index('ward_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_warehouses');
    }
};
