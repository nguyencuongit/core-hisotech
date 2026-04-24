<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_warehouse_map_items')) {
            Schema::create('inv_warehouse_map_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('warehouse_map_id');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->string('item_type', 50);
                $table->string('label')->nullable();
                $table->string('shape_type', 30)->default('rect');
                $table->decimal('x', 12, 2)->default(0);
                $table->decimal('y', 12, 2)->default(0);
                $table->decimal('width', 12, 2)->default(100);
                $table->decimal('height', 12, 2)->default(100);
                $table->decimal('rotation', 8, 2)->default(0);
                $table->string('color', 20)->nullable();
                $table->integer('z_index')->default(0);
                $table->boolean('is_clickable')->default(true);
                $table->json('meta_json')->nullable();
                $table->timestamps();

                $table->index('warehouse_map_id');
                $table->index('location_id');

                $table->foreign('warehouse_map_id')->references('id')->on('inv_warehouse_maps')->cascadeOnDelete();
                $table->foreign('location_id')->references('id')->on('inv_warehouse_locations')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_warehouse_map_items');
    }
};
