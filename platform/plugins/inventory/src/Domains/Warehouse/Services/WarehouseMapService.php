<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseMap;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseMapItem;
use Botble\Inventory\Domains\Warehouse\Support\WarehouseMapBlueprints;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseMapService
{
    public function create(Warehouse $warehouse, array $data): WarehouseMap
    {
        return DB::transaction(function () use ($warehouse, $data): WarehouseMap {
            $isActive = (bool) Arr::get($data, 'is_active', true);

            if ($isActive) {
                WarehouseMap::query()
                    ->where('warehouse_id', $warehouse->getKey())
                    ->update(['is_active' => false]);
            }

            return WarehouseMap::query()->create([
                'warehouse_id' => $warehouse->getKey(),
                'name' => Arr::get($data, 'name'),
                'map_type' => Arr::get($data, 'map_type', 'floor_plan'),
                'background_image' => Arr::get($data, 'background_image'),
                'width' => Arr::get($data, 'width'),
                'height' => Arr::get($data, 'height'),
                'scale_ratio' => Arr::get($data, 'scale_ratio'),
                'is_active' => $isActive,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function createFromBlueprint(Warehouse $warehouse, string $blueprintCode): WarehouseMap
    {
        $blueprint = WarehouseMapBlueprints::all()[$blueprintCode] ?? null;

        if (! $blueprint) {
            throw ValidationException::withMessages([
                'blueprint_code' => 'Sơ đồ mẫu không hợp lệ.',
            ]);
        }

        return DB::transaction(function () use ($warehouse, $blueprint, $blueprintCode): WarehouseMap {
            WarehouseMap::query()
                ->where('warehouse_id', $warehouse->getKey())
                ->update(['is_active' => false]);

            $map = WarehouseMap::query()->create([
                'warehouse_id' => $warehouse->getKey(),
                'name' => $blueprint['name'],
                'map_type' => 'floor_plan',
                'background_image' => $blueprint['background_image'] ?? null,
                'width' => $blueprint['width'] ?? 1200,
                'height' => $blueprint['height'] ?? 800,
                'scale_ratio' => 1,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            foreach ($blueprint['items'] as $index => $item) {
                $locationId = null;

                if (! empty($item['location_code'])) {
                    $locationId = WarehouseLocation::query()
                        ->where('warehouse_id', $warehouse->getKey())
                        ->where('code', $item['location_code'])
                        ->value('id');
                }

                WarehouseMapItem::query()->create([
                    'warehouse_map_id' => $map->getKey(),
                    'location_id' => $locationId,
                    'item_type' => $item['item_type'],
                    'label' => $item['label'],
                    'shape_type' => $item['shape_type'],
                    'x' => $item['x'],
                    'y' => $item['y'],
                    'width' => $item['width'],
                    'height' => $item['height'],
                    'rotation' => $item['rotation'] ?? 0,
                    'color' => $item['color'] ?? '#e2e8f0',
                    'z_index' => $item['z_index'] ?? ($index + 1),
                    'is_clickable' => (bool) ($item['is_clickable'] ?? true),
                    'meta_json' => [
                        'blueprint' => $blueprintCode,
                        'location_code' => $item['location_code'] ?? null,
                    ],
                ]);
            }

            return $map;
        });
    }

    public function syncItems(Warehouse $warehouse, WarehouseMap $map, array $items): WarehouseMap
    {
        $this->ensureMapBelongsToWarehouse($warehouse, $map);

        return DB::transaction(function () use ($warehouse, $map, $items): WarehouseMap {
            $existingItems = $map->items()->get()->keyBy(fn (WarehouseMapItem $item) => (string) $item->getKey());
            $keepIds = [];

            foreach ($items as $index => $item) {
                $itemId = Arr::get($item, 'id');
                $locationId = Arr::get($item, 'location_id') ?: null;

                if ($locationId) {
                    $this->ensureLocationBelongsToWarehouse($warehouse, (int) $locationId);
                }

                $payload = [
                    'location_id' => $locationId ? (int) $locationId : null,
                    'item_type' => (string) Arr::get($item, 'item_type', 'zone'),
                    'label' => trim((string) Arr::get($item, 'label', '')),
                    'shape_type' => (string) Arr::get($item, 'shape_type', 'rect'),
                    'x' => round((float) Arr::get($item, 'x', 0), 2),
                    'y' => round((float) Arr::get($item, 'y', 0), 2),
                    'width' => max(36, round((float) Arr::get($item, 'width', 120), 2)),
                    'height' => max(28, round((float) Arr::get($item, 'height', 90), 2)),
                    'rotation' => round((float) Arr::get($item, 'rotation', 0), 2),
                    'color' => Arr::get($item, 'color') ?: '#e2e8f0',
                    'z_index' => (int) Arr::get($item, 'z_index', $index + 1),
                    'is_clickable' => (bool) Arr::get($item, 'is_clickable', true),
                    'meta_json' => $this->normalizeMeta(Arr::get($item, 'meta_json', [])),
                ];

                if ($itemId && $existingItems->has((string) $itemId)) {
                    $mapItem = $existingItems->get((string) $itemId);
                    $mapItem->fill($payload);
                    $mapItem->save();
                    $keepIds[] = (int) $mapItem->getKey();
                    continue;
                }

                $mapItem = WarehouseMapItem::query()->create($payload + [
                    'warehouse_map_id' => $map->getKey(),
                ]);

                $keepIds[] = (int) $mapItem->getKey();
            }

            WarehouseMapItem::query()
                ->where('warehouse_map_id', $map->getKey())
                ->when(
                    $keepIds !== [],
                    fn ($query) => $query->whereNotIn('id', $keepIds),
                    fn ($query) => $query
                )
                ->delete();

            return $map->fresh(['items.location']);
        });
    }

    protected function ensureMapBelongsToWarehouse(Warehouse $warehouse, WarehouseMap $map): void
    {
        if ((int) $map->warehouse_id !== (int) $warehouse->getKey()) {
            abort(404);
        }
    }

    protected function ensureLocationBelongsToWarehouse(Warehouse $warehouse, int $locationId): void
    {
        $exists = WarehouseLocation::query()
            ->whereKey($locationId)
            ->where('warehouse_id', $warehouse->getKey())
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'items' => 'Có vùng trên sơ đồ đang gắn sai vị trí kho.',
            ]);
        }
    }

    protected function normalizeMeta(mixed $meta): array
    {
        if (! is_array($meta)) {
            return [];
        }

        return collect($meta)
            ->map(function ($value) {
                if (is_array($value)) {
                    return $this->normalizeMeta($value);
                }

                return is_scalar($value) || $value === null ? $value : (string) $value;
            })
            ->all();
    }
}
