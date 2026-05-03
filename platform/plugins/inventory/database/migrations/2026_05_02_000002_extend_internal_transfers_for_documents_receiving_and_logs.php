<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inv_internal_transfers')) {
            Schema::table('inv_internal_transfers', function (Blueprint $table): void {
                if (! Schema::hasColumn('inv_internal_transfers', 'export_id')) {
                    $table->unsignedBigInteger('export_id')->nullable()->after('to_warehouse_id')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfers', 'import_id')) {
                    $table->unsignedBigInteger('import_id')->nullable()->after('export_id')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfers', 'in_transit_at')) {
                    $table->timestamp('in_transit_at')->nullable()->after('transfer_date')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfers', 'received_at')) {
                    $table->timestamp('received_at')->nullable()->after('in_transit_at')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfers', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('received_at')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfers', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('completed_at')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfers', 'cancelled_by')) {
                    $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfers', 'cancelled_reason')) {
                    $table->string('cancelled_reason', 255)->nullable()->after('cancelled_by');
                }
            });
        }

        if (Schema::hasTable('inv_internal_transfer_items')) {
            Schema::table('inv_internal_transfer_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('inv_internal_transfer_items', 'export_item_id')) {
                    $table->unsignedBigInteger('export_item_id')->nullable()->after('transfer_id')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfer_items', 'import_item_id')) {
                    $table->unsignedBigInteger('import_item_id')->nullable()->after('export_item_id')->index();
                }

                if (! Schema::hasColumn('inv_internal_transfer_items', 'exported_qty')) {
                    $table->decimal('exported_qty', 15, 4)->default(0)->after('requested_qty');
                }

                if (! Schema::hasColumn('inv_internal_transfer_items', 'received_qty')) {
                    $table->decimal('received_qty', 15, 4)->default(0)->after('exported_qty');
                }

                if (! Schema::hasColumn('inv_internal_transfer_items', 'damaged_qty')) {
                    $table->decimal('damaged_qty', 15, 4)->default(0)->after('received_qty');
                }

                if (! Schema::hasColumn('inv_internal_transfer_items', 'shortage_qty')) {
                    $table->decimal('shortage_qty', 15, 4)->default(0)->after('damaged_qty');
                }

                if (! Schema::hasColumn('inv_internal_transfer_items', 'overage_qty')) {
                    $table->decimal('overage_qty', 15, 4)->default(0)->after('shortage_qty');
                }

                if (! Schema::hasColumn('inv_internal_transfer_items', 'to_pallet_id')) {
                    $table->unsignedBigInteger('to_pallet_id')->nullable()->after('pallet_id')->index();
                }
            });
        }

        if (! Schema::hasTable('inv_internal_transfer_logs')) {
            Schema::create('inv_internal_transfer_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('transfer_id')->index();
                $table->string('action', 50)->index();
                $table->string('old_status', 50)->nullable();
                $table->string('new_status', 50)->nullable()->index();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamp('created_at')->nullable();

                $table->index(['transfer_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_internal_transfer_logs');

        if (Schema::hasTable('inv_internal_transfer_items')) {
            Schema::table('inv_internal_transfer_items', function (Blueprint $table): void {
                foreach ([
                    'export_item_id',
                    'import_item_id',
                    'exported_qty',
                    'received_qty',
                    'damaged_qty',
                    'shortage_qty',
                    'overage_qty',
                    'to_pallet_id',
                ] as $column) {
                    if (Schema::hasColumn('inv_internal_transfer_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('inv_internal_transfers')) {
            Schema::table('inv_internal_transfers', function (Blueprint $table): void {
                foreach ([
                    'export_id',
                    'import_id',
                    'in_transit_at',
                    'received_at',
                    'completed_at',
                    'cancelled_at',
                    'cancelled_by',
                    'cancelled_reason',
                ] as $column) {
                    if (Schema::hasColumn('inv_internal_transfers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
