<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseMap;
use Botble\Inventory\Domains\Warehouse\Services\WarehouseMapService;
use Botble\Inventory\Domains\Warehouse\Support\WarehouseMapBlueprints;
use Botble\Inventory\Domains\Warehouse\Support\PalletLocationRules;
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
            'storage_mode' => ['required', Rule::in(['direct', 'pallet'])],
            'background_image' => ['nullable', 'string', 'max:255'],
            'width' => ['required', 'integer', 'min:480', 'max:10000'],
            'height' => ['required', 'integer', 'min:320', 'max:10000'],
            'scale_ratio' => ['nullable', 'numeric', 'min:0.0001'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $map = $service->create($warehouse, $data);

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', ['warehouse' => $warehouse, 'tab' => 'maps', 'map_id' => $map->getKey()]))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function applyBlueprint(Warehouse $warehouse, Request $request, WarehouseMapService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.maps.manage'), 403);

        $data = $request->validate([
            'blueprint_code' => ['required', Rule::in(array_keys(WarehouseMapBlueprints::all()))],
        ]);

        $map = $service->createFromBlueprint($warehouse, $data['blueprint_code']);

        return $this->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', ['warehouse' => $warehouse, 'tab' => 'maps', 'map_id' => $map->getKey()]))
            ->setMessage('Đã tạo sơ đồ mẫu.');
    }

    public function sync(Warehouse $warehouse, WarehouseMap $warehouseMap, Request $request, WarehouseMapService $service)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.maps.manage'), 403);

        if (is_string($request->input('items'))) {
            $decodedItems = json_decode((string) $request->input('items'), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['items' => $decodedItems]);
            }
        }

        $data = $request->validate([
            'map_width' => ['nullable', 'integer', 'min:480', 'max:10000'],
            'map_height' => ['nullable', 'integer', 'min:320', 'max:10000'],
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

        $map = $service->syncItems($warehouse, $warehouseMap, $data['items'], [
            'width' => $data['map_width'] ?? null,
            'height' => $data['map_height'] ?? null,
        ]);

        return response()->json([
            'message' => 'Đã lưu bố cục sơ đồ kho.',
            'map' => [
                'id' => (int) $map->getKey(),
                'width' => (int) $map->width,
                'height' => (int) $map->height,
            ],
            'data' => $map->items,
            'locations' => $this->locationOptions($warehouse),
        ]);
    }

    protected function locationOptions(Warehouse $warehouse): array
    {
        return WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->orderBy('path')
            ->orderBy('level')
            ->orderBy('code')
            ->get(['id', 'parent_id', 'code', 'name', 'path', 'level', 'type', 'status'])
            ->map(fn (WarehouseLocation $location) => [
                'id' => (int) $location->getKey(),
                'label' => $location->displayLabel(),
                'path' => $location->path,
                'type' => $location->type,
                'status' => (bool) $location->status,
                'is_stockable' => PalletLocationRules::isAllowed($location->type),
            ])
            ->values()
            ->all();
    }
}
