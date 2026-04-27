<?php

namespace Botble\Inventory\Domains\Return\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Domains\Return\Models\InventoryReturn;
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




class ReturnTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(InventoryReturn::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.return.create')->permission('return.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.return.edit')->permission('return.edit'),
                DeleteAction::make()->route('inventory.return.destroy')->permission('return.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.return.destroy'),
            ]);
    }

   

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()    
            ->query()
            ->with('warehouse')        
            ->select([
                'code',
                'type',
                'status',
                'warehouse_id',
                'partner_type',
                'partner_id',
                'partner_code',
                'partner_name',
                'partner_phone',
                'reference_type',
                'reference_id',
                'reference_code',
                'reason',
                'requested_by',
                'approved_by',
                'note',
            ]);
        
        $warehouseIds = inventory_warehouse_ids();
        if(!inventory_is_super_admin() && !empty($warehouseIds)){
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
    
            NameColumn::make('code')
                ->route('inventory.return.edit'),
            FormattedColumn::make('warehouses')
                ->title('Kho')
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(function (FormattedColumn $column) {
                    $warehouses = $column->getItem()
                        ->assignments
                        ->pluck('warehouse.name')
                        ->filter()
                        ->implode(', ');

                    return $warehouses ?: '&mdash;';
                }),

        
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}