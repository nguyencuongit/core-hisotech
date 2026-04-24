<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseMap;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseMapService;
use Botble\Inventory\Domains\Warehouse\Support\WarehouseMapBlueprints;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseMapController extends BaseController
{
    public function index(Warehouse $warehouse)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.maps.manage'), 403);

        return response()->json([
            'data' => WarehouseMapBlueprints::all(),
        ]);
    }

    public function store(Warehouse $warehouse, Request $request, WarehouseMapService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.maps.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'map_type' => ['required', 'in:layout,2d,floor_plan,rack_map'],
            'background_image' => ['nullable', 'string', 'max:255'],
            'width' => ['nullable', 'integer', 'min:1'],
            'height' => ['nullable', 'integer', 'min:1'],
            'scale_ratio' => ['nullable', 'numeric', 'min:0.0001'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service->create($warehouse, $data);

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function applyBlueprint(Warehouse $warehouse, Request $request, WarehouseMapService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.maps.manage'), 403);

        $data = $request->validate([
            'blueprint_code' => ['required', 'in:simple,qc,rack_floor'],
        ]);

        $service->createFromBlueprint($warehouse, $data['blueprint_code']);

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse, ['tab' => 'maps']))
            ->setMessage('Đã tạo sơ đồ mẫu.');
    }

    public function sync(Warehouse $warehouse, WarehouseMap $warehouseMap, Request $request, WarehouseMapService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.maps.manage'), 403);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.location_id' => ['nullable', 'integer'],
            'items.*.item_type' => ['required', 'string', 'max:50'],
            'items.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.shape_type' => ['required', Rule::in(['rect', 'label', 'image', 'polygon'])],
            'items.*.x' => ['required', 'numeric'],
            'items.*.y' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric', 'min:1'],
            'items.*.height' => ['required', 'numeric', 'min:1'],
            'items.*.rotation' => ['nullable', 'numeric'],
            'items.*.color' => ['nullable', 'string', 'max:20'],
            'items.*.z_index' => ['nullable', 'integer'],
            'items.*.is_clickable' => ['nullable', 'boolean'],
            'items.*.meta_json' => ['nullable', 'array'],
        ]);

        $map = $service->syncItems($warehouse, $warehouseMap, $data['items']);

        return response()->json([
            'message' => 'Đã lưu bố cục sơ đồ kho.',
            'data' => $map->items,
        ]);
    }
}
