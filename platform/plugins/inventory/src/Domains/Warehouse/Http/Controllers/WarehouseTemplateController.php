<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseTemplateService;
use Illuminate\Http\Request;

class WarehouseTemplateController extends BaseController
{
    public function index()
    {
        return response()->json([
            'data' => \Botble\Inventory\Domains\Warehouse\Support\WarehouseTemplateRegistry::all(),
        ]);
    }

    public function apply(Warehouse $warehouse, Request $request, WarehouseTemplateService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.locations.manage'), 403);

        $data = $request->validate([
            'template_code' => ['required', 'string'],
            'mode' => ['nullable', 'in:append,overwrite'],
        ]);

        $service->apply($warehouse, $data['template_code'], $data['mode'] ?? 'append');

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage('Áp dụng mẫu kho thành công.');
    }
}
