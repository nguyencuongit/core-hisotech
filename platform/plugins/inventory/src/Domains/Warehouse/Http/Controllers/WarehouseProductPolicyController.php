<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseProductPolicyRequest;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProductPolicy;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseProductPolicyService;

class WarehouseProductPolicyController extends BaseController
{
    public function store(Warehouse $warehouse, WarehouseProduct $warehouseProduct, WarehouseProductPolicyRequest $request, WarehouseProductPolicyService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $this->ensureWarehouseProduct($warehouse, $warehouseProduct);

        $presetCode = $request->input('preset_code');
        $policy = $presetCode ? $service->applyPreset($warehouseProduct, (string) $presetCode) : $service->upsert($warehouseProduct, $request->validated());

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function update(Warehouse $warehouse, WarehouseProduct $warehouseProduct, WarehouseProductPolicy $warehouseProductPolicy, WarehouseProductPolicyRequest $request, WarehouseProductPolicyService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $this->ensureWarehouseProduct($warehouse, $warehouseProduct);

        $service->upsert($warehouseProduct, $request->validated());

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    protected function ensureWarehouseProduct(Warehouse $warehouse, WarehouseProduct $warehouseProduct): void
    {
        if ((int) $warehouseProduct->warehouse_id !== (int) $warehouse->getKey()) {
            abort(404);
        }
    }
}
