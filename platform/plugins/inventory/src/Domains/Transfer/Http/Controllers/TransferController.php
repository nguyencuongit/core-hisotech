<?php

namespace Botble\Inventory\Domains\Transfer\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Transfer\Forms\TransferForm;
use Botble\Inventory\Domains\Transfer\Models\InternalTransfer;
use Botble\Inventory\Domains\Transfer\Tables\TransferTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.transfer.name')), route('inventory.transfer.index'));
    }

    public function index(TransferTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.transfer.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return TransferForm::create()->renderForm();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $form = TransferForm::create()->setRequest($request);
            $form->save();

            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.transfer.index'))
                ->setNextUrl(route('inventory.transfer.edit', $form->getModel()->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        });
    }

    public function edit(InternalTransfer $transfer)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $transfer->code]));

        return TransferForm::createFromModel($transfer)->renderForm();
    }

    public function update(InternalTransfer $transfer, Request $request)
    {
        return DB::transaction(function () use ($request, $transfer) {
            TransferForm::createFromModel($transfer)
                ->setRequest($request)
                ->save();

            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.transfer.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        });
    }

    public function destroy(InternalTransfer $transfer)
    {
        return DeleteResourceAction::make($transfer);
    }
}
