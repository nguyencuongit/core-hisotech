<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Warehouse\Forms\WarehouseForm;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseRequest;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\PalletMovement;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseSetting;
use Botble\Inventory\Domains\Warehouse\Services\PalletService;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseProductPolicyService;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseSettingService;
use Botble\Inventory\Domains\Warehouse\Support\WarehouseShowViewData;
use Botble\Inventory\Domains\Warehouse\Tables\WarehouseTable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

        return view('plugins/inventory::warehouse.create');
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
                ->with(['product', 'productVariation', 'supplier', 'supplierProduct'])
                ->latest(),
            'maps' => fn ($query) => $query
                ->with([
                    'items' => fn ($itemQuery) => $itemQuery
                        ->with('location')
                        ->orderBy('z_index')
                        ->orderBy('id'),
                ])
                ->orderByDesc('is_active')
                ->latest(),
            'setting',
        ])->loadCount([
            'locations',
            'warehouseProducts',
            'warehouseProducts as active_warehouse_products_count' => fn ($query) => $query->where('is_active', true),
        ]);

        $settings = app(WarehouseSettingService::class)->firstOrCreateDefault($warehouse);

        $locations = WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->orderBy('path')
            ->orderBy('level')
            ->orderBy('code')
            ->get(['id', 'parent_id', 'code', 'name', 'path', 'level', 'type', 'status', 'description']);

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(fn (Supplier $supplier): array => [
                $supplier->getKey() => trim(sprintf('%s - %s', $supplier->code, $supplier->name), ' -'),
            ])
            ->all();

        $pallets = Pallet::query()
            ->with(['currentLocation', 'movements' => fn ($query) => $query->latest()])
            ->where('warehouse_id', $warehouse->getKey())
            ->latest()
            ->get();

        $viewData = WarehouseShowViewData::make($warehouse, $locations)->toArray();

        $this->pageTitle($warehouse->name);

        return view('plugins/inventory::warehouse.show', array_merge(compact('warehouse', 'locations', 'suppliers', 'settings'), $viewData));
    }

    public function updateSettings(Warehouse $warehouse, Request $request, WarehouseSettingService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.edit'), 403);

        $data = $request->validate([
            'warehouse_mode' => ['required', 'in:simple,advanced'],
            'use_pallet' => ['nullable', 'boolean'],
            'require_pallet' => ['nullable', 'boolean'],
            'use_qc' => ['nullable', 'boolean'],
            'use_batch' => ['nullable', 'boolean'],
            'use_serial' => ['nullable', 'boolean'],
            'use_map' => ['nullable', 'boolean'],
        ]);

        $service->upsert($warehouse, $data);

        return $this->httpResponse()->setPreviousUrl(route('inventory.warehouse.show', $warehouse))->setMessage('Cập nhật cấu hình kho thành công.');
    }

    public function createPallet(Warehouse $warehouse, Request $request, PalletService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.edit'), 403);

        $settings = $warehouse->setting ?: app(WarehouseSettingService::class)->firstOrCreateDefault($warehouse);
        if (! $settings->use_pallet) {
            return $this->httpResponse()->setError()->setMessage('Kho này chưa bật pallet.');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'current_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:empty,open,in_use,closed,damaged,locked'],
            'note' => ['nullable', 'string'],
        ]);

        $service->create($warehouse, $data);

        return $this->httpResponse()->setPreviousUrl(route('inventory.warehouse.show', $warehouse))->setMessage('Tạo pallet thành công.');
    }

    public function movePallet(Warehouse $warehouse, Pallet $pallet, Request $request, PalletService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.edit'), 403);

        $data = $request->validate([
            'to_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'note' => ['nullable', 'string'],
        ]);

        $service->move($warehouse, $pallet, Arr::get($data, 'to_location_id') ? (int) $data['to_location_id'] : null, $data['note'] ?? null);

        return $this->httpResponse()->setPreviousUrl(route('inventory.warehouse.show', $warehouse))->setMessage('Di chuyển pallet thành công.');
    }
}
