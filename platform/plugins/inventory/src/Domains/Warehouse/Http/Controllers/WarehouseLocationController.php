<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseLocationRequest;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseLocationService;

class WarehouseLocationController extends BaseController
{
    public function store(Warehouse $warehouse, WarehouseLocationRequest $request, WarehouseLocationService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.locations.manage'), 403);

        $service->create($warehouse, $request->validated());

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function update(Warehouse $warehouse, WarehouseLocation $warehouseLocation, WarehouseLocationRequest $request, WarehouseLocationService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.locations.manage'), 403);

        $service->update($warehouse, $warehouseLocation, $request->validated());

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Warehouse $warehouse, WarehouseLocation $warehouseLocation, WarehouseLocationService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.locations.manage'), 403);

        $service->deactivateOrDelete($warehouse, $warehouseLocation);

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
