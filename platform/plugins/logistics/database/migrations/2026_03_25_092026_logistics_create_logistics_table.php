<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('logistics')) {
            Schema::create('logistics', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('logistics_translations')) {
            Schema::create('logistics_translations', function (Blueprint $table) {
                $table->string('lang_code');
                $table->foreignId('logistics_id');
                $table->string('name', 255)->nullable();

                $table->primary(['lang_code', 'logistics_id'], 'logistics_translations_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics');
        Schema::dropIfExists('logistics_translations');
    }
};
