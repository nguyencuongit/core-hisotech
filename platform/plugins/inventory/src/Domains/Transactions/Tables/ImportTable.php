<?php

namespace Botble\Inventory\Domains\Transactions\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Domains\Transactions\Models\Import;
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

class ImportTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Import::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.transactions-import.create')->permission('transactions-import.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.transactions-import.edit')->permission('transactions-import.edit'),
                DeleteAction::make()->route('inventory.transactions-import.destroy')->permission('transactions-import.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.transactions-import.destroy'),
            ]);
    }
   

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query();
        
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
    
            NameColumn::make('doc_code')->title('Mã đơn')
                ->route('inventory.transactions-import.edit'),
            FormattedColumn::make('warehouse_id')
                ->title('Kho'),
            // Column::make('staff_code')->title('Mã nhân viên'),
            
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}