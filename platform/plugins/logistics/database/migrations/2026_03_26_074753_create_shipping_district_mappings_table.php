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
        Schema::create('shipping_district_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('district_id', 50);

            $table->timestamps();

            $table->unique(['city_id', 'provider']);

            $table->index('provider');
            $table->index('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_district_mappings');
    }
};
