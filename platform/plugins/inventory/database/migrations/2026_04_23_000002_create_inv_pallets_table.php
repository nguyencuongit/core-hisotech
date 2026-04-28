<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_pallets')) {
            Schema::create('inv_pallets', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 50)->unique();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('current_location_id')->nullable();
                $table->string('type', 50)->nullable();
                $table->enum('status', ['empty', 'open', 'in_use', 'closed', 'damaged', 'locked'])->default('empty');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('warehouse_id', 'inv_pallets_warehouse_id_index');
                $table->index('current_location_id', 'inv_pallets_current_location_id_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_pallets');
    }
};
