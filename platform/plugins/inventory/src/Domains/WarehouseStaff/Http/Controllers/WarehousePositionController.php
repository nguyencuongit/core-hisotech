<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Http\Requests\InventoryRequest;
use Botble\Inventory\Models\Inventory;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\WarehouseStaff\Tables\WarehousePositionTable;
use Botble\Inventory\Forms\InventoryForm;

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

        return InventoryForm::create()->renderForm();
    }

    public function store(InventoryRequest $request)
    {
        $form = InventoryForm::create()->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.index'))
            ->setNextUrl(route('inventory.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Inventory $inventory)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $inventory->name]));

        return InventoryForm::createFromModel($inventory)->renderForm();
    }

    public function update(Inventory $inventory, InventoryRequest $request)
    {
        InventoryForm::createFromModel($inventory)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Inventory $inventory)
    {
        return DeleteResourceAction::make($inventory);
    }
}
