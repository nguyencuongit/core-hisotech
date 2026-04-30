<?php

namespace Botble\Inventory\Domains\Packing\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Packing\DTO\PackingDTO;
use Botble\Inventory\Domains\Packing\Forms\PackingForm;
use Botble\Inventory\Domains\Packing\Http\Requests\PackingRequest;
use Botble\Inventory\Domains\Packing\Tables\PackingTable;
use Botble\Inventory\Domains\Packing\UseCases\PackingUsecase;

class PackingController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.packing.name')), route('inventory.packing.index'));
    }

    public function index(PackingTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.packing.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return PackingForm::create()->renderForm();
    }

    public function store(PackingRequest $request, PackingUsecase $usecase)
    {
        $packing = $usecase->create(PackingDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.packing.index'))
            ->setNextUrl(route('inventory.packing.edit', $packing->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $packing, PackingUsecase $usecase)
    {
        $packing = $usecase->loadForEdit($packing);

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $packing->code]));

        return PackingForm::createFromModel($packing)->renderForm();
    }

    public function update(int|string $packing, PackingRequest $request, PackingUsecase $usecase)
    {
        $usecase->update($packing, PackingDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.packing.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $packing, PackingUsecase $usecase)
    {
        $usecase->delete($packing);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.packing.index'))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
