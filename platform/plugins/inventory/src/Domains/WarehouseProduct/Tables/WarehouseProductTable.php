<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Tables;

use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class WarehouseProductTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(WarehouseProduct::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('inventory.warehouse-products.create'))
            ->addActions([
                EditAction::make()->route('inventory.warehouse-products.edit'),
                DeleteAction::make()->route('inventory.warehouse-products.destroy'),
            ])
            ->addColumns([
                FormattedColumn::make('product_id')->title('Product'),
                FormattedColumn::make('product_variation_id')->title('Variation'),
                FormattedColumn::make('supplier_id')->title('Supplier'),
                FormattedColumn::make('is_active')->title('Active'),
                CreatedAtColumn::make(),
            ])
            ->queryUsing(function (Builder $query): void {
                $query->select(['id', 'warehouse_id', 'product_id', 'product_variation_id', 'supplier_id', 'is_active', 'created_at']);
            });
    }
}
