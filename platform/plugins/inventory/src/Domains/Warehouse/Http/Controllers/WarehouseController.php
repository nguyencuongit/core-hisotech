<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Warehouse\Forms\WarehouseForm;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseRequest;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Tables\WarehouseTable;

class WarehouseController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/inventory::inventory.warehouse.name'), route('inventory.warehouse.index'));
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

    public function edit(Warehouse $warehouse)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $warehouse->name]));

        return WarehouseForm::createFromModel($warehouse)->renderForm();
    }

    public function update(Warehouse $warehouse, WarehouseRequest $request)
    {
        WarehouseForm::createFromModel($warehouse)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Warehouse $warehouse)
    {
        return DeleteResourceAction::make($warehouse);
    }

    public function show(Warehouse $warehouse)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.show'), 403);

        $warehouse->load([
            'warehouseProducts' => fn ($query) => $query
                ->with(['product', 'productVariation', 'defaultLocation', 'supplier', 'supplierProduct'])
                ->latest(),
        ])->loadCount([
            'locations',
            'warehouseProducts',
            'warehouseProducts as active_warehouse_products_count' => fn ($query) => $query->where('is_active', true),
        ]);

        $locations = WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->orderBy('path')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'path']);

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(fn (Supplier $supplier): array => [
                $supplier->getKey() => trim(sprintf('%s - %s', $supplier->code, $supplier->name), ' -'),
            ])
            ->all();

        $this->pageTitle($warehouse->name);

        return view('plugins/inventory::warehouse.show', compact('warehouse', 'locations', 'suppliers'));
    }
}
