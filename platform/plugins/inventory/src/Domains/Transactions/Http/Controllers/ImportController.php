<?php

namespace Botble\Inventory\Domains\Transactions\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\WarehouseStaff\Http\Requests\WarehousePositionRequest;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehousePosition;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Transactions\Tables\ImportTable;
use Botble\Inventory\Forms\InventoryForm;
use Botble\Inventory\Domains\Transactions\Forms\ImportForm;

class ImportController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.transactions.import.name')), route('inventory.transactions-import.index'));
    }

    public function index(ImportTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.transactions.import.name'));
        
        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return ImportForm::create()->renderForm();
    }

    public function store(WarehousePositionRequest $request)
    {
        $form = ImportForm::create()->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse-positions.index'))
            ->setNextUrl(route('inventory.warehouse-positions.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(WarehousePosition $warehousePosition)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $warehousePosition->name]));

        return ImportForm::createFromModel($warehousePosition)->renderForm();
    }

    public function update(WarehousePosition $warehousePosition, WarehousePositionRequest $request)
    {
        ImportForm::createFromModel($warehousePosition)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse-positions.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(WarehousePosition $warehousePosition)
    {
        return DeleteResourceAction::make($warehousePosition);
    }
}
