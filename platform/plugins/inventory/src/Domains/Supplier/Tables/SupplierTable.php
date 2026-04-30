<?php

namespace Botble\Inventory\Domains\Supplier\Tables;

use Botble\Base\Facades\Html;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\RowActionsColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class SupplierTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->setView('plugins/inventory::suppliers.table')
            ->model(Supplier::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('inventory.suppliers.create')->permission('inventory.suppliers.create'))
            ->addActions([
                EditAction::make()->route('inventory.suppliers.edit')->permission('inventory.suppliers.edit'),
                DeleteAction::make()->route('inventory.suppliers.destroy')->permission('inventory.suppliers.delete'),
            ])
            ->addColumns([
                FormattedColumn::make('code')
                    ->title(trans('plugins/inventory::inventory.supplier.code'))
                    ->alignStart()
                    ->withEmptyState()
                    ->renderUsing(fn (FormattedColumn $column) => Html::tag('span', e((string) $column->getValue()), [
                        'class' => 'badge bg-light text-dark border font-monospace px-3 py-2',
                        'style' => 'letter-spacing:.02em;',
                    ]))
                    ->copyable(),
                NameColumn::make()
                    ->title(trans('plugins/inventory::inventory.supplier.name'))
                    ->route('inventory.suppliers.show'),
                FormattedColumn::make('type')
                    ->title(trans('plugins/inventory::inventory.supplier.type.label'))
                    ->alignStart()
                    ->withEmptyState()
                    ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->type?->label() ? Html::tag('span', e($column->getItem()->type?->label()), [
                        'class' => 'badge bg-info text-info-fg',
                    ]) : null),
                FormattedColumn::make('tax_code')
                    ->title(trans('plugins/inventory::inventory.supplier.tax_code'))
                    ->alignStart()
                    ->withEmptyState()
                    ->copyable(),
                FormattedColumn::make('primary_contact')
                    ->title(trans('plugins/inventory::inventory.supplier.primary_contact'))
                    ->alignStart()
                    ->orderable(false)
                    ->searchable(false)
                    ->withEmptyState()
                    ->getValueUsing(function (FormattedColumn $column) {
                        $contact = $column->getItem()->contacts->first();

                        if (! $contact) {
                            return null;
                        }

                        $meta = array_filter([$contact->position, $contact->phone, $contact->email]);
                        $title = Html::tag('div', e($contact->name), ['class' => 'fw-semibold'])->toHtml();
                        $subtitle = $meta ? Html::tag('div', e(implode(' - ', $meta)), ['class' => 'text-muted small'])->toHtml() : '';

                        return $title . $subtitle;
                    }),
                FormattedColumn::make('supplier_products_count')
                    ->title(trans('plugins/inventory::inventory.supplier.products'))
                    ->alignCenter()
                    ->searchable(false)
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->supplier_products_count ?: 0)
                    ->renderUsing(fn (FormattedColumn $column) => Html::tag('span', $column->getValue(), [
                        'class' => 'badge bg-blue text-blue-fg rounded-pill px-3 py-2 shadow-sm',
                    ])),
                FormattedColumn::make('status')
                    ->title(trans('plugins/inventory::inventory.supplier.status.label'))
                    ->alignCenter()
                    ->withEmptyState()
                    ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->status?->toHtml()),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.suppliers.delete'),
            ])
            ->queryUsing(function (Builder $query): void {
                $query->select(['id', 'code', 'name', 'type', 'tax_code', 'status', 'created_at'])
                    ->withCount('supplierProducts')
                    ->with(['contacts' => fn ($query) => $query->where('is_primary', true)]);
            });
    }

    protected function getRowActionsHeading(): array
    {
        return [
            RowActionsColumn::make()
                ->title(trans('core/base::tables.operations'))
                ->alignCenter()
                ->nowrap()
                ->responsivePriority(1),
        ];
    }
}
