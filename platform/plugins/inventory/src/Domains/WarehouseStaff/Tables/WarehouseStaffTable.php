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

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),

            NameColumn::make('full_name')
                ->route('inventory.warehouse-staff.edit'),
            Column::make('staff_code')->title('Mã nhân viên'),
            Column::make('phone')->title('Số điện thoại'),
            Column::make('email')->title('Email'),
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}