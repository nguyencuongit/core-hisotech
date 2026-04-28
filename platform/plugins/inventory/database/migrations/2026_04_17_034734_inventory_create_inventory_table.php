<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inventories_translations')) {
            Schema::create('inventories_translations', function (Blueprint $table) {
                $table->string('lang_code');
                $table->unsignedBigInteger('inventories_id');
                $table->string('name', 255)->nullable();

                $table->primary(['lang_code', 'inventories_id'], 'inventories_translations_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('inventories_translations');
    }
};
