<?php

namespace Botble\Inventory\Domains\Transfer\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Domains\Transfer\Models\InternalTransfer;
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
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Columns\FormattedColumn;




class TransferTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(InternalTransfer::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.transfer.create')->permission('transfer.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.transfer.edit')->permission('transfer.edit'),
                DeleteAction::make()->route('inventory.transfer.destroy')->permission('transfer.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.transfer.destroy'),
            ]);
    }

   

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()    
            ->query()
            ->with('fromWarehouse')        
            ->select([
                'code',
                'status',
                'from_warehouse_id',
                'to_warehouse_id',
                'requested_by',
                'approved_by',
                'exported_by',
                'imported_by',
                'transfer_date',
                'reason',
                'note',
            ]);
        
        $warehouseIds = inventory_warehouse_ids();
        if(!inventory_is_super_admin() && !empty($warehouseIds)){
            $query->whereIn('from_warehouse_id', $warehouseIds);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
    
            NameColumn::make('code')
                ->route('inventory.transfer.edit'),
            FormattedColumn::make('from_warehouse_id')
                ->title('Kho')
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(function (FormattedColumn $column) {
                    $warehouses = $column->getItem()
                        ->assignments
                        ->pluck('fromWarehouse.name')
                        ->filter()
                        ->implode(', ');

                    return $warehouses ?: '&mdash;';
                }),

        
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}