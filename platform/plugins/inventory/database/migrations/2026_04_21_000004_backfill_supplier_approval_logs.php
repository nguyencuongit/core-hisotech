<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SOURCE = '2026_04_21_000004_backfill_supplier_approval_logs';

    public function up(): void
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
                JSON_OBJECT('source', '" . self::SOURCE . "')
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
                JSON_OBJECT('source', '" . self::SOURCE . "')
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
                JSON_OBJECT('source', '" . self::SOURCE . "')
            FROM inv_suppliers s
            WHERE s.approved_at IS NOT NULL
              AND s.status IN ('active', 'rejected')
              AND NOT EXISTS (
                SELECT 1 FROM inv_supplier_approvals a
                WHERE a.supplier_id = s.id AND a.action IN ('approve', 'reject')
              )
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('inv_supplier_approvals')) {
            return;
        }

        DB::table('inv_supplier_approvals')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.source')) = ?", [self::SOURCE])
            ->delete();
    }
};
