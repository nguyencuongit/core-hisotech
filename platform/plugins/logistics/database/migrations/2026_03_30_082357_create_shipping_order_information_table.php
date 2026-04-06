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
        Schema::create('shipping_order_information', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipping_order_id')->constrained()->cascadeOnDelete();;
            
            // from
            $table->string('from_name');
            $table->string('from_phone');
            $table->string('from_address');
            $table->unsignedBigInteger('from_ward')->nullable();
            $table->unsignedBigInteger('from_district')->nullable();
            $table->unsignedBigInteger('from_province')->nullable();

            // To
            $table->string('to_name');
            $table->string('to_phone');
            $table->string('to_address');
            $table->unsignedBigInteger('to_ward')->nullable();
            $table->unsignedBigInteger('to_district')->nullable();
            $table->unsignedBigInteger('to_province')->nullable();

            // Shipment info
            $table->decimal('cod_amount', 15, 2)->default(0);
            $table->float('weight')->nullable();
            $table->float('length')->nullable();
            $table->float('width')->nullable();
            $table->float('height')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_order_information');
    }
};
