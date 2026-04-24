<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_pallet_movements')) {
            Schema::create('inv_pallet_movements', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('pallet_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('from_location_id')->nullable();
                $table->unsignedBigInteger('to_location_id')->nullable();
                $table->enum('movement_type', ['create', 'receive', 'putaway', 'internal_move', 'pick', 'transfer', 'return', 'adjustment']);
                $table->string('reference_type', 100)->nullable();
                $table->string('reference_id', 100)->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('pallet_id', 'inv_pallet_movements_pallet_id_index');
                $table->index('warehouse_id', 'inv_pallet_movements_warehouse_id_index');
                $table->index('from_location_id', 'inv_pallet_movements_from_location_id_index');
                $table->index('to_location_id', 'inv_pallet_movements_to_location_id_index');

                $table->foreign('pallet_id', 'fk_inv_pallet_movements_pallet')
                    ->references('id')
                    ->on('inv_pallets')
                    ->cascadeOnDelete();

                $table->foreign('warehouse_id', 'fk_inv_pallet_movements_warehouse')
                    ->references('id')
                    ->on('inv_warehouses')
                    ->cascadeOnDelete();

                $table->foreign('from_location_id', 'fk_inv_pallet_movements_from_location')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();

                $table->foreign('to_location_id', 'fk_inv_pallet_movements_to_location')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_pallet_movements');
    }
};
