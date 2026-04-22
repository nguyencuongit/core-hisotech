<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseRequest;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Tables\WarehouseTable;
use Botble\Inventory\Forms\InventoryForm;
use Botble\Inventory\Domains\Warehouse\Forms\WarehouseForm;

class WarehouseController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.warehouse.name')), route('inventory.warehouse.index'));
    }

    public function index(WarehouseTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.warehouse.name'));
        
        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return WarehouseForm::create()->renderForm();
    }

    public function store(WarehouseRequest $request)
    {
        $form = WarehouseForm::create()->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.index'))
            ->setNextUrl(route('inventory.warehouse.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Warehouse $Warehouse)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $Warehouse->name]));

        return WarehouseForm::createFromModel($Warehouse)->renderForm();
    }

    public function update(Warehouse $Warehouse, WarehouseRequest $request)
    {
        WarehouseForm::createFromModel($Warehouse)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Warehouse $Warehouse)
    {
        return DeleteResourceAction::make($Warehouse);
    }

    public function show(){
        dd(1);
    }
}
