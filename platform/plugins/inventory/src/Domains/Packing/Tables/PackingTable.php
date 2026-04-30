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
                DeleteBulkAction::make()->permission('packing.destroy'),
            ]);
    }

   

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()    
            ->query()
            ->with('warehouse')        
            ->select([
                'id',
                'export_id',
                'warehouse_id',
                'code',
                'status',
                'packer_id',
                'packed_at',
                'total_packages',
                'total_items',
                'total_weight',
                'total_volume',
                'note',
                'created_at',
            ]);
        
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
    
            NameColumn::make('code')
                ->route('inventory.packing.edit'),
            FormattedColumn::make('warehouses')
                ->title('Kho')
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(function (FormattedColumn $column) {
                    return $column->getItem()->warehouse?->name ?: '&mdash;';
                }),

        
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}
