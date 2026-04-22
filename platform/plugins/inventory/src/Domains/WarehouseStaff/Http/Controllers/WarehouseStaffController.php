<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\WarehouseStaff\Http\Requests\WarehouseStaffRequest;

use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaff;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaffAssignments;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\WarehouseStaff\Tables\WarehouseStaffTable;
use Botble\Inventory\Domains\WarehouseStaff\Forms\WarehouseStaffForm;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use Botble\Inventory\Domains\WarehouseStaff\Usecase\AssignmentsUsercase;

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

    public function store(WarehouseStaffRequest $request, AssignmentsUsercase $assignmentsUsercase)
    {
        return DB::transaction(function () use ($request,$assignmentsUsercase) {
            $form = WarehouseStaffForm::create()->setRequest($request);

            $form->save();
            $warehouseIds = [];
            foreach($request->warehouse_id as $item){
                $warehouseIds[] = $item[0];
            }
            $assignmentsUsercase->updateWarehouseId($form->getModel()->getKey(),$warehouseIds,$request->position);
            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.warehouse-staff.index'))
                ->setNextUrl(route('inventory.warehouse-staff.edit', $form->getModel()->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        });
    }

    public function edit(WarehouseStaff $warehouseStaff)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $warehouseStaff->full_name]));

        return WarehouseStaffForm::createFromModel($warehouseStaff)->renderForm();
    }

    public function update(WarehouseStaff $warehouseStaff, WarehouseStaffRequest $request, AssignmentsUsercase $assignmentsUsercase)
    {
        return DB::transaction(function () use ($request, $warehouseStaff,$assignmentsUsercase) {
            $form = WarehouseStaffForm::createFromModel($warehouseStaff)
                ->setRequest($request)
                ->save();

            $warehouseIds = [];
            foreach($request->warehouse_id as $item){
                $warehouseIds[] = $item[0];
            }
            $assignmentsUsercase->updateWarehouseId($form->getModel()->getKey(),$warehouseIds,$request->position);

            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.warehouse-staff.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        });
    }

    public function destroy(WarehouseStaff $warehouseStaff)
    {
        return DeleteResourceAction::make($warehouseStaff);
    }
}
