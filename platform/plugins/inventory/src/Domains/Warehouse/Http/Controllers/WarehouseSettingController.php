<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseSettingRequest;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseSettingService;

class WarehouseSettingController extends BaseController
{
    public function store(Warehouse $warehouse, WarehouseSettingRequest $request, WarehouseSettingService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.edit'), 403);

        $service->upsert($warehouse, $request->validated());

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
