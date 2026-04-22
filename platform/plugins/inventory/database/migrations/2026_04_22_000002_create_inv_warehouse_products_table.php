<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_warehouse_products')) {
            Schema::create('inv_warehouse_products', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variation_id')->nullable();
                $table->unsignedBigInteger('default_location_id')->nullable();
                $table->uuid('supplier_id')->nullable();
                $table->uuid('supplier_product_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('note', 255)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique([
                    'warehouse_id',
                    'product_id',
                    'product_variation_id',
                ], 'inv_wh_prod_unique');

                $table->index('warehouse_id', 'inv_wh_prod_warehouse_id_index');
                $table->index('product_id', 'inv_wh_prod_product_id_index');
                $table->index('product_variation_id', 'inv_wh_prod_product_variation_id_index');
                $table->index('default_location_id', 'inv_wh_prod_default_location_id_index');
                $table->index('supplier_id', 'inv_wh_prod_supplier_id_index');
                $table->index('supplier_product_id', 'inv_wh_prod_supplier_product_id_index');

                $table->foreign('warehouse_id', 'fk_inv_wh_prod_warehouse')
                    ->references('id')
                    ->on('inv_warehouses')
                    ->cascadeOnDelete();

                $table->foreign('product_id', 'fk_inv_wh_prod_product')
                    ->references('id')
                    ->on('ec_products')
                    ->cascadeOnDelete();

                $table->foreign('product_variation_id', 'fk_inv_wh_prod_variation')
                    ->references('id')
                    ->on('ec_product_variations')
                    ->nullOnDelete();

                $table->foreign('default_location_id', 'fk_inv_wh_prod_location')
                    ->references('id')
                    ->on('inv_warehouse_locations')
                    ->nullOnDelete();

                $table->foreign('supplier_id', 'fk_inv_wh_prod_supplier')
                    ->references('id')
                    ->on('inv_suppliers')
                    ->nullOnDelete();

                $table->foreign('supplier_product_id', 'fk_inv_wh_prod_supplier_product')
                    ->references('id')
                    ->on('inv_supplier_products')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_warehouse_products');
    }
};
