<?php

namespace Botble\Inventory\Domains\Transactions\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Transactions\Tables\ExportTable;
use Botble\Inventory\Domains\Transactions\Forms\ExportForm;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Illuminate\Http\Request;

class ExportController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.transactions.export.name')), route('inventory.transactions-export.index'));
    }

    public function index(ExportTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.transactions.export.name'));

        return $table->render('plugins/inventory::tables.export_table');
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return ExportForm::create()->renderForm();
    }

    public function store(Request $request)
    {
        $form = ExportForm::create()->setRequest($request);
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.transactions-export.index'))
            ->setNextUrl(route('inventory.transactions-export.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Export $export)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $export->code]));

        return ExportForm::createFromModel($export)->renderForm();
    }

    public function update(Export $export, Request $request)
    {
        $form = ExportForm::createFromModel($export)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.transactions-export.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Export $export)
    {
        return DeleteResourceAction::make($export);
    }
}
