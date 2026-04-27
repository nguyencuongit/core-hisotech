<?php

namespace Botble\Inventory\Domains\Warehouse\Support;

use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Services\PalletService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseShowViewData
{
    public function __construct(protected Warehouse $warehouse, protected Collection $locations)
    {
    }

    public static function make(Warehouse $warehouse, Collection $locations): self
    {
        return new self($warehouse, $locations);
    }

    public function toArray(): array
    {
        $warehouseSetting = $this->warehouse->setting;
        $selectedMapId = (int) request('map_id');
        $selectedLocationId = (int) request('location_id');
        $selectedMap = $selectedMapId ? $this->warehouse->maps->firstWhere('id', $selectedMapId) : null;

        if (! $selectedMap && $selectedLocationId) {
            $selectedMap = $this->warehouse->maps->first(
                fn ($map) => $map->items->contains(fn ($item) => (int) $item->location_id === $selectedLocationId)
            );
        }

        $selectedMap ??= $this->warehouse->maps->first();
        $selectedMapItems = $selectedMap?->items ?? collect();
        $selectedLocation = $selectedLocationId ? $this->locations->firstWhere('id', $selectedLocationId) : $this->locations->first();
        $selectedLocationMapItem = $selectedLocation ? $selectedMapItems->firstWhere('location_id', $selectedLocation->getKey()) : null;
        $registry = WarehouseUiRegistry::class;
        $storageModeMeta = $registry::storageModeMeta();
        $selectedStorageMode = in_array((string) ($selectedMap?->storage_mode ?: ''), ['direct', 'pallet'], true)
            ? (string) $selectedMap->storage_mode
            : (($warehouseSetting?->use_pallet ?? false) ? 'pallet' : 'direct');

        $selectedMapBackgroundUrl = null;
        if ($selectedMap?->background_image) {
            try {
                $selectedMapBackgroundUrl = rv_media()->getImageUrl($selectedMap->background_image);
            } catch (\Throwable) {
                $selectedMapBackgroundUrl = $selectedMap->background_image;
            }
        }

        $setupState = [
            'has_settings' => (bool) $warehouseSetting,
            'has_locations' => $this->locations->isNotEmpty(),
            'has_maps' => $this->warehouse->maps->isNotEmpty(),
            'use_pallet' => (bool) (($warehouseSetting?->use_pallet ?? false)),
            'is_ready' => $this->locations->isNotEmpty() && $this->warehouse->maps->isNotEmpty(),
        ];

        $mapEditorTools = collect($this->mapEditorTools())
            ->filter(fn (array $tool): bool => in_array($selectedStorageMode, $tool['storage_modes'] ?? ['direct', 'pallet'], true))
            ->values()
            ->all();

        $selectedMapItemsPayload = $selectedMapItems->map(fn ($item) => [
            'id' => (int) $item->getKey(),
            'location_id' => $item->location_id ? (int) $item->location_id : null,
            'item_type' => (string) $item->item_type,
            'label' => (string) ($item->label ?: ''),
            'shape_type' => (string) ($item->shape_type ?: 'rect'),
            'x' => (float) $item->x,
            'y' => (float) $item->y,
            'width' => (float) $item->width,
            'height' => (float) $item->height,
            'rotation' => (float) ($item->rotation ?: 0),
            'color' => (string) ($item->color ?: '#e2e8f0'),
            'z_index' => (int) ($item->z_index ?: 0),
            'is_clickable' => (bool) $item->is_clickable,
            'meta_json' => $item->meta_json ?? [],
        ])->values();

        return [
            'locationMeta' => $registry::locationMeta(),
            'locationTypes' => collect($registry::locationMeta())->mapWithKeys(fn ($meta, $key) => [$key => $meta['label']])->all(),
            'storageModeMeta' => $storageModeMeta,
            'mapTypeMeta' => $registry::mapTypeMeta(),
            'mapEditorTools' => $mapEditorTools,
            'templates' => WarehouseTemplateRegistry::all(),
            'selectedMap' => $selectedMap,
            'selectedMapItems' => $selectedMapItems,
            'selectedLocation' => $selectedLocation,
            'selectedLocationMapItem' => $selectedLocationMapItem,
            'mapItemByLocation' => $this->warehouse->maps->flatMap(fn ($map) => $map->items)->filter(fn ($item) => ! empty($item->location_id))->keyBy('location_id'),
            'mapWidth' => max(1, (int) ($selectedMap?->width ?: 1200)),
            'mapHeight' => max(1, (int) ($selectedMap?->height ?: 800)),
            'systemLocationCount' => $this->locations->filter(fn ($location) => $location->isSystemLocation())->count(),
            'mappedLocationCount' => $selectedMapItems->pluck('location_id')->filter()->unique()->count(),
            'rootLocations' => $this->locations->whereNull('parent_id'),
            'warehouseSetting' => $warehouseSetting,
            'selectedStorageMode' => $selectedStorageMode,
            'mapBlueprints' => WarehouseMapBlueprints::all(),
            'pallets' => app(PalletService::class)->listByWarehouse($this->warehouse),
            'canManageMaps' => auth()->user()?->hasPermission('warehouse.maps.manage'),
            'selectedMapBackgroundUrl' => $selectedMapBackgroundUrl,
            'mapLocationOptions' => $this->mapLocationOptions(),
            'selectedMapItemsPayload' => $selectedMapItemsPayload,
            'mapLegendGroups' => $selectedMapItems->groupBy('item_type'),
            'linkedLocationCount' => $selectedMapItems->whereNotNull('location_id')->count(),
            'unlinkedLocationCount' => $selectedMapItems->whereNull('location_id')->count(),
            'setupCheckpoints' => $this->setupCheckpoints($setupState),
            'isWarehouseSetupReady' => $setupState['is_ready'],
            'tabs' => $this->tabs(),
            'activeTab' => request('tab', 'maps'),
            'mapEditorConfig' => $this->mapEditorConfig($selectedMap, $selectedMapItemsPayload, $mapEditorTools, $selectedStorageMode, $selectedLocation, $selectedLocationMapItem, $selectedMapBackgroundUrl),
            'selectedMapId' => $selectedMapId,
            'selectedLocationId' => $selectedLocationId,
        ];
    }

    protected function locationMeta(): array
    {
        return [
            'system' => ['label' => 'Hệ thống', 'icon' => 'ti ti-settings', 'badge' => 'bg-secondary-lt text-secondary', 'accent' => '#64748b'],
            'floor' => ['label' => 'Tầng', 'icon' => 'ti ti-layers-difference', 'badge' => 'bg-indigo-lt text-indigo', 'accent' => '#6366f1'],
            'zone' => ['label' => 'Khu vực', 'icon' => 'ti ti-layout-grid', 'badge' => 'bg-primary-lt text-primary', 'accent' => '#3b82f6'],
            'rack' => ['label' => 'Kệ', 'icon' => 'ti ti-package', 'badge' => 'bg-success-lt text-success', 'accent' => '#16a34a'],
            'level' => ['label' => 'Tầng kệ', 'icon' => 'ti ti-badge', 'badge' => 'bg-teal-lt text-teal', 'accent' => '#0f766e'],
            'bin' => ['label' => 'Ô chứa', 'icon' => 'ti ti-box', 'badge' => 'bg-cyan-lt text-cyan', 'accent' => '#0891b2'],
            'receiving' => ['label' => 'Nhận hàng', 'icon' => 'ti ti-inbox', 'badge' => 'bg-info-lt text-info', 'accent' => '#2563eb'],
            'waiting_putaway' => ['label' => 'Chờ xếp', 'icon' => 'ti ti-clock-hour-4', 'badge' => 'bg-primary-lt text-primary', 'accent' => '#7c3aed'],
            'qc_hold' => ['label' => 'Giữ QC', 'icon' => 'ti ti-shield-check', 'badge' => 'bg-warning-lt text-warning', 'accent' => '#d97706'],
            'damaged' => ['label' => 'Hàng lỗi', 'icon' => 'ti ti-alert-triangle', 'badge' => 'bg-danger-lt text-danger', 'accent' => '#dc2626'],
            'rejected' => ['label' => 'Từ chối', 'icon' => 'ti ti-ban', 'badge' => 'bg-danger-lt text-danger', 'accent' => '#b91c1c'],
            'return_area' => ['label' => 'Khu trả hàng', 'icon' => 'ti ti-rotate-clockwise', 'badge' => 'bg-secondary-lt text-secondary', 'accent' => '#475569'],
            'dispatch' => ['label' => 'Xuất hàng', 'icon' => 'ti ti-truck', 'badge' => 'bg-success-lt text-success', 'accent' => '#15803d'],
        ];
    }

    protected function storageModeMeta(): array
    {
        return [
            'direct' => ['label' => 'Hàng để trực tiếp', 'short_label' => 'Không pallet', 'description' => 'Hàng được lưu trực tiếp trong kệ, tầng hoặc ô chứa. Phù hợp kho nhỏ, shop, hàng lẻ.', 'icon' => 'ti ti-shelf'],
            'pallet' => ['label' => 'Hàng đặt trên pallet', 'short_label' => 'Có pallet', 'description' => 'Hàng nằm trên pallet, pallet nằm trong slot/rack. Phù hợp kho lớn, xe nâng, di chuyển nguyên pallet.', 'icon' => 'ti ti-stack-2'],
        ];
    }

    protected function mapTypeMeta(): array
    {
        return [
            'zone' => ['label' => 'Khu vực', 'icon' => 'ti ti-layout-grid', 'accent' => '#2563eb', 'color' => '#dbeafe'],
            'receiving_area' => ['label' => 'Khu nhận hàng', 'icon' => 'ti ti-inbox', 'accent' => '#2563eb', 'color' => '#dbeafe'],
            'qc_area' => ['label' => 'Khu QC', 'icon' => 'ti ti-shield-check', 'accent' => '#d97706', 'color' => '#fef3c7'],
            'staging_area' => ['label' => 'Khu chờ xếp', 'icon' => 'ti ti-hourglass', 'accent' => '#7c3aed', 'color' => '#ede9fe'],
            'dispatch_area' => ['label' => 'Khu xuất hàng', 'icon' => 'ti ti-truck-delivery', 'accent' => '#15803d', 'color' => '#dcfce7'],
            'aisle' => ['label' => 'Lối đi', 'icon' => 'ti ti-route', 'accent' => '#64748b', 'color' => '#e2e8f0'],
            'dock' => ['label' => 'Cửa kho', 'icon' => 'ti ti-building-warehouse', 'accent' => '#4f46e5', 'color' => '#c7d2fe'],
            'rack' => ['label' => 'Kệ / Rack', 'icon' => 'ti ti-package', 'accent' => '#16a34a', 'color' => '#bbf7d0'],
            'simple_shelf' => ['label' => 'Kệ', 'icon' => 'ti ti-shelf', 'accent' => '#0ea5e9', 'color' => '#e0f2fe'],
            'pallet_rack' => ['label' => 'Rack pallet', 'icon' => 'ti ti-stack-2', 'accent' => '#0f766e', 'color' => '#dcfce7'],
            'pallet_slot' => ['label' => 'Slot pallet', 'icon' => 'ti ti-square', 'accent' => '#047857', 'color' => '#bbf7d0'],
            'floor_pallet_area' => ['label' => 'Khu pallet sàn', 'icon' => 'ti ti-grid-dots', 'accent' => '#9333ea', 'color' => '#ede9fe'],
            'bin_area' => ['label' => 'Khu ngoại lệ', 'icon' => 'ti ti-alert-triangle', 'accent' => '#dc2626', 'color' => '#fecaca'],
            'label' => ['label' => 'Text / Label', 'icon' => 'ti ti-text-size', 'accent' => '#334155', 'color' => '#ffffff'],
        ];
    }

    protected function mapEditorTools(): array
    {
        return [
            'receiving_area' => ['label' => 'Khu nhận hàng', 'icon' => 'ti ti-inbox', 'item_type' => 'receiving_area', 'shape_type' => 'rect', 'width' => 220, 'height' => 120, 'color' => '#dbeafe', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'receiving_area', 'stockable' => false]],
            'qc_area' => ['label' => 'Khu QC', 'icon' => 'ti ti-shield-check', 'item_type' => 'qc_area', 'shape_type' => 'rect', 'width' => 200, 'height' => 110, 'color' => '#fef3c7', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'qc_area', 'stockable' => false]],
            'staging_area' => ['label' => 'Khu chờ xếp', 'icon' => 'ti ti-hourglass', 'item_type' => 'staging_area', 'shape_type' => 'rect', 'width' => 220, 'height' => 120, 'color' => '#ede9fe', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'staging_area', 'stockable' => false]],
            'zone' => ['label' => 'Khu lưu hàng', 'icon' => 'ti ti-layout-grid', 'item_type' => 'zone', 'shape_type' => 'rect', 'width' => 240, 'height' => 150, 'color' => '#dbeafe', 'storage_modes' => ['direct'], 'meta_json' => ['module_type' => 'zone', 'stockable' => true, 'storage_mode' => 'direct']],
            'aisle' => ['label' => 'Lối đi', 'icon' => 'ti ti-route', 'item_type' => 'aisle', 'shape_type' => 'rect', 'width' => 96, 'height' => 384, 'color' => '#e2e8f0', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'aisle', 'stockable' => false, 'width_count' => 1, 'length_count' => 4]],
            'simple_shelf' => ['label' => 'Kệ', 'icon' => 'ti ti-shelf', 'item_type' => 'simple_shelf', 'shape_type' => 'rect', 'width' => 256, 'height' => 112, 'color' => '#e0f2fe', 'storage_modes' => ['direct'], 'meta_json' => ['module_type' => 'simple_shelf', 'storage_mode' => 'direct', 'prefix' => 'SHELF-A', 'width_count' => 1, 'height_count' => 2, 'length_count' => 4, 'uses_pallet' => false]],
            'pallet_rack' => ['label' => 'Rack pallet 4 x 4', 'icon' => 'ti ti-stack-2', 'item_type' => 'pallet_rack', 'shape_type' => 'rect', 'width' => 320, 'height' => 150, 'color' => '#dcfce7', 'storage_modes' => ['pallet'], 'meta_json' => ['module_type' => 'pallet_rack', 'storage_mode' => 'pallet', 'prefix' => 'RACK-A', 'level_count' => 4, 'positions_per_level' => 4, 'pallets_per_position' => 1]],
            'pallet_slot' => ['label' => 'Slot pallet', 'icon' => 'ti ti-square', 'item_type' => 'pallet_slot', 'shape_type' => 'rect', 'width' => 110, 'height' => 80, 'color' => '#bbf7d0', 'storage_modes' => ['pallet'], 'meta_json' => ['module_type' => 'pallet_slot', 'storage_mode' => 'pallet', 'pallets_per_position' => 1]],
            'floor_pallet_area' => ['label' => 'Khu pallet sàn', 'icon' => 'ti ti-grid-dots', 'item_type' => 'floor_pallet_area', 'shape_type' => 'rect', 'width' => 260, 'height' => 150, 'color' => '#ede9fe', 'storage_modes' => ['pallet'], 'meta_json' => ['module_type' => 'floor_pallet_area', 'storage_mode' => 'pallet', 'prefix' => 'PALLET-A', 'row_count' => 4, 'column_count' => 4, 'pallets_per_position' => 1]],
            'label' => ['label' => 'Text / Label', 'icon' => 'ti ti-text-size', 'item_type' => 'label', 'shape_type' => 'label', 'width' => 180, 'height' => 52, 'color' => '#0f172a', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'label', 'stockable' => false]],
        ];
    }

    protected function mapLocationOptions(): array
    {
        return $this->locations->map(fn (WarehouseLocation $location) => [
            'id' => (int) $location->getKey(),
            'label' => $location->displayLabel(),
            'path' => $location->path,
            'type' => $location->type,
            'status' => (bool) $location->status,
            'is_stockable' => in_array($location->type, PalletLocationRules::allowedTypes(), true),
        ])->values()->all();
    }

    protected function setupCheckpoints(array $setupState): array
    {
        return [
            ['key' => 'settings', 'done' => $setupState['has_settings'], 'title' => 'Bước 1 · Cài đặt kho', 'description' => 'Bật QC, pallet, batch/serial và sơ đồ theo đúng mô hình kho thực tế.'],
            ['key' => 'locations', 'done' => $setupState['has_locations'], 'title' => 'Bước 2 · Cây vị trí', 'description' => 'Tạo zone, rack, level, bin để hệ thống có cấu trúc địa chỉ lưu hàng chuẩn.'],
            ['key' => 'maps', 'done' => $setupState['has_maps'], 'title' => 'Bước 3 · Sơ đồ kho', 'description' => 'Kéo thả vùng, gắn location, nhìn bố cục kho trực quan và dễ thao tác.'],
            ['key' => 'pallets', 'done' => $setupState['use_pallet'], 'title' => 'Bước 4 · Pallet', 'description' => 'Nếu kho dùng pallet, mở tab pallet để tạo pallet và đặt pallet vào đúng location.', 'optional' => true],
        ];
    }

    protected function tabs(): array
    {
        return [
            'overview' => 'Tổng quan',
            'settings' => 'Cài đặt kho',
            'locations' => 'Cây vị trí',
            'maps' => 'Sơ đồ kho',
            'products' => 'Sản phẩm',
            'policies' => 'Chính sách',
            'pallets' => 'Pallet',
        ];
    }

    protected function isWarehouseSetupReady($warehouseSetting): bool
    {
        return $this->locations->isNotEmpty() && $this->warehouse->maps->isNotEmpty();
    }

    protected function mapEditorConfig($selectedMap, Collection $selectedMapItemsPayload, array $mapEditorTools, string $selectedStorageMode, $selectedLocation, $selectedLocationMapItem, ?string $selectedMapBackgroundUrl): array
    {
        return [
            'map' => $selectedMap ? [
                'id' => (int) $selectedMap->getKey(),
                'name' => $selectedMap->name,
                'width' => max(1, (int) ($selectedMap->width ?: 1200)),
                'height' => max(1, (int) ($selectedMap->height ?: 800)),
                'storage_mode' => $selectedStorageMode,
                'background_url' => $selectedMapBackgroundUrl,
                'sync_url' => auth()->user()?->hasPermission('warehouse.maps.manage') ? route('inventory.warehouse.maps.sync', [$this->warehouse, $selectedMap]) : null,
            ] : null,
            'items' => $selectedMapItemsPayload,
            'tools' => collect($mapEditorTools)->map(fn ($tool, $key) => $tool + ['key' => $key])->values(),
            'meta' => $this->mapTypeMeta(),
            'locations' => $this->mapLocationOptions(),
            'can_manage' => (bool) auth()->user()?->hasPermission('warehouse.maps.manage'),
            'selected_item_id' => $selectedLocationMapItem?->getKey(),
            'selected_location_id' => $selectedLocation?->getKey(),
        ];
    }
}
