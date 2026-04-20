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
        Schema::create('inv_warehouse_staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();

            $table->boolean('is_primary')->default(false);
            $table->boolean('status')->default(true);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
            
            $table->unique(['staff_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_warehouse_staff_assignments');
    }
};
