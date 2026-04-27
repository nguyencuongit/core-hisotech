<?php

namespace Botble\Inventory\Domains\Packing\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Packing\Forms\PackingForm;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Tables\PackingTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $form = PackingForm::create()->setRequest($request);
            $form->save();

            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.packing.index'))
                ->setNextUrl(route('inventory.packing.edit', $form->getModel()->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        });
    }

    public function edit(PackingList $packing)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $packing->code]));

        return PackingForm::createFromModel($packing)->renderForm();
    }

    public function update(PackingList $packing, Request $request)
    {
        return DB::transaction(function () use ($request, $packing) {
            PackingForm::createFromModel($packing)
                ->setRequest($request)
                ->save();

            return $this
                ->httpResponse()
                ->setPreviousUrl(route('inventory.packing.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        });
    }

    public function destroy(PackingList $packing)
    {
        return DeleteResourceAction::make($packing);
    }
}
