<?php

namespace Botble\Inventory\Domains\Transactions\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\StatusColumn;


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

    // public function ajax(): JsonResponse
    // {
    //     // $data = $this->table
    //     //     ->eloquent($this->query())
    //     //     ->editColumn('status', function (WarehousePosition $item) {
    //     //         return (int) $item->is_active === 1
    //     //             ? '<span class="badge bg-success-lt text-success">Active</span>'
    //     //             : '<span class="badge bg-danger-lt text-danger">Inactive</span>';
    //     //     });

    //     // return $this->toJson($data);
    // }

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

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),

            NameColumn::make('code')->title('Mã đơn')
                ->route('inventory.transactions-export.edit'),
            FormattedColumn::make('warehouse_id')
                ->title('Kho'),
            // Column::make('is_active')
            //     ->title('Active')
            //     ->alignCenter(),
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}
