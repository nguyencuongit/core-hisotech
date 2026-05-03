<?php

namespace Botble\Inventory\Domains\Transfer\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Transfer\DTO\TransferDTO;
use Botble\Inventory\Domains\Transfer\Forms\TransferForm;
use Botble\Inventory\Domains\Transfer\Http\Requests\TransferRequest;
use Botble\Inventory\Domains\Transfer\Models\InternalTransfer;
use Botble\Inventory\Domains\Transfer\Services\TransferService;
use Botble\Inventory\Domains\Transfer\Tables\TransferTable;

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

    public function store(TransferRequest $request, TransferService $service)
    {
        $transfer = $service->create(TransferDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.transfer.index'))
            ->setNextUrl(route('inventory.transfer.edit', $transfer->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(InternalTransfer $transfer)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $transfer->code]));

        return TransferForm::createFromModel($transfer)->renderForm();
    }

    public function update(InternalTransfer $transfer, TransferRequest $request, TransferService $service)
    {
        $service->update($transfer, TransferDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.transfer.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(InternalTransfer $transfer, TransferService $service)
    {
        $service->delete($transfer);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.transfer.index'))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
