<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehousePosition;
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

class WarehousePositionTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(WarehousePosition::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.warehouse-positions.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.warehouse-positions.edit'),
                DeleteAction::make()->route('inventory.warehouse-positions.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.warehouse-positions.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('is_active', function (WarehousePosition $item) {
                return (int) $item->is_active === 1
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
                'is_active',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),

            NameColumn::make()
                ->route('inventory.warehouse-positions.edit'),

            Column::make('is_active')
                ->title('Active')
                ->alignCenter(),

            CreatedAtColumn::make(),
        ];
    }
}