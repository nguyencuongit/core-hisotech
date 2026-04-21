<?php

namespace Botble\Inventory\Tables;

use Botble\Inventory\Enums\SupplierStatusEnum;
use Botble\Inventory\Models\Supplier;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class SupplierTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Supplier::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('inventory.suppliers.create'))
            ->addActions([
                EditAction::make()->route('inventory.suppliers.edit'),
                DeleteAction::make()->route('inventory.suppliers.destroy'),
            ])
            ->addColumns([
                NameColumn::make()->title('#')->route('inventory.suppliers.show'),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('inventory.suppliers.delete'),
            ])
            ->queryUsing(function (Builder $query): void {
                $query->select(['id', 'code', 'name', 'type', 'tax_code', 'status', 'created_at'])
                    ->withCount('supplierProducts')
                    ->with(['contacts' => fn ($q) => $q->where('is_primary', true)]);
            });
    }

    protected function tableBody(): string
    {
        return parent::tableBody();
    }
}
