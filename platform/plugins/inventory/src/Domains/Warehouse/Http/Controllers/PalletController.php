<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Services\PalletService;
use Illuminate\Http\Request;

class PalletController extends BaseController
{
    public function store(Warehouse $warehouse, Request $request, PalletService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'current_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:empty,open,in_use,closed,damaged,locked'],
            'note' => ['nullable', 'string'],
        ]);

        $service->create($warehouse, $data);

        return $this->httpResponse()->setPreviousUrl(route('inventory.warehouse.show', $warehouse))->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function move(Warehouse $warehouse, Pallet $pallet, Request $request, PalletService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $data = $request->validate([
            'to_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'note' => ['nullable', 'string'],
        ]);

        $service->move($warehouse, $pallet, $data['to_location_id'] ?? null, $data['note'] ?? null);

        return $this->httpResponse()->setPreviousUrl(route('inventory.warehouse.show', $warehouse))->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Warehouse $warehouse, Pallet $pallet, PalletService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $service->deleteOrDeactivate($warehouse, $pallet);

        return $this->httpResponse()->setPreviousUrl(route('inventory.warehouse.show', $warehouse))->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
