<?php

namespace Botble\Inventory\Domains\Transactions\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\WarehouseStaff\Http\Requests\WarehousePositionRequest;
use Botble\Inventory\Domains\Transactions\Models\Import;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Transactions\Tables\ImportTable;
use Botble\Inventory\Forms\InventoryForm;
use Botble\Inventory\Domains\Transactions\Forms\ImportForm;
use Illuminate\Http\Request;

class ImportController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.transactions.import.name')), route('inventory.transactions-import.index'));
    }

    public function index(ImportTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.transactions.import.name'));
        
        // return $table->renderTable();
       return $table->render("plugins/inventory::tables.import_table");
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return ImportForm::create()->renderForm();
    }

    public function store(Request $request)
    {
        $form = ImportForm::create()->setRequest($request);
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.transactions-import.index'))
            ->setNextUrl(route('inventory.transactions-import.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Import $import)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $import->partner_code]));

        return ImportForm::createFromModel($import)->renderForm();
    }

    public function update(Import $import, Request $request)
    {
        ImportForm::createFromModel($import)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.transactions-import.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Import $import)
    {
        return DeleteResourceAction::make($import);
    }
}
