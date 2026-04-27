<?php

namespace Botble\Inventory\Domains\Packing\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Domains\Packing\Models\PackingList;
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




class PackingTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(PackingList::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.packing.create')->permission('packing.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.packing.edit')->permission('packing.edit'),
                DeleteAction::make()->route('inventory.packing.destroy')->permission('packing.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.packing.destroy'),
            ]);
    }

   

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()    
            ->query()
            ->with('warehouse')        
            ->select([
                'export_id',
                'warehouse_id',
                'code',
                'status',
                'packer_id',
                'packed_at',
                'total_packages',
                'total_weight',
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
                ->route('inventory.packing.edit'),
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