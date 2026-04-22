<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaff;
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
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaffAssignments;




class WarehouseStaffTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(WarehouseStaff::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.warehouse-staff.create')->permission('warehouse-staff.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.warehouse-staff.edit')->permission('warehouse-staff.edit'),
                DeleteAction::make()->route('inventory.warehouse-staff.destroy')->permission('warehouse-staff.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.warehouse-staff.destroy'),
            ]);
    }

   

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with('warehouse')
            ->with('assignments.warehouse')
            ->select([
                'id',
                'user_id',
                'staff_code',
                'full_name',
                'phone',
                'email',
                'status',
                'created_at',
            ]);
        
        $warehouseIds = inventory_warehouse_ids();
        if(!inventory_is_super_admin() && !empty($warehouseIds)){
            $query->whereHas('assignments', function ($q) use ($warehouseIds) {
                $q->whereIn('warehouse_id', $warehouseIds);
            });
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
    
            NameColumn::make('full_name')
                ->route('inventory.warehouse-staff.edit'),
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

        
            Column::make('staff_code')->title('Mã nhân viên'),
            Column::make('phone')->title('Số điện thoại'),
            Column::make('email')->title('Email'),
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}