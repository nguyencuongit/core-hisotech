<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductPolicyDTO;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductPolicyRequest;
use Botble\Inventory\Domains\WarehouseProduct\UseCases\WarehouseProductPolicyUsecase;

class WarehouseProductPolicyController extends BaseController
{
    public function store($warehouse, $warehouseProduct, WarehouseProductPolicyRequest $request, WarehouseProductPolicyUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $usecase->store((int) $warehouse, (int) $warehouseProduct, WarehouseProductPolicyDTO::fromRequest($request));

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function update($warehouse, $warehouseProduct, $warehouseProductPolicy, WarehouseProductPolicyRequest $request, WarehouseProductPolicyUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $usecase->update((int) $warehouse, (int) $warehouseProduct, (int) $warehouseProductPolicy, WarehouseProductPolicyDTO::fromRequest($request));

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
