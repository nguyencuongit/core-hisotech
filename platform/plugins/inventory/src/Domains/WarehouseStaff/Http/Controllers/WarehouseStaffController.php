<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\WarehouseStaff\Http\Requests\WarehouseStaffRequest;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaff;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\WarehouseStaff\Tables\WarehouseStaffTable;
use Botble\Inventory\Domains\WarehouseStaff\Forms\WarehouseStaffForm;

class WarehouseStaffController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.warehouse-staff.name')), route('inventory.warehouse-staff.index'));
    }

    public function index(WarehouseStaffTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.warehouse-staff.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return WarehouseStaffForm::create()->renderForm();
    }

    public function store(WarehouseStaffRequest $request)
    {
        $form = WarehouseStaffForm::create()->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse-staff.index'))
            ->setNextUrl(route('inventory.warehouse-staff.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(WarehouseStaff $WarehouseStaff)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $WarehouseStaff->full_name]));

        return WarehouseStaffForm::createFromModel($WarehouseStaff)->renderForm();
    }

    public function update(WarehouseStaff $WarehouseStaff, WarehouseStaffRequest $request)
    {
        WarehouseStaffForm::createFromModel($WarehouseStaff)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse-staff.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(WarehouseStaff $WarehouseStaff)
    {
        return DeleteResourceAction::make($WarehouseStaff);
    }
}
