<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\WarehouseStaff\Http\Requests\WarehousePositionRequest;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehousePosition;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\WarehouseStaff\Tables\WarehousePositionTable;
use Botble\Inventory\Forms\InventoryForm;
use Botble\Inventory\Domains\WarehouseStaff\Forms\WarehousePositionForm;

class WarehousePositionController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.warehouse_positions.name')), route('inventory.warehouse-positions.index'));
    }

    public function index(WarehousePositionTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.warehouse_positions.name'));
        
        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return WarehousePositionForm::create()->renderForm();
    }

    public function store(WarehousePositionRequest $request)
    {
        $form = WarehousePositionForm::create()->setRequest($request);

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

        return WarehousePositionForm::createFromModel($warehousePosition)->renderForm();
    }

    public function update(WarehousePosition $warehousePosition, WarehousePositionRequest $request)
    {
        WarehousePositionForm::createFromModel($warehousePosition)
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
