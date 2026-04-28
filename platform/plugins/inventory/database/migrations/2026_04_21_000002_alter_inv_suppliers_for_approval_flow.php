<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inv_suppliers')) {
            Schema::table('inv_suppliers', function (Blueprint $table): void {
                if (! Schema::hasColumn('inv_suppliers', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
                }

                if (! Schema::hasColumn('inv_suppliers', 'submitted_by')) {
                    $table->unsignedBigInteger('submitted_by')->nullable()->after('metadata');
                }

                if (! Schema::hasColumn('inv_suppliers', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('submitted_by');
                }

                if (! Schema::hasColumn('inv_suppliers', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('submitted_at');
                }

                if (! Schema::hasColumn('inv_suppliers', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }

                if (! Schema::hasColumn('inv_suppliers', 'approval_note')) {
                    $table->text('approval_note')->nullable()->after('approved_at');
                }

                if (! Schema::hasColumn('inv_suppliers', 'requires_reapproval')) {
                    $table->boolean('requires_reapproval')->default(false)->after('approval_note');
                }
            });
        }

        if (Schema::hasTable('inv_suppliers') && ! Schema::hasTable('inv_supplier_approvals')) {
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

                $table->index('supplier_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inv_supplier_approvals')) {
            Schema::drop('inv_supplier_approvals');
        }

        if (Schema::hasTable('inv_suppliers')) {
            Schema::table('inv_suppliers', function (Blueprint $table): void {
                foreach (['created_by', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'approval_note', 'requires_reapproval'] as $column) {
                    if (Schema::hasColumn('inv_suppliers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
