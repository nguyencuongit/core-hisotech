<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_warehouse_maps')) {
            Schema::create('inv_warehouse_maps', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->string('name');
                $table->string('map_type', 50)->default('floor_plan');
                $table->string('background_image')->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->decimal('scale_ratio', 10, 4)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('warehouse_id');
                $table->index('map_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_warehouse_maps');
    }
};
