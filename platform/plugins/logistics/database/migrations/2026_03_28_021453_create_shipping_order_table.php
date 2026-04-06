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
        Schema::create('shipping_orders', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('order_id');     
            $table->string('provider');
            $table->string('status')->nullable();       
            $table->string('code')->nullable();  
            $table->decimal('total_fee', 12, 2);
            $table->text('error')->nullable();          

            $table->timestamps();

            
            $table->foreign('order_id')->references('id')->on('ec_orders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_order');
    }
};
