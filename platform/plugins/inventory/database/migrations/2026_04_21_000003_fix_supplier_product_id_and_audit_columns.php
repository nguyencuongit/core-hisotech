<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inv_suppliers') && ! Schema::hasColumn('inv_suppliers', 'created_by')) {
            Schema::table('inv_suppliers', function (Blueprint $table): void {
                $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
            });
        }

        if (Schema::hasTable('inv_suppliers') && Schema::hasColumn('inv_suppliers', 'created_by')) {
            DB::table('inv_suppliers')
                ->whereNull('created_by')
                ->whereNotNull('submitted_by')
                ->update(['created_by' => DB::raw('submitted_by')]);
        }

        if (! Schema::hasTable('inv_supplier_products')) {
            return;
        }

        if (Schema::getColumnType('inv_supplier_products', 'product_id') !== 'bigint') {
            $this->addIndexIfMissing('inv_supplier_products', 'inv_supplier_products_supplier_id_index', '
                ALTER TABLE inv_supplier_products
                ADD INDEX inv_supplier_products_supplier_id_index (supplier_id)
            ');

            $this->dropIndexIfExists('inv_supplier_products', 'inv_supplier_products_supplier_id_product_id_unique');

            DB::statement("
                DELETE FROM inv_supplier_products
                WHERE product_id NOT REGEXP '^[0-9]+$'
            ");

            DB::statement('
                DELETE isp FROM inv_supplier_products isp
                LEFT JOIN ec_products ep ON ep.id = CAST(isp.product_id AS UNSIGNED)
                WHERE ep.id IS NULL
            ');

            DB::statement('
                ALTER TABLE inv_supplier_products
                MODIFY product_id BIGINT UNSIGNED NOT NULL
            ');
        }

        $this->addIndexIfMissing('inv_supplier_products', 'inv_supplier_products_supplier_id_product_id_unique', '
            ALTER TABLE inv_supplier_products
            ADD UNIQUE inv_supplier_products_supplier_id_product_id_unique (supplier_id, product_id)
        ');

        $this->backfillSupplierApprovalLogs();
    }

    public function down(): void
    {
        if (Schema::hasTable('inv_supplier_products')) {
            $this->dropIndexIfExists('inv_supplier_products', 'inv_supplier_products_supplier_id_product_id_unique');

            if (Schema::getColumnType('inv_supplier_products', 'product_id') !== 'char') {
                DB::statement('
                    ALTER TABLE inv_supplier_products
                    MODIFY product_id CHAR(36) NOT NULL
                ');
            }

            $this->addIndexIfMissing('inv_supplier_products', 'inv_supplier_products_supplier_id_product_id_unique', '
                ALTER TABLE inv_supplier_products
                ADD UNIQUE inv_supplier_products_supplier_id_product_id_unique (supplier_id, product_id)
            ');
        }

        if (Schema::hasTable('inv_suppliers') && Schema::hasColumn('inv_suppliers', 'created_by')) {
            Schema::table('inv_suppliers', function (Blueprint $table): void {
                $table->dropColumn('created_by');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $index]
        );
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            DB::statement(sprintf('DROP INDEX %s ON %s', $index, $table));
        }
    }

    private function addIndexIfMissing(string $table, string $index, string $statement): void
    {
        if (! $this->indexExists($table, $index)) {
            DB::statement($statement);
        }
    }

    private function backfillSupplierApprovalLogs(): void
    {
        if (! Schema::hasTable('inv_suppliers') || ! Schema::hasTable('inv_supplier_approvals')) {
            return;
        }

        DB::statement("
            INSERT INTO inv_supplier_approvals (
                supplier_id, action, from_status, to_status, note, acted_by, acted_at, meta
            )
            SELECT
                s.id,
                'create',
                NULL,
                s.status,
                NULL,
                s.created_by,
                s.created_at,
                JSON_OBJECT()
            FROM inv_suppliers s
            WHERE NOT EXISTS (
                SELECT 1 FROM inv_supplier_approvals a
                WHERE a.supplier_id = s.id AND a.action = 'create'
            )
        ");

        DB::statement("
            INSERT INTO inv_supplier_approvals (
                supplier_id, action, from_status, to_status, note, acted_by, acted_at, meta
            )
            SELECT
                s.id,
                'submit',
                'draft',
                'pending_approval',
                s.approval_note,
                s.submitted_by,
                s.submitted_at,
                JSON_OBJECT()
            FROM inv_suppliers s
            WHERE s.submitted_at IS NOT NULL
              AND NOT EXISTS (
                SELECT 1 FROM inv_supplier_approvals a
                WHERE a.supplier_id = s.id AND a.action = 'submit'
              )
        ");

        DB::statement("
            INSERT INTO inv_supplier_approvals (
                supplier_id, action, from_status, to_status, note, acted_by, acted_at, meta
            )
            SELECT
                s.id,
                CASE WHEN s.status = 'rejected' THEN 'reject' ELSE 'approve' END,
                'pending_approval',
                s.status,
                s.approval_note,
                s.approved_by,
                s.approved_at,
                JSON_OBJECT()
            FROM inv_suppliers s
            WHERE s.approved_at IS NOT NULL
              AND s.status IN ('active', 'rejected')
              AND NOT EXISTS (
                SELECT 1 FROM inv_supplier_approvals a
                WHERE a.supplier_id = s.id AND a.action IN ('approve', 'reject')
              )
        ");
    }
};
