<?php

namespace Botble\Inventory\Domains\Return\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Return\Forms\ReturnForm;
use Botble\Inventory\Domains\Return\Models\InventoryReturn;
use Botble\Inventory\Domains\Return\Tables\ReturnTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.return.name')), route('inventory.return.index'));
    }

    public function index(ReturnTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.return.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return ReturnForm::create()->renderForm();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $form = ReturnForm::create()->setRequest($request);
            $form->save();

            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.return.index'))
                ->setNextUrl(route('inventory.return.edit', $form->getModel()->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        });
    }

    public function edit(InventoryReturn $return)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $return->code]));

        return ReturnForm::createFromModel($return)->renderForm();
    }

    public function update(InventoryReturn $return, Request $request)
    {
        return DB::transaction(function () use ($request, $return) {
            ReturnForm::createFromModel($return)
                ->setRequest($request)
                ->save();

            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.return.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        });
    }

    public function destroy(InventoryReturn $return)
    {
        return DeleteResourceAction::make($return);
    }
}
