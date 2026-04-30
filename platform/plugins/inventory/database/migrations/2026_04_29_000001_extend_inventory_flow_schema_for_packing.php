<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alterImports();
        $this->alterSupplierProducts();
        $this->alterWarehouseProducts();
        $this->alterImportItems();
        $this->alterExportItems();
        $this->alterPackingLists();
        $this->alterPackages();
        $this->alterPackingListItems();
        $this->alterStockBalances();
        $this->alterStockTransactions();
        $this->createPackingLogs();
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_packing_logs');

        $this->dropColumns('inv_stock_transactions', [
            'stock_balance_id',
            'reserved_delta',
            'available_delta',
        ]);

        $this->dropColumns('inv_stock_balances', [
            'dimension_key',
            'last_movement_at',
        ]);

        $this->dropColumns('inv_packing_list_items', [
            'package_id',
            'export_item_id',
            'product_variation_id',
            'product_code',
            'pallet_id',
            'batch_id',
            'goods_receipt_batch_id',
            'stock_balance_id',
            'storage_item_id',
            'lot_no',
            'expiry_date',
        ]);

        $this->dropColumns('inv_packages', [
            'package_no',
            'status',
            'dimension_unit',
            'volume',
            'volume_weight',
            'sealed_by',
            'sealed_at',
            'tracking_code',
            'shipping_label_url',
        ]);

        $this->dropColumns('inv_packing_lists', [
            'started_at',
            'completed_at',
            'cancelled_at',
            'cancelled_by',
            'cancelled_reason',
            'total_items',
            'total_volume',
        ]);

        $this->dropColumns('inv_export_items', [
            'product_variation_id',
            'reserved_qty',
            'picked_qty',
            'packed_qty',
            'cancelled_qty',
            'pallet_id',
            'batch_id',
            'goods_receipt_batch_id',
            'stock_balance_id',
        ]);

        $this->dropColumns('inv_import_items', [
            'product_variation_id',
            'supplier_product_id',
            'pallet_id',
            'batch_id',
            'goods_receipt_batch_id',
            'unit_cost',
            'total_cost',
            'qc_status',
            'accepted_qty',
            'rejected_qty',
        ]);

        $this->dropColumns('inv_warehouse_products', [
            'default_location_id',
            'default_unit_id',
            'min_stock_qty',
            'max_stock_qty',
            'reorder_point_qty',
            'reorder_qty',
            'is_batch_required',
            'is_serial_required',
            'is_pallet_required',
            'allow_negative_stock',
        ]);

        $this->dropColumns('inv_supplier_products', [
            'product_variation_id',
            'unit_id',
            'currency',
            'last_purchase_price',
            'tax_rate',
            'is_default',
            'is_active',
            'note',
        ]);

        $this->dropColumns('inv_imports', [
            'supplier_id',
        ]);
    }

    private function alterImports(): void
    {
        if (! Schema::hasTable('inv_imports')) {
            return;
        }

        Schema::table('inv_imports', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_imports', 'supplier_id')) {
                $table->char('supplier_id', 36)->nullable()->after('partner_id');
            }
        });

        $this->addIndexIfMissing('inv_imports', 'inv_imports_supplier_id_idx', ['supplier_id']);
    }

    private function alterSupplierProducts(): void
    {
        if (! Schema::hasTable('inv_supplier_products')) {
            return;
        }

        Schema::table('inv_supplier_products', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_supplier_products', 'product_variation_id')) {
                $table->unsignedBigInteger('product_variation_id')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('inv_supplier_products', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('product_variation_id');
            }

            if (! Schema::hasColumn('inv_supplier_products', 'currency')) {
                $table->string('currency', 10)->default('VND')->after('purchase_price');
            }

            if (! Schema::hasColumn('inv_supplier_products', 'last_purchase_price')) {
                $table->decimal('last_purchase_price', 15, 4)->nullable()->after('currency');
            }

            if (! Schema::hasColumn('inv_supplier_products', 'tax_rate')) {
                $table->decimal('tax_rate', 8, 4)->nullable()->after('last_purchase_price');
            }

            if (! Schema::hasColumn('inv_supplier_products', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('lead_time_days');
            }

            if (! Schema::hasColumn('inv_supplier_products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_default');
            }

            if (! Schema::hasColumn('inv_supplier_products', 'note')) {
                $table->string('note', 255)->nullable()->after('is_active');
            }
        });

        $this->addIndexIfMissing('inv_supplier_products', 'inv_sp_variation_id_idx', ['product_variation_id']);
        $this->addIndexIfMissing('inv_supplier_products', 'inv_sp_unit_id_idx', ['unit_id']);
        $this->addIndexIfMissing('inv_supplier_products', 'inv_sp_is_default_idx', ['is_default']);
        $this->addIndexIfMissing('inv_supplier_products', 'inv_sp_is_active_idx', ['is_active']);
    }

    private function alterWarehouseProducts(): void
    {
        if (! Schema::hasTable('inv_warehouse_products')) {
            return;
        }

        Schema::table('inv_warehouse_products', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_warehouse_products', 'default_location_id')) {
                $table->unsignedBigInteger('default_location_id')->nullable()->after('supplier_product_id');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'default_unit_id')) {
                $table->unsignedBigInteger('default_unit_id')->nullable()->after('default_location_id');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'min_stock_qty')) {
                $table->decimal('min_stock_qty', 15, 4)->default(0)->after('default_unit_id');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'max_stock_qty')) {
                $table->decimal('max_stock_qty', 15, 4)->default(0)->after('min_stock_qty');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'reorder_point_qty')) {
                $table->decimal('reorder_point_qty', 15, 4)->default(0)->after('max_stock_qty');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'reorder_qty')) {
                $table->decimal('reorder_qty', 15, 4)->default(0)->after('reorder_point_qty');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'is_batch_required')) {
                $table->boolean('is_batch_required')->default(false)->after('reorder_qty');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'is_serial_required')) {
                $table->boolean('is_serial_required')->default(false)->after('is_batch_required');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'is_pallet_required')) {
                $table->boolean('is_pallet_required')->default(false)->after('is_serial_required');
            }

            if (! Schema::hasColumn('inv_warehouse_products', 'allow_negative_stock')) {
                $table->boolean('allow_negative_stock')->default(false)->after('is_pallet_required');
            }
        });

        $this->addIndexIfMissing('inv_warehouse_products', 'inv_wh_prod_default_location_idx', ['default_location_id']);
        $this->addIndexIfMissing('inv_warehouse_products', 'inv_wh_prod_default_unit_idx', ['default_unit_id']);
    }

    private function alterImportItems(): void
    {
        if (! Schema::hasTable('inv_import_items')) {
            return;
        }

        Schema::table('inv_import_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_import_items', 'product_variation_id')) {
                $table->unsignedBigInteger('product_variation_id')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('inv_import_items', 'supplier_product_id')) {
                $table->char('supplier_product_id', 36)->nullable()->after('product_variation_id');
            }

            if (! Schema::hasColumn('inv_import_items', 'pallet_id')) {
                $table->unsignedBigInteger('pallet_id')->nullable()->after('warehouse_location_id');
            }

            if (! Schema::hasColumn('inv_import_items', 'batch_id')) {
                $table->char('batch_id', 36)->nullable()->after('pallet_id');
            }

            if (! Schema::hasColumn('inv_import_items', 'goods_receipt_batch_id')) {
                $table->char('goods_receipt_batch_id', 36)->nullable()->after('batch_id');
            }

            if (! Schema::hasColumn('inv_import_items', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 4)->default(0)->after('amount');
            }

            if (! Schema::hasColumn('inv_import_items', 'total_cost')) {
                $table->decimal('total_cost', 15, 4)->default(0)->after('unit_cost');
            }

            if (! Schema::hasColumn('inv_import_items', 'qc_status')) {
                $table->string('qc_status', 50)->nullable()->after('total_cost');
            }

            if (! Schema::hasColumn('inv_import_items', 'accepted_qty')) {
                $table->decimal('accepted_qty', 15, 4)->default(0)->after('qc_status');
            }

            if (! Schema::hasColumn('inv_import_items', 'rejected_qty')) {
                $table->decimal('rejected_qty', 15, 4)->default(0)->after('accepted_qty');
            }
        });

        $this->addIndexIfMissing('inv_import_items', 'inv_import_items_variation_idx', ['product_variation_id']);
        $this->addIndexIfMissing('inv_import_items', 'inv_import_items_supplier_product_idx', ['supplier_product_id']);
        $this->addIndexIfMissing('inv_import_items', 'inv_import_items_pallet_idx', ['pallet_id']);
        $this->addIndexIfMissing('inv_import_items', 'inv_import_items_batch_idx', ['batch_id']);
        $this->addIndexIfMissing('inv_import_items', 'inv_import_items_gr_batch_idx', ['goods_receipt_batch_id']);
        $this->addIndexIfMissing('inv_import_items', 'inv_import_items_qc_status_idx', ['qc_status']);
    }

    private function alterExportItems(): void
    {
        if (! Schema::hasTable('inv_export_items')) {
            return;
        }

        Schema::table('inv_export_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_export_items', 'product_variation_id')) {
                $table->unsignedBigInteger('product_variation_id')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('inv_export_items', 'reserved_qty')) {
                $table->decimal('reserved_qty', 15, 4)->default(0)->after('document_qty');
            }

            if (! Schema::hasColumn('inv_export_items', 'picked_qty')) {
                $table->decimal('picked_qty', 15, 4)->default(0)->after('reserved_qty');
            }

            if (! Schema::hasColumn('inv_export_items', 'packed_qty')) {
                $table->decimal('packed_qty', 15, 4)->default(0)->after('picked_qty');
            }

            if (! Schema::hasColumn('inv_export_items', 'cancelled_qty')) {
                $table->decimal('cancelled_qty', 15, 4)->default(0)->after('shipped_qty');
            }

            if (! Schema::hasColumn('inv_export_items', 'pallet_id')) {
                $table->unsignedBigInteger('pallet_id')->nullable()->after('warehouse_location_id');
            }

            if (! Schema::hasColumn('inv_export_items', 'batch_id')) {
                $table->char('batch_id', 36)->nullable()->after('pallet_id');
            }

            if (! Schema::hasColumn('inv_export_items', 'goods_receipt_batch_id')) {
                $table->char('goods_receipt_batch_id', 36)->nullable()->after('batch_id');
            }

            if (! Schema::hasColumn('inv_export_items', 'stock_balance_id')) {
                $table->char('stock_balance_id', 36)->nullable()->after('goods_receipt_batch_id');
            }
        });

        $this->addIndexIfMissing('inv_export_items', 'inv_export_items_variation_idx', ['product_variation_id']);
        $this->addIndexIfMissing('inv_export_items', 'inv_export_items_pallet_idx', ['pallet_id']);
        $this->addIndexIfMissing('inv_export_items', 'inv_export_items_batch_idx', ['batch_id']);
        $this->addIndexIfMissing('inv_export_items', 'inv_export_items_gr_batch_idx', ['goods_receipt_batch_id']);
        $this->addIndexIfMissing('inv_export_items', 'inv_export_items_stock_balance_idx', ['stock_balance_id']);
    }

    private function alterPackingLists(): void
    {
        if (! Schema::hasTable('inv_packing_lists')) {
            return;
        }

        Schema::table('inv_packing_lists', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_packing_lists', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('packed_at');
            }

            if (! Schema::hasColumn('inv_packing_lists', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }

            if (! Schema::hasColumn('inv_packing_lists', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }

            if (! Schema::hasColumn('inv_packing_lists', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            }

            if (! Schema::hasColumn('inv_packing_lists', 'cancelled_reason')) {
                $table->string('cancelled_reason', 255)->nullable()->after('cancelled_by');
            }

            if (! Schema::hasColumn('inv_packing_lists', 'total_items')) {
                $table->decimal('total_items', 15, 4)->default(0)->after('total_packages');
            }

            if (! Schema::hasColumn('inv_packing_lists', 'total_volume')) {
                $table->decimal('total_volume', 15, 4)->default(0)->after('total_weight');
            }
        });

        $this->addIndexIfMissing('inv_packing_lists', 'inv_packing_lists_started_at_idx', ['started_at']);
        $this->addIndexIfMissing('inv_packing_lists', 'inv_packing_lists_completed_at_idx', ['completed_at']);
        $this->addIndexIfMissing('inv_packing_lists', 'inv_packing_lists_cancelled_at_idx', ['cancelled_at']);
        $this->addIndexIfMissing('inv_packing_lists', 'inv_packing_lists_cancelled_by_idx', ['cancelled_by']);
    }

    private function alterPackages(): void
    {
        if (! Schema::hasTable('inv_packages')) {
            return;
        }

        Schema::table('inv_packages', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_packages', 'package_no')) {
                $table->unsignedInteger('package_no')->default(1)->after('package_code');
            }

            if (! Schema::hasColumn('inv_packages', 'status')) {
                $table->string('status', 50)->default('open')->after('package_type_id');
            }

            if (! Schema::hasColumn('inv_packages', 'dimension_unit')) {
                $table->string('dimension_unit', 20)->default('cm')->after('height');
            }

            if (! Schema::hasColumn('inv_packages', 'volume')) {
                $table->decimal('volume', 15, 4)->default(0)->after('dimension_unit');
            }

            if (! Schema::hasColumn('inv_packages', 'volume_weight')) {
                $table->decimal('volume_weight', 15, 4)->default(0)->after('volume');
            }

            if (! Schema::hasColumn('inv_packages', 'sealed_by')) {
                $table->unsignedBigInteger('sealed_by')->nullable()->after('volume_weight');
            }

            if (! Schema::hasColumn('inv_packages', 'sealed_at')) {
                $table->timestamp('sealed_at')->nullable()->after('sealed_by');
            }

            if (! Schema::hasColumn('inv_packages', 'tracking_code')) {
                $table->string('tracking_code', 191)->nullable()->after('sealed_at');
            }

            if (! Schema::hasColumn('inv_packages', 'shipping_label_url')) {
                $table->string('shipping_label_url', 500)->nullable()->after('tracking_code');
            }
        });

        $this->addIndexIfMissing('inv_packages', 'inv_packages_package_no_idx', ['package_no']);
        $this->addIndexIfMissing('inv_packages', 'inv_packages_status_idx', ['status']);
        $this->addIndexIfMissing('inv_packages', 'inv_packages_sealed_by_idx', ['sealed_by']);
        $this->addIndexIfMissing('inv_packages', 'inv_packages_tracking_code_idx', ['tracking_code']);
    }

    private function alterPackingListItems(): void
    {
        if (! Schema::hasTable('inv_packing_list_items')) {
            return;
        }

        Schema::table('inv_packing_list_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_packing_list_items', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('packing_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'export_item_id')) {
                $table->unsignedBigInteger('export_item_id')->nullable()->after('package_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'product_variation_id')) {
                $table->unsignedBigInteger('product_variation_id')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'product_code')) {
                $table->string('product_code', 191)->nullable()->after('product_variation_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'product_name')) {
                $table->string('product_name', 191)->nullable()->after('product_code');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'packed_qty')) {
                $table->decimal('packed_qty', 15, 4)->default(0)->after('product_name');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('packed_qty');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'unit_name')) {
                $table->string('unit_name', 191)->nullable()->after('unit_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'warehouse_location_id')) {
                $table->unsignedBigInteger('warehouse_location_id')->nullable()->after('unit_name');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'pallet_id')) {
                $table->unsignedBigInteger('pallet_id')->nullable()->after('warehouse_location_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'batch_id')) {
                $table->char('batch_id', 36)->nullable()->after('pallet_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'goods_receipt_batch_id')) {
                $table->char('goods_receipt_batch_id', 36)->nullable()->after('batch_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'stock_balance_id')) {
                $table->char('stock_balance_id', 36)->nullable()->after('goods_receipt_batch_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'storage_item_id')) {
                $table->char('storage_item_id', 36)->nullable()->after('stock_balance_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'lot_no')) {
                $table->string('lot_no', 191)->nullable()->after('storage_item_id');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('lot_no');
            }

            if (! Schema::hasColumn('inv_packing_list_items', 'note')) {
                $table->text('note')->nullable()->after('expiry_date');
            }
        });

        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_package_id_idx', ['package_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_export_item_id_idx', ['export_item_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_product_id_idx', ['product_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_variation_id_idx', ['product_variation_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_product_code_idx', ['product_code']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_unit_id_idx', ['unit_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_location_id_idx', ['warehouse_location_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_pallet_id_idx', ['pallet_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_batch_id_idx', ['batch_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_gr_batch_id_idx', ['goods_receipt_batch_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_stock_balance_id_idx', ['stock_balance_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_storage_item_id_idx', ['storage_item_id']);
        $this->addIndexIfMissing('inv_packing_list_items', 'inv_pli_lot_no_idx', ['lot_no']);
    }

    private function alterStockBalances(): void
    {
        if (! Schema::hasTable('inv_stock_balances')) {
            return;
        }

        Schema::table('inv_stock_balances', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_stock_balances', 'dimension_key')) {
                $table->string('dimension_key', 191)->nullable()->after('id');
            }

            if (! Schema::hasColumn('inv_stock_balances', 'last_movement_at')) {
                $table->timestamp('last_movement_at')->nullable()->after('last_unit_cost');
            }
        });

        $this->addUniqueIfMissing('inv_stock_balances', 'inv_stock_balances_dimension_key_unique', ['dimension_key']);
        $this->addIndexIfMissing('inv_stock_balances', 'inv_stock_balances_last_movement_at_idx', ['last_movement_at']);
    }

    private function alterStockTransactions(): void
    {
        if (! Schema::hasTable('inv_stock_transactions')) {
            return;
        }

        Schema::table('inv_stock_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('inv_stock_transactions', 'stock_balance_id')) {
                $table->char('stock_balance_id', 36)->nullable()->after('id');
            }

            if (! Schema::hasColumn('inv_stock_transactions', 'reserved_delta')) {
                $table->decimal('reserved_delta', 15, 4)->default(0)->after('quantity');
            }

            if (! Schema::hasColumn('inv_stock_transactions', 'available_delta')) {
                $table->decimal('available_delta', 15, 4)->default(0)->after('reserved_delta');
            }
        });

        $this->addIndexIfMissing('inv_stock_transactions', 'inv_stock_tx_stock_balance_idx', ['stock_balance_id']);
    }

    private function createPackingLogs(): void
    {
        if (Schema::hasTable('inv_packing_logs')) {
            return;
        }

        Schema::create('inv_packing_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('packing_list_id');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('export_id')->nullable();
            $table->unsignedBigInteger('export_item_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_variation_id')->nullable();
            $table->string('action', 50);
            $table->decimal('old_qty', 15, 4)->nullable();
            $table->decimal('new_qty', 15, 4)->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('packing_list_id', 'inv_packing_logs_packing_list_idx');
            $table->index('package_id', 'inv_packing_logs_package_idx');
            $table->index('export_id', 'inv_packing_logs_export_idx');
            $table->index('export_item_id', 'inv_packing_logs_export_item_idx');
            $table->index('product_id', 'inv_packing_logs_product_idx');
            $table->index('product_variation_id', 'inv_packing_logs_variation_idx');
            $table->index('action', 'inv_packing_logs_action_idx');
            $table->index('created_by', 'inv_packing_logs_created_by_idx');
            $table->index('created_at', 'inv_packing_logs_created_at_idx');
        });
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $index) || ! $this->columnsExist($table, $columns)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE %s ADD INDEX %s (%s)',
            $this->wrap($table),
            $this->wrap($index),
            $this->wrapColumns($columns)
        ));
    }

    private function addUniqueIfMissing(string $table, string $index, array $columns): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $index) || ! $this->columnsExist($table, $columns)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE %s ADD UNIQUE %s (%s)',
            $this->wrap($table),
            $this->wrap($index),
            $this->wrapColumns($columns)
        ));
    }

    private function dropColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }

    private function columnsExist(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $table, string $index): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $index]
        );
    }

    private function wrapColumns(array $columns): string
    {
        return implode(', ', array_map(fn (string $column): string => $this->wrap($column), $columns));
    }

    private function wrap(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }
};
