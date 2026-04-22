<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Tables;

use Botble\Base\Facades\Html;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceipt;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class GoodsReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(GoodsReceipt::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('inventory.goods-receipts.create')->permission('inventory.goods-receipts.create'))
            ->addActions([
                EditAction::make('show')
                    ->label(trans('plugins/inventory::inventory.goods_receipt.show'))
                    ->icon('ti ti-eye')
                    ->route('inventory.goods-receipts.show')
                    ->permission('inventory.goods-receipts.show'),
                EditAction::make()->route('inventory.goods-receipts.edit')->permission('inventory.goods-receipts.edit'),
                DeleteAction::make()->route('inventory.goods-receipts.destroy')->permission('inventory.goods-receipts.delete'),
            ])
            ->addColumns([
                FormattedColumn::make('code')
                    ->title(trans('plugins/inventory::inventory.goods_receipt.code'))
                    ->alignStart()
                    ->renderUsing(fn (FormattedColumn $column) => Html::tag('span', e((string) $column->getValue()), [
                        'class' => 'badge bg-light text-dark border font-monospace px-3 py-2',
                    ]))
                    ->copyable(),
                FormattedColumn::make('supplier')
                    ->title(trans('plugins/inventory::inventory.goods_receipt.supplier'))
                    ->orderable(false)
                    ->searchable(false)
                    ->withEmptyState()
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->supplier?->name),
                FormattedColumn::make('warehouse')
                    ->title(trans('plugins/inventory::inventory.goods_receipt.warehouse'))
                    ->orderable(false)
                    ->searchable(false)
                    ->withEmptyState()
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->warehouse?->name),
                FormattedColumn::make('receipt_date')
                    ->title(trans('plugins/inventory::inventory.goods_receipt.receipt_date'))
                    ->withEmptyState()
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->receipt_date?->format('Y-m-d')),
                FormattedColumn::make('items_count')
                    ->title(trans('plugins/inventory::inventory.goods_receipt.items'))
                    ->alignCenter()
                    ->searchable(false)
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->items_count ?: 0),
                FormattedColumn::make('total_amount')
                    ->title(trans('plugins/inventory::inventory.goods_receipt.total_amount'))
                    ->alignEnd()
                    ->getValueUsing(fn (FormattedColumn $column) => number_format((float) $column->getItem()->total_amount, 0)),
                FormattedColumn::make('status')
                    ->title(trans('plugins/inventory::inventory.goods_receipt.status.label'))
                    ->alignCenter()
                    ->renderUsing(fn (FormattedColumn $column) => $column->getItem()->status?->toHtml()),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.goods-receipts.delete'),
            ])
            ->queryUsing(function (Builder $query): void {
                $query
                    ->select([
                        'id',
                        'code',
                        'supplier_id',
                        'warehouse_id',
                        'receipt_date',
                        'status',
                        'total_amount',
                        'created_at',
                    ])
                    ->with(['supplier:id,code,name', 'warehouse:id,code,name'])
                    ->withCount('items');
            });
    }
}
