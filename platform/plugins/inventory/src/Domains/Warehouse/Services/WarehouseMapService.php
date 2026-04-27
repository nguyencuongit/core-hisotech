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
                'storage_mode' => Arr::get($data, 'storage_mode', 'direct'),
                'background_image' => Arr::get($data, 'background_image'),
                'width' => max(480, (int) Arr::get($data, 'width', 1200)),
                'height' => max(320, (int) Arr::get($data, 'height', 800)),
                'scale_ratio' => Arr::get($data, 'scale_ratio', 1),
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
                'storage_mode' => $blueprint['storage_mode'] ?? 'direct',
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
                    'meta_json' => array_merge([
                        'blueprint' => $blueprintCode,
                        'storage_mode' => $blueprint['storage_mode'] ?? 'direct',
                        'location_code' => $item['location_code'] ?? null,
                    ], $item['meta_json'] ?? []),
                ]);
            }

            return $map;
        });
    }

    public function syncItems(Warehouse $warehouse, WarehouseMap $map, array $items, array $mapData = []): WarehouseMap
    {
        $this->ensureMapBelongsToWarehouse($warehouse, $map);

        return DB::transaction(function () use ($warehouse, $map, $items, $mapData): WarehouseMap {
            $map->forceFill([
                'width' => max(480, (int) (Arr::get($mapData, 'width') ?: $map->width ?: 1200)),
                'height' => max(320, (int) (Arr::get($mapData, 'height') ?: $map->height ?: 800)),
            ])->save();

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
                    $this->syncLogicalLocationsForMapItem($warehouse, $mapItem);
                    $keepIds[] = (int) $mapItem->getKey();
                    continue;
                }

                $mapItem = WarehouseMapItem::query()->create($payload + [
                    'warehouse_map_id' => $map->getKey(),
                ]);

                $this->syncLogicalLocationsForMapItem($warehouse, $mapItem);
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

    protected function syncLogicalLocationsForMapItem(Warehouse $warehouse, WarehouseMapItem $mapItem): void
    {
        $meta = $mapItem->meta_json ?: [];
        $moduleType = (string) Arr::get($meta, 'module_type', $mapItem->item_type);

        if (! in_array($moduleType, ['simple_shelf', 'pallet_rack', 'rack', 'floor_pallet_area', 'pallet_slot'], true)) {
            return;
        }

        if ($moduleType === 'floor_pallet_area') {
            $this->syncFloorPalletAreaLocationsForMapItem($warehouse, $mapItem, $meta);

            return;
        }

        if ($moduleType === 'pallet_slot') {
            $this->syncStandalonePalletSlotLocationForMapItem($warehouse, $mapItem);

            return;
        }

        $levelCount = max(1, (int) (Arr::get($meta, 'height_count') ?: Arr::get($meta, 'level_count') ?: 1));
        $widthCount = max(1, (int) (Arr::get($meta, 'width_count') ?: Arr::get($meta, 'positions_per_level') ?: Arr::get($meta, 'column_count') ?: 1));
        $lengthCount = max(1, (int) (Arr::get($meta, 'length_count') ?: Arr::get($meta, 'row_count') ?: 1));
        $positionsPerLevel = max(1, $widthCount * $lengthCount);

        $location = $this->mapItemLocation($warehouse, $mapItem);

        if (! $location && $mapItem->location_id) {
            return;
        }

        $rack = $this->resolveRackLocationForMapItem($warehouse, $location, $mapItem);

        if (! $rack) {
            return;
        }

        if ((int) $mapItem->location_id !== (int) $rack->getKey()) {
            $mapItem->forceFill(['location_id' => $rack->getKey()])->save();
        }

        $leafType = $moduleType === 'pallet_rack' ? 'pallet_slot' : 'bin';
        $leafPrefix = $leafType === 'pallet_slot' ? 'S' : 'B';
        $leafNamePrefix = $leafType === 'pallet_slot' ? 'Pallet slot' : 'Bin';

        for ($index = 1; $index <= $levelCount; $index++) {
            $level = $this->firstOrCreateLocation(
                $warehouse,
                $rack,
                sprintf('%s-L%s', $rack->code, $index),
                'Level ' . $index,
                'level'
            );

            for ($position = 1; $position <= $positionsPerLevel; $position++) {
                $this->firstOrCreateLocation(
                    $warehouse,
                    $level,
                    sprintf('%s-%s%02d', $level->code, $leafPrefix, $position),
                    $leafNamePrefix . ' ' . $position,
                    $leafType
                );
            }
        }
    }

    protected function mapItemLocation(Warehouse $warehouse, WarehouseMapItem $mapItem): ?WarehouseLocation
    {
        if (! $mapItem->location_id) {
            return null;
        }

        return WarehouseLocation::query()
            ->whereKey($mapItem->location_id)
            ->where('warehouse_id', $warehouse->getKey())
            ->first();
    }

    protected function syncFloorPalletAreaLocationsForMapItem(Warehouse $warehouse, WarehouseMapItem $mapItem, array $meta): void
    {
        $location = $this->mapItemLocation($warehouse, $mapItem);

        if (! $location && $mapItem->location_id) {
            return;
        }

        $area = $this->resolvePalletAreaLocationForMapItem($warehouse, $location, $mapItem);

        if (! $area) {
            return;
        }

        if ((int) ($mapItem->location_id ?? 0) !== (int) $area->getKey()) {
            $mapItem->forceFill(['location_id' => $area->getKey()])->save();
        }

        $rowCount = max(1, (int) (Arr::get($meta, 'row_count') ?: Arr::get($meta, 'length_count') ?: 1));
        $columnCount = max(1, (int) (Arr::get($meta, 'column_count') ?: Arr::get($meta, 'width_count') ?: 1));
        $slotCount = max(1, $rowCount * $columnCount);

        for ($slot = 1; $slot <= $slotCount; $slot++) {
            $this->firstOrCreateLocation(
                $warehouse,
                $area,
                sprintf('%s-S%02d', $area->code, $slot),
                'Pallet slot ' . $slot,
                'pallet_slot'
            );
        }
    }

    protected function syncStandalonePalletSlotLocationForMapItem(Warehouse $warehouse, WarehouseMapItem $mapItem): void
    {
        if ($mapItem->location_id) {
            return;
        }

        $slot = $this->firstOrCreateLocation(
            $warehouse,
            null,
            $this->rackCodeForMapItem($mapItem),
            trim((string) ($mapItem->label ?: 'Pallet slot')),
            'pallet_slot'
        );

        $mapItem->forceFill(['location_id' => $slot->getKey()])->save();
    }

    protected function resolveRackLocationForMapItem(Warehouse $warehouse, ?WarehouseLocation $location, WarehouseMapItem $mapItem): ?WarehouseLocation
    {
        if ($location?->type === 'rack') {
            return $location;
        }

        if ($location?->type === 'level' && $location->parent_id) {
            return WarehouseLocation::query()
                ->whereKey($location->parent_id)
                ->where('warehouse_id', $warehouse->getKey())
                ->where('type', 'rack')
                ->first();
        }

        $baseCode = $this->rackCodeForMapItem($mapItem);
        $existing = WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('code', $baseCode)
            ->where('type', 'rack')
            ->first();

        if ($existing) {
            return $existing;
        }

        $parent = $location && ! in_array($location->type, ['level', 'bin', 'pallet_slot'], true) ? $location : null;

        return $this->firstOrCreateLocation(
            $warehouse,
            $parent,
            $baseCode,
            trim((string) ($mapItem->label ?: $baseCode)),
            'rack'
        );
    }

    protected function resolvePalletAreaLocationForMapItem(Warehouse $warehouse, ?WarehouseLocation $location, WarehouseMapItem $mapItem): ?WarehouseLocation
    {
        if ($location?->type === 'pallet_area') {
            return $location;
        }

        $baseCode = $this->rackCodeForMapItem($mapItem);
        $existing = WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('code', $baseCode)
            ->where('type', 'pallet_area')
            ->first();

        if ($existing) {
            return $existing;
        }

        $parent = $location && ! in_array($location->type, ['level', 'bin', 'pallet_slot'], true) ? $location : null;

        return $this->firstOrCreateLocation(
            $warehouse,
            $parent,
            $baseCode,
            trim((string) ($mapItem->label ?: $baseCode)),
            'pallet_area'
        );
    }

    protected function firstOrCreateLocation(Warehouse $warehouse, ?WarehouseLocation $parent, string $baseCode, string $name, string $type): WarehouseLocation
    {
        $parentId = $parent?->getKey();
        $code = $this->normalizeLocationCode($baseCode);
        $existing = WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('code', $code)
            ->first();

        if ($existing && (int) ($existing->parent_id ?? 0) === (int) ($parentId ?? 0) && $existing->type === $type) {
            return $existing;
        }

        if ($existing) {
            $code = $this->uniqueLocationCode($warehouse, $code, $parentId);
            $existing = WarehouseLocation::query()
                ->where('warehouse_id', $warehouse->getKey())
                ->where('code', $code)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return app(WarehouseLocationService::class)->create($warehouse, [
            'parent_id' => $parentId,
            'code' => $code,
            'name' => $name !== '' ? $name : $code,
            'type' => $type,
            'status' => true,
            'description' => 'Generated from warehouse map.',
        ]);
    }

    protected function rackCodeForMapItem(WarehouseMapItem $mapItem): string
    {
        $meta = $mapItem->meta_json ?: [];
        $source = Arr::get($meta, 'prefix') ?: $mapItem->label ?: ('RACK-' . $mapItem->getKey());

        return $this->normalizeLocationCode((string) $source);
    }

    protected function normalizeLocationCode(string $value): string
    {
        $code = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '-', trim($value)));
        $code = trim($code, '-');

        return $code !== '' ? mb_substr($code, 0, 48) : 'RACK';
    }

    protected function uniqueLocationCode(Warehouse $warehouse, string $baseCode, ?int $parentId = null): string
    {
        $baseCode = $this->normalizeLocationCode($baseCode);
        $existing = WarehouseLocation::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('code', $baseCode)
            ->first();

        if (! $existing) {
            return $baseCode;
        }

        $counter = 2;
        do {
            $code = mb_substr($baseCode, 0, 43) . '-' . $counter;
            $exists = WarehouseLocation::query()
                ->where('warehouse_id', $warehouse->getKey())
                ->where('code', $code)
                ->exists();
            $counter++;
        } while ($exists);

        return $code;
    }
}
