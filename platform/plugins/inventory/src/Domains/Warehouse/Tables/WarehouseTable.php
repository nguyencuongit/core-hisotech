<?php

namespace Botble\Inventory\Domains\Warehouse\Tables;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
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

class WarehouseTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Warehouse::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.warehouse.create')->permission('warehouse.create')
            )
            ->addActions([
                EditAction::make('show')
                    ->label('Xem')
                    ->icon('ti ti-map-2')
                    ->route('inventory.warehouse.show')
                    ->permission('warehouse.show'),
                EditAction::make()->route('inventory.warehouse.edit')->permission('warehouse.edit'),
                DeleteAction::make()->route('inventory.warehouse.destroy')->permission('warehouse.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('warehouse.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('status', function (Warehouse $item) {
                return (int) $item->status === 1
                    ? '<span class="badge bg-success-lt text-success">Active</span>'
                    : '<span class="badge bg-danger-lt text-danger">Inactive</span>';
            });

        return $this->toJson($data);
    }

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'code',
                'type',
                'manager_id',
                'address',
                'province_id',
                'ward_id',
                'phone',
                'email',
                'status',
                'description',
                'created_at',
            ]);

        $warehouseIds = inventory_warehouse_ids();
        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('id', $warehouseIds);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),

            NameColumn::make()
                ->route('inventory.warehouse.edit'),

            Column::make('code')
                ->title('Mã kho'),

            Column::make('address')
                ->title('Địa chỉ kho'),

            Column::make('status')
                ->title('Active')
                ->alignCenter(),

            CreatedAtColumn::make(),
        ];
    }
}
