<?php

namespace Botble\Inventory\Domains\Transactions\Tables;

use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Enums\DocumentStatusEnum;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ExportTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Export::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.transactions-export.create')->permission('transactions-export.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.transactions-export.edit')->permission('transactions-export.create'),
                DeleteAction::make()->route('inventory.transactions-export.destroy')->permission('transactions-export.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('transactions-export.destroy'),
            ]);
    }

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query();

        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin()) {
            if (empty($warehouseIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('warehouse_id', $warehouseIds);
            }
        }

        $status = DocumentStatusEnum::tryFrom(strtolower((string) request('status')));

        if ($status) {
            $query->where('status', $status->value);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make('code')->title('Ma phieu')
                ->route('inventory.transactions-export.edit'),
            FormattedColumn::make('warehouse_id')
                ->title('Kho'),
            FormattedColumn::make('status')
                ->title(trans('core/base::tables.status'))
                ->alignCenter()
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderStatusBadge($column->getItem()->status)),
            CreatedAtColumn::make(),
        ];
    }

    protected function renderStatusBadge(DocumentStatusEnum|string|null $status): string
    {
        $enum = $status instanceof DocumentStatusEnum
            ? $status
            : DocumentStatusEnum::tryFrom(strtolower((string) $status));

        if (! $enum) {
            return '<span class="badge bg-secondary-lt text-secondary">-</span>';
        }

        $classes = [
            DocumentStatusEnum::DRAFT->value => 'bg-warning-lt text-warning',
            DocumentStatusEnum::CONFIRMED->value => 'bg-success-lt text-success',
            DocumentStatusEnum::CANCELLED->value => 'bg-danger-lt text-danger',
        ];

        return '<span class="badge ' . e($classes[$enum->value] ?? 'bg-secondary-lt text-secondary') . '">'
            . e($enum->label())
            . '</span>';
    }
}
