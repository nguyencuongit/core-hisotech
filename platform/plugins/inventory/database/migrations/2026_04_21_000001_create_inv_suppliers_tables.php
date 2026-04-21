<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_suppliers')) {
            Schema::create('inv_suppliers', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->string('type', 50);
                $table->string('tax_code', 50)->nullable();
                $table->string('website')->nullable();
                $table->text('note')->nullable();
                $table->string('status', 50)->default('draft');
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('submitted_by')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('approval_note')->nullable();
                $table->boolean('requires_reapproval')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('inv_supplier_approvals')) {
            Schema::create('inv_supplier_approvals', function (Blueprint $table): void {
                $table->id();
                $table->uuid('supplier_id');
                $table->string('action', 50);
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('acted_by')->nullable();
                $table->timestamp('acted_at')->nullable();
                $table->json('meta')->nullable();
                $table->foreign('supplier_id')->references('id')->on('inv_suppliers')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('inv_supplier_contacts')) {
            Schema::create('inv_supplier_contacts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('supplier_id');
                $table->boolean('is_primary')->default(false);
                $table->string('name');
                $table->string('position', 100)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email')->nullable();
                $table->string('identity_number', 50)->nullable();
                $table->json('social_contact')->nullable();
                $table->foreign('supplier_id')->references('id')->on('inv_suppliers')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('inv_supplier_addresses')) {
            Schema::create('inv_supplier_addresses', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('supplier_id');
                $table->string('type', 50);
                $table->boolean('is_default')->default(false);
                $table->string('address');
                $table->unsignedBigInteger('ward_id')->nullable();
                $table->unsignedBigInteger('district_id')->nullable();
                $table->unsignedBigInteger('province_id')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->foreign('supplier_id')->references('id')->on('inv_suppliers')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('inv_supplier_banks')) {
            Schema::create('inv_supplier_banks', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('supplier_id');
                $table->boolean('is_default')->default(false);
                $table->string('bank_name');
                $table->string('branch')->nullable();
                $table->string('account_number', 100);
                $table->string('account_name');
                $table->foreign('supplier_id')->references('id')->on('inv_suppliers')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('inv_supplier_products')) {
            Schema::create('inv_supplier_products', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('supplier_id');
                $table->unsignedBigInteger('product_id');
                $table->string('supplier_sku', 100)->nullable();
                $table->decimal('purchase_price', 15, 4)->nullable();
                $table->unsignedInteger('moq')->nullable();
                $table->unsignedInteger('lead_time_days')->nullable();
                $table->timestamps();
                $table->unique(['supplier_id', 'product_id']);
                $table->foreign('supplier_id')->references('id')->on('inv_suppliers')->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('ec_products')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_supplier_products');
        Schema::dropIfExists('inv_supplier_banks');
        Schema::dropIfExists('inv_supplier_addresses');
        Schema::dropIfExists('inv_supplier_contacts');
        Schema::dropIfExists('inv_supplier_approvals');
        Schema::dropIfExists('inv_suppliers');
    }
};
