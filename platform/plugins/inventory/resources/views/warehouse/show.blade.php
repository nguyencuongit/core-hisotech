@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $locationMeta = [
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

        $locationTypes = collect($locationMeta)->mapWithKeys(fn ($meta, $key) => [$key => $meta['label']])->all();
        $mapTypeMeta = [
            'zone' => ['label' => 'Khu vực', 'icon' => 'ti ti-layout-grid', 'accent' => '#2563eb', 'color' => '#dbeafe'],
            'receiving_area' => ['label' => 'Khu nhận hàng', 'icon' => 'ti ti-inbox', 'accent' => '#2563eb', 'color' => '#dbeafe'],
            'qc_area' => ['label' => 'Khu QC', 'icon' => 'ti ti-shield-check', 'accent' => '#d97706', 'color' => '#fef3c7'],
            'staging_area' => ['label' => 'Khu chờ xếp', 'icon' => 'ti ti-hourglass', 'accent' => '#7c3aed', 'color' => '#ede9fe'],
            'dispatch_area' => ['label' => 'Khu xuất hàng', 'icon' => 'ti ti-truck-delivery', 'accent' => '#15803d', 'color' => '#dcfce7'],
            'aisle' => ['label' => 'Lối đi', 'icon' => 'ti ti-route', 'accent' => '#64748b', 'color' => '#e2e8f0'],
            'dock' => ['label' => 'Cửa kho', 'icon' => 'ti ti-building-warehouse', 'accent' => '#4f46e5', 'color' => '#c7d2fe'],
            'rack' => ['label' => 'Kệ / Rack', 'icon' => 'ti ti-package', 'accent' => '#16a34a', 'color' => '#bbf7d0'],
            'simple_shelf' => ['label' => 'Kệ đơn giản', 'icon' => 'ti ti-shelf', 'accent' => '#0ea5e9', 'color' => '#e0f2fe'],
            'pallet_rack' => ['label' => 'Rack pallet', 'icon' => 'ti ti-stack-2', 'accent' => '#0f766e', 'color' => '#dcfce7'],
            'floor_pallet_area' => ['label' => 'Khu pallet sàn', 'icon' => 'ti ti-grid-dots', 'accent' => '#9333ea', 'color' => '#ede9fe'],
            'bin_area' => ['label' => 'Khu ngoại lệ', 'icon' => 'ti ti-alert-triangle', 'accent' => '#dc2626', 'color' => '#fecaca'],
            'label' => ['label' => 'Text / Label', 'icon' => 'ti ti-text-size', 'accent' => '#334155', 'color' => '#ffffff'],
        ];
        $mapEditorTools = [
            'zone' => ['label' => 'Khu vực', 'icon' => 'ti ti-layout-grid', 'item_type' => 'zone', 'shape_type' => 'rect', 'width' => 220, 'height' => 140, 'color' => '#dbeafe', 'meta_json' => ['module_type' => 'zone', 'stockable' => true]],
            'aisle' => ['label' => 'Đường đi', 'icon' => 'ti ti-route', 'item_type' => 'aisle', 'shape_type' => 'rect', 'width' => 120, 'height' => 280, 'color' => '#e2e8f0', 'meta_json' => ['module_type' => 'aisle', 'stockable' => false]],
            'simple_shelf' => ['label' => 'Kệ đơn giản', 'icon' => 'ti ti-shelf', 'item_type' => 'simple_shelf', 'shape_type' => 'rect', 'width' => 220, 'height' => 120, 'color' => '#e0f2fe', 'meta_json' => ['module_type' => 'simple_shelf', 'prefix' => 'SHELF-A', 'level_count' => 3, 'bin_count_per_level' => 4, 'uses_pallet' => false]],
            'pallet_rack' => ['label' => 'Rack pallet', 'icon' => 'ti ti-stack-2', 'item_type' => 'pallet_rack', 'shape_type' => 'rect', 'width' => 300, 'height' => 140, 'color' => '#dcfce7', 'meta_json' => ['module_type' => 'pallet_rack', 'prefix' => 'RACK-A01', 'bay_count' => 5, 'level_count' => 4, 'positions_per_level' => 2]],
            'floor_pallet_area' => ['label' => 'Khu pallet sàn', 'icon' => 'ti ti-grid-dots', 'item_type' => 'floor_pallet_area', 'shape_type' => 'rect', 'width' => 240, 'height' => 140, 'color' => '#ede9fe', 'meta_json' => ['module_type' => 'floor_pallet_area', 'prefix' => 'PALLET-A', 'row_count' => 4, 'column_count' => 5]],
            'label' => ['label' => 'Text / Label', 'icon' => 'ti ti-text-size', 'item_type' => 'label', 'shape_type' => 'label', 'width' => 180, 'height' => 52, 'color' => '#0f172a', 'meta_json' => ['module_type' => 'label', 'stockable' => false]],
        ];
        $templates = \Botble\Inventory\Domains\Warehouse\Support\WarehouseTemplateRegistry::all();
        $selectedMapId = (int) request('map_id');
        $selectedMap = $warehouse->maps->firstWhere('id', $selectedMapId) ?: $warehouse->maps->first();
        $selectedMapItems = $selectedMap?->items ?? collect();
        $selectedLocationId = (int) request('location_id');
        $selectedLocation = $selectedLocationId ? $locations->firstWhere('id', $selectedLocationId) : $locations->first();
        $selectedLocationMapItem = $selectedLocation ? $selectedMapItems->firstWhere('location_id', $selectedLocation->getKey()) : null;
        $mapItemByLocation = $mapItemByLocation ?? collect();
        $mapWidth = max(1, (int) ($selectedMap?->width ?: 1200));
        $mapHeight = max(1, (int) ($selectedMap?->height ?: 800));
        $systemLocationCount = $locations->filter(fn ($location) => $location->isSystemLocation())->count();
        $mappedLocationCount = $selectedMapItems->pluck('location_id')->filter()->unique()->count();
        $rootLocations = $locations->whereNull('parent_id');
        $warehouseSetting = $warehouse->setting;
        $pallets = app(\Botble\Inventory\Domains\Warehouse\Services\PalletService::class)->listByWarehouse($warehouse);
        $canManageMaps = auth()->user()?->hasPermission('warehouse.maps.manage');
        $selectedMapBackgroundUrl = null;

        if ($selectedMap?->background_image) {
            try {
                $selectedMapBackgroundUrl = rv_media()->getImageUrl($selectedMap->background_image);
            } catch (\Throwable) {
                $selectedMapBackgroundUrl = $selectedMap->background_image;
            }
        }

        $mapLocationOptions = $locations->map(fn ($location) => [
            'id' => (int) $location->getKey(),
            'label' => $location->displayLabel(),
            'path' => $location->path,
            'type' => $location->type,
            'status' => (bool) $location->status,
            'is_stockable' => in_array($location->type, \Botble\Inventory\Domains\Warehouse\Support\PalletLocationRules::allowedTypes(), true),
        ])->values();

        $selectedMapItemsPayload = $selectedMapItems->map(function ($item) {
            return [
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
            ];
        })->values();

        $mapLegendGroups = $selectedMapItems->groupBy('item_type');
        $linkedLocationCount = $selectedMapItems->whereNotNull('location_id')->count();
        $unlinkedLocationCount = $selectedMapItems->whereNull('location_id')->count();
        $tabs = [
            'overview' => 'Tổng quan',
            'settings' => 'Cài đặt kho',
            'locations' => 'Cây vị trí',
            'maps' => 'Sơ đồ kho',
            'products' => 'Sản phẩm',
            'policies' => 'Chính sách',
            'pallets' => 'Pallet',
        ];
        $activeTab = request('tab', 'overview');
        $mapEditorConfig = [
            'map' => $selectedMap ? [
                'id' => (int) $selectedMap->getKey(),
                'name' => $selectedMap->name,
                'width' => $mapWidth,
                'height' => $mapHeight,
                'background_url' => $selectedMapBackgroundUrl,
                'sync_url' => $canManageMaps ? route('inventory.warehouse.maps.sync', [$warehouse, $selectedMap]) : null,
            ] : null,
            'items' => $selectedMapItemsPayload,
            'tools' => collect($mapEditorTools)->map(fn ($tool, $key) => $tool + ['key' => $key])->values(),
            'meta' => $mapTypeMeta,
            'locations' => $mapLocationOptions,
            'can_manage' => (bool) $canManageMaps,
            'selected_item_id' => $selectedLocationMapItem?->getKey(),
            'selected_location_id' => $selectedLocation?->getKey(),
        ];
        $mapEditorConfigJson = json_encode(
            $mapEditorConfig,
            JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_APOS
                | JSON_HEX_AMP
                | JSON_HEX_QUOT
                | JSON_INVALID_UTF8_SUBSTITUTE
        ) ?: 'null';
    @endphp

    <style>
        .warehouse-workspace { margin: -1.25rem; min-height: calc(100vh - 56px); padding: 28px; background: #f8fafc; }
        .warehouse-shell { display: grid; gap: 18px; }
        .warehouse-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; box-shadow: 0 18px 44px rgba(15, 23, 42, .05); overflow: hidden; }
        .warehouse-card__header { padding: 22px 22px 0; }
        .warehouse-card__body { padding: 22px; }
        .warehouse-card__title { margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; }
        .warehouse-card__hint { margin-top: 6px; color: #64748b; }
        .warehouse-hero { padding: 28px; }
        .warehouse-hero__grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, .8fr); gap: 20px; align-items: start; }
        .warehouse-kicker { color: #6366f1; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .warehouse-title { margin: 8px 0 10px; font-size: 30px; font-weight: 850; color: #111827; }
        .warehouse-subtitle { color: #64748b; max-width: 72ch; }
        .warehouse-header-meta,
        .warehouse-actions,
        .warehouse-preset-grid,
        .warehouse-tree-list,
        .warehouse-map-legend,
        .warehouse-map-stats,
        .warehouse-map-switcher,
        .warehouse-map-blueprints,
        .warehouse-editor-toolbar { display: grid; gap: 10px; }
        .warehouse-header-meta { grid-auto-flow: column; grid-auto-columns: max-content; justify-content: start; margin-top: 16px; }
        .warehouse-metrics .warehouse-metric { background: #f9fafb; border: 1px solid #edf0f6; border-radius: 20px; padding: 16px; }
        .warehouse-metric span { color: #64748b; display: block; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .warehouse-metric strong { color: #111827; font-size: 24px; }
        .warehouse-tabs { display: flex; flex-wrap: wrap; gap: 8px; padding: 0 0 8px; }
        .warehouse-tab { border: 1px solid #e5e7eb; background: #fff; border-radius: 999px; padding: 10px 14px; color: #334155; font-weight: 700; text-decoration: none; transition: .2s ease; }
        .warehouse-tab.is-active,
        .warehouse-tab:hover { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .warehouse-panel { display: none; }
        .warehouse-panel.is-active { display: block; }
        .warehouse-side-column { display: grid; gap: 18px; position: sticky; top: 16px; }
        .warehouse-preset-btn,
        .warehouse-template-card { text-align: left; border: 1px solid #e5e7eb; border-radius: 18px; padding: 14px; background: #fff; display: grid; gap: 4px; }
        .warehouse-template-card { gap: 12px; height: 100%; }
        .warehouse-template-card__tree { margin: 0; padding-left: 18px; color: #64748b; font-size: 13px; }
        .warehouse-template-card__tree li + li { margin-top: 4px; }
        .warehouse-location-form .form-control,
        .warehouse-location-form .form-select { border-radius: 14px; min-height: 42px; }
        .warehouse-system-note { padding: 14px; border-radius: 16px; background: #f8fafc; border: 1px dashed #dbe4f0; color: #475569; font-size: 13px; }
        .warehouse-tree-node { display: grid; gap: 12px; padding: 14px; border-radius: 20px; border: 1px solid #e5e7eb; background: #fff; transition: .2s ease; }
        .warehouse-tree-node.is-active { border-color: rgba(79, 70, 229, .42); box-shadow: 0 14px 28px rgba(79, 70, 229, .10); }
        .warehouse-tree-main { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 12px; align-items: start; cursor: pointer; }
        .warehouse-tree-icon { width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--tree-accent) 12%, #fff); color: var(--tree-accent); font-size: 1.15rem; }
        .warehouse-tree-title-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .warehouse-tree-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; color: #64748b; font-size: .84rem; }
        .warehouse-tree-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .warehouse-tree-children { display: grid; gap: 10px; margin-top: 10px; padding-left: 18px; border-left: 2px dashed rgba(148, 163, 184, .32); }
        .warehouse-empty-state { padding: 28px; border: 1px dashed #d8e1ee; border-radius: 22px; background: #f8fafc; text-align: center; color: #64748b; }
        .warehouse-map-editor { display: grid; grid-template-columns: 220px minmax(0, 1fr) 320px; grid-template-areas: "tools canvas tree" "tools canvas inspector"; gap: 14px; align-items: start; }
        .warehouse-map-editor.is-view-mode { grid-template-columns: minmax(0, 1fr) 320px; grid-template-areas: "canvas tree"; }
        .warehouse-map-editor.is-init-mode { grid-template-columns: 1fr; grid-template-areas: "canvas"; }
        .warehouse-map-editor.is-view-mode .warehouse-map-toolbox,
        .warehouse-map-editor.is-view-mode .warehouse-map-inspector,
        .warehouse-map-editor.is-view-mode [data-edit-only],
        .warehouse-map-editor.is-edit-mode [data-view-only],
        .warehouse-map-editor.is-init-mode .warehouse-map-toolbox,
        .warehouse-map-editor.is-init-mode .warehouse-map-sidebar,
        .warehouse-map-editor.is-init-mode .warehouse-map-inspector,
        .warehouse-map-editor.is-init-mode .warehouse-map-canvas-panel__header,
        .warehouse-map-editor.is-init-mode [data-edit-only],
        .warehouse-map-editor.is-init-mode [data-view-only],
        .warehouse-map-editor.is-init-mode [data-map-editor-only] { display: none !important; }
        .warehouse-map-editor.is-init-mode .warehouse-map-canvas-panel { max-width: 1180px; margin: 0 auto; width: 100%; }
        .warehouse-map-toolbox,
        .warehouse-map-sidebar,
        .warehouse-map-canvas-panel,
        .warehouse-map-inspector { background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; box-shadow: 0 18px 44px rgba(15, 23, 42, .04); }
        .warehouse-map-toolbox { grid-area: tools; position: sticky; top: 16px; }
        .warehouse-map-sidebar { grid-area: tree; }
        .warehouse-map-canvas-panel { grid-area: canvas; }
        .warehouse-map-inspector { grid-area: inspector; }
        .warehouse-map-toolbox,
        .warehouse-map-sidebar,
        .warehouse-map-canvas-panel { overflow: hidden; }
        .warehouse-map-toolbox__header,
        .warehouse-map-sidebar__header,
        .warehouse-map-canvas-panel__header,
        .warehouse-map-inspector__header { padding: 14px 14px 0; }
        .warehouse-map-toolbox__body,
        .warehouse-map-sidebar__body,
        .warehouse-map-canvas-panel__body,
        .warehouse-map-inspector__body { padding: 14px; }
        .warehouse-map-toolbox__body { display: grid; gap: 12px; }
        .warehouse-map-canvas-panel__header,
        .warehouse-map-canvas-panel__body { padding-left: 18px; padding-right: 18px; }
        .warehouse-map-sidebar__body { max-height: 360px; overflow: auto; }
        .warehouse-map-sidebar .warehouse-tree-actions { display: none; }
        .warehouse-map-sidebar .warehouse-tree-node { padding: 10px; border-radius: 16px; }
        .warehouse-map-sidebar .warehouse-tree-icon { width: 34px; height: 34px; border-radius: 12px; font-size: 1rem; }
        .warehouse-map-sidebar .warehouse-tree-meta { font-size: .76rem; }
        .warehouse-map-sidebar .warehouse-tree-title-row { gap: 6px; }
        .warehouse-map-sidebar .badge { font-size: .68rem; }
        .warehouse-editor-toolbar { gap: 12px; }
        .warehouse-editor-toolbar__row { display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .warehouse-map-canvas-panel__header .warehouse-editor-toolbar__row:has([data-map-toolbox]) { display: none; }
        .warehouse-editor-toolbar__group { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .warehouse-map-toolbox .warehouse-editor-toolbar__group { display: grid; gap: 8px; }
        .warehouse-editor-tool { border: 1px solid #dbe4f0; background: #fff; border-radius: 16px; padding: 10px 12px; font-weight: 700; color: #0f172a; display: inline-flex; gap: 8px; align-items: center; cursor: grab; }
        .warehouse-map-toolbox .warehouse-editor-tool { width: 100%; justify-content: flex-start; }
        .warehouse-editor-tool:hover { border-color: #c7d2fe; box-shadow: 0 10px 20px rgba(79, 70, 229, .08); }
        .warehouse-editor-tool.is-active,
        .warehouse-editor-action.is-active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
        .warehouse-editor-action { border-radius: 14px; min-height: 40px; }
        .warehouse-map-editor-state { display: inline-flex; align-items: center; gap: 8px; padding: 7px 11px; border-radius: 999px; background: #eef2ff; color: #4f46e5; font-size: 12px; font-weight: 800; }
        .warehouse-map-editor-summary { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; color: #64748b; font-size: 12px; }
        .warehouse-map-editor-summary strong { color: #0f172a; }
        .warehouse-map-header-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .warehouse-map-guide { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 12px 0 0; }
        .warehouse-map-guide__item { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 8px; align-items: start; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 16px; background: #f8fafc; color: #475569; font-size: 12px; }
        .warehouse-map-guide__item i { color: #4f46e5; font-size: 18px; margin-top: 1px; }
        .warehouse-map-guide__item strong { display: block; color: #0f172a; font-size: 12px; }
        .warehouse-map-stats { display: flex; gap: 8px; flex-wrap: wrap; }
        .warehouse-map-stat { border: 1px solid #e5e7eb; border-radius: 16px; padding: 10px 12px; background: #f8fafc; }
        .warehouse-map-stat span { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .warehouse-map-stat strong { margin-left: 6px; font-size: 16px; color: #0f172a; }
        .warehouse-map-status { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .warehouse-map-status .badge { border-radius: 999px; }
        .warehouse-map-stage { border: 1px solid #e2e8f0; border-radius: 24px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 14px; }
        .warehouse-map-viewport { position: relative; overflow: auto; width: 100%; min-height: 760px; height: calc(100vh - 250px); max-height: 1080px; border-radius: 22px; border: 1px solid rgba(148, 163, 184, .24); background: #eff6ff; }
        .warehouse-map-canvas-wrap { position: relative; transform-origin: top left; transition: transform .16s ease; }
        .warehouse-map-canvas { position: relative; background-size: 32px 32px, 32px 32px, cover; background-position: 0 0, 0 0, center; background-repeat: repeat, repeat, no-repeat; }
        .warehouse-map-canvas.is-draw-mode { cursor: crosshair; }
        .warehouse-map-canvas::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255, 255, 255, .12) 0%, rgba(255, 255, 255, .28) 100%); pointer-events: none; }
        .warehouse-map-drop-hint { position: absolute; inset: 16px auto auto 16px; z-index: 5; padding: 8px 12px; border-radius: 999px; background: rgba(15, 23, 42, .74); color: #fff; font-size: 12px; font-weight: 700; box-shadow: 0 10px 24px rgba(15, 23, 42, .18); }
        .warehouse-map-editor.is-view-mode .warehouse-map-drop-hint { display: none; }
        .warehouse-map-canvas__item { position: absolute; display: flex; align-items: flex-end; justify-content: flex-start; padding: 12px; border-radius: 18px; border: 2px solid rgba(15, 23, 42, .08); box-shadow: 0 10px 24px rgba(15, 23, 42, .08); color: #0f172a; text-align: left; cursor: pointer; user-select: none; transition: box-shadow .16s ease, border-color .16s ease, transform .16s ease; }
        .warehouse-map-canvas__item.is-selected { border-color: #4f46e5; box-shadow: 0 16px 32px rgba(79, 70, 229, .16); }
        .warehouse-map-canvas__item.is-linked::after { content: ''; position: absolute; top: 10px; right: 10px; width: 10px; height: 10px; border-radius: 999px; background: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, .16); }
        .warehouse-map-canvas__item--label { align-items: center; justify-content: center; background: transparent !important; border-style: dashed; box-shadow: none; }
        .warehouse-map-canvas__content { display: grid; gap: 4px; position: relative; z-index: 2; }
        .warehouse-map-canvas__title { display: flex; gap: 8px; align-items: center; font-weight: 800; }
        .warehouse-map-canvas__meta { color: rgba(15, 23, 42, .72); font-size: 12px; font-weight: 700; }
        .warehouse-map-canvas__handle { position: absolute; right: -8px; bottom: -8px; width: 18px; height: 18px; border-radius: 999px; background: #4f46e5; border: 3px solid #fff; box-shadow: 0 8px 16px rgba(79, 70, 229, .24); cursor: nwse-resize; z-index: 3; }
        .warehouse-map-canvas__selection { position: absolute; border: 2px dashed rgba(79, 70, 229, .42); border-radius: 18px; pointer-events: none; display: none; }
        .warehouse-map-canvas__selection.is-visible { display: block; }
        .warehouse-map-legend { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .warehouse-map-legend-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 16px; border: 1px solid #e5e7eb; background: #fff; }
        .warehouse-map-legend-item span { display: inline-flex; gap: 8px; align-items: center; color: #334155; font-weight: 700; }
        .warehouse-map-inspector__body { display: grid; gap: 10px; max-height: 520px; overflow: auto; }
        .warehouse-map-inspector__section { display: grid; gap: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 16px; background: #fff; }
        .warehouse-map-inspector__section h4 { margin: 0; font-size: 13px; font-weight: 800; color: #0f172a; }
        .warehouse-map-inspector__section small { color: #64748b; }
        .warehouse-field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .warehouse-field-grid--single { grid-template-columns: 1fr; }
        .warehouse-map-inspector label { display: grid; gap: 6px; color: #334155; font-size: 12px; font-weight: 700; }
        .warehouse-map-inspector .form-control,
        .warehouse-map-inspector .form-select { border-radius: 14px; min-height: 42px; }
        .warehouse-map-inspector__capacity { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .warehouse-map-inspector__empty { padding: 18px; border-radius: 18px; border: 1px dashed #dbe4f0; background: #f8fafc; color: #64748b; }
        .warehouse-map-inspector__divider { height: 1px; background: #eef2f7; }
        .warehouse-inline-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .warehouse-inline-form .form-control,
        .warehouse-inline-form .form-select { min-height: 40px; border-radius: 14px; }
        .warehouse-map-mini-empty { padding: 18px; border-radius: 18px; background: #f8fafc; border: 1px dashed #dbe4f0; color: #64748b; }
        .warehouse-map-toolbar-card { display: grid; gap: 12px; padding: 16px; border-radius: 18px; border: 1px solid #e5e7eb; background: #fff; }
        .warehouse-map-init { display: grid; gap: 18px; padding: 24px; border: 1px solid #e5e7eb; border-radius: 24px; background: linear-gradient(135deg, #fff 0%, #f8fafc 100%); }
        .warehouse-map-init__actions { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .warehouse-map-init__templates { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .warehouse-map-template-preview { min-height: 92px; border-radius: 16px; border: 1px dashed #cbd5e1; background: linear-gradient(90deg, rgba(79, 70, 229, .10) 25%, transparent 25%), linear-gradient(180deg, rgba(16, 185, 129, .10) 33%, transparent 33%); background-size: 52px 52px; }
        .warehouse-map-view-detail { display: grid; gap: 4px; padding: 12px 14px; border-radius: 16px; background: #f8fafc; border: 1px solid #e5e7eb; color: #475569; }
        .warehouse-map-view-detail strong { color: #0f172a; }
        .warehouse-map-preview-list { display: grid; gap: 10px; }
        .warehouse-map-preview-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; border: 1px solid #e5e7eb; border-radius: 16px; padding: 10px 12px; }
        .warehouse-map-preview-item span { color: #334155; font-weight: 700; }
        .warehouse-hidden { display: none !important; }

        @media (max-width: 1480px) {
            .warehouse-map-editor { grid-template-columns: 200px minmax(0, 1fr) 300px; }
            .warehouse-map-editor.is-view-mode { grid-template-columns: minmax(0, 1fr) 300px; }
            .warehouse-map-guide { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 1200px) {
            .warehouse-hero__grid,
            .warehouse-map-editor,
            .warehouse-map-editor.is-view-mode { grid-template-columns: 1fr; grid-template-areas: "tools" "canvas" "tree" "inspector"; }

            .warehouse-side-column { position: static; }
            .warehouse-map-toolbox { position: static; }
            .warehouse-map-stats { grid-template-columns: 1fr; }
            .warehouse-map-legend { grid-template-columns: 1fr; }
            .warehouse-map-sidebar__body,
            .warehouse-map-inspector__body { max-height: none; }
            .warehouse-map-init__templates { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .warehouse-workspace { margin: -1rem; padding: 16px; }
            .warehouse-field-grid { grid-template-columns: 1fr; }
            .warehouse-map-guide { grid-template-columns: 1fr; }
            .warehouse-map-viewport { min-height: 540px; height: 70vh; }
        }
    </style>

    <div class="warehouse-workspace">
        <div class="container-fluid warehouse-shell">
            <section class="warehouse-card warehouse-hero">
                <div class="warehouse-hero__grid">
                    <div>
                        <div class="warehouse-kicker">Quản lý kho</div>
                        <h1 class="warehouse-title">{{ $warehouse->name }}</h1>
                        <div class="warehouse-subtitle">
                            Theo dõi cây vị trí, sơ đồ kho và các khu vận hành trên cùng một màn hình.
                            Mục tiêu là giúp người dùng nhìn ra bố cục kho ngay, không phải suy luận từ danh sách kỹ thuật.
                        </div>
                        <div class="warehouse-header-meta">
                            <span class="badge bg-light text-dark"><i class="ti ti-building-warehouse me-1"></i>{{ $warehouse->code }}</span>
                            @if($warehouse->address)
                                <span class="badge bg-light text-dark"><i class="ti ti-map-pin me-1"></i>{{ $warehouse->address }}</span>
                            @endif
                            <span class="badge {{ $warehouse->status ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                {{ $warehouse->status ? 'Đang hoạt động' : 'Tạm tắt' }}
                            </span>
                        </div>
                    </div>

                    <div class="warehouse-metrics">
                        <div class="warehouse-actions">
                            <a href="{{ route('inventory.warehouse.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Quay lại</a>
                            @if(auth()->user()?->hasPermission('warehouse.edit'))
                                <a href="{{ route('inventory.warehouse.edit', $warehouse) }}" class="btn btn-primary"><i class="ti ti-pencil me-1"></i>Chỉnh sửa kho</a>
                            @endif
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="warehouse-metric">
                                    <span>Vị trí đang có</span>
                                    <strong>{{ number_format($warehouse->locations_count) }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="warehouse-metric">
                                    <span>Khu hệ thống</span>
                                    <strong>{{ number_format($systemLocationCount) }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="warehouse-metric">
                                    <span>Đã lên sơ đồ</span>
                                    <strong>{{ number_format($mappedLocationCount) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="warehouse-card">
                <div class="warehouse-card__body">
                    <div class="warehouse-tabs" data-warehouse-tabs>
                        @foreach($tabs as $tabKey => $tabLabel)
                            <a
                                href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => $tabKey, 'map_id' => request('map_id'), 'location_id' => request('location_id')]) }}"
                                class="warehouse-tab {{ $activeTab === $tabKey ? 'is-active' : '' }}"
                                data-tab-key="{{ $tabKey }}"
                            >
                                {{ $tabLabel }}
                            </a>
                        @endforeach
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'overview' ? 'is-active' : '' }}" data-panel-key="overview">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <h2 class="warehouse-card__title">Cài đặt hiện tại</h2>
                                    </div>
                                    <div class="warehouse-card__body">
                                        <div><strong>Kiểu kho:</strong> {{ $warehouseSetting?->warehouse_mode ?: 'simple' }}</div>
                                        <div><strong>Dùng pallet:</strong> {{ $warehouseSetting?->use_pallet ? 'Có' : 'Không' }}</div>
                                        <div><strong>Bắt buộc pallet:</strong> {{ $warehouseSetting?->require_pallet ? 'Có' : 'Không' }}</div>
                                        <div><strong>Dùng QC:</strong> {{ $warehouseSetting?->use_qc ? 'Có' : 'Không' }}</div>
                                        <div><strong>Dùng sơ đồ:</strong> {{ $warehouseSetting?->use_map ? 'Có' : 'Không' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <h2 class="warehouse-card__title">Tổng quan nhanh</h2>
                                    </div>
                                    <div class="warehouse-card__body">
                                        <div><strong>Số location:</strong> {{ number_format($warehouse->locations_count) }}</div>
                                        <div><strong>Số product mapping:</strong> {{ number_format($warehouse->warehouse_products_count) }}</div>
                                        <div><strong>Số pallet:</strong> {{ number_format($pallets->count()) }}</div>
                                        <div><strong>Số sơ đồ:</strong> {{ number_format($warehouse->maps->count()) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'settings' ? 'is-active' : '' }}" data-panel-key="settings">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <h2 class="warehouse-card__title">Cài đặt kho</h2>
                                        <p class="warehouse-card__hint">Bật hoặc tắt pallet, QC, batch và sơ đồ theo nhu cầu vận hành.</p>
                                    </div>
                                    <div class="warehouse-card__body">
                                        <form method="POST" action="{{ route('inventory.warehouse.settings.store', $warehouse) }}" class="warehouse-location-form">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Kiểu kho</label>
                                                    <select name="warehouse_mode" class="form-select">
                                                        <option value="simple" @selected(($warehouseSetting?->warehouse_mode ?? 'simple') === 'simple')>Kho đơn giản</option>
                                                        <option value="advanced" @selected(($warehouseSetting?->warehouse_mode ?? 'simple') === 'advanced')>Kho nâng cao</option>
                                                    </select>
                                                </div>
                                                <div class="col-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_pallet" value="1" @checked($warehouseSetting?->use_pallet)><span class="form-check-label">Dùng pallet</span></label></div>
                                                <div class="col-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="require_pallet" value="1" @checked($warehouseSetting?->require_pallet)><span class="form-check-label">Bắt buộc pallet</span></label></div>
                                                <div class="col-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_qc" value="1" @checked($warehouseSetting?->use_qc)><span class="form-check-label">Dùng QC</span></label></div>
                                                <div class="col-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_map" value="1" @checked($warehouseSetting?->use_map)><span class="form-check-label">Dùng sơ đồ</span></label></div>
                                                <div class="col-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_batch" value="1" @checked($warehouseSetting?->use_batch)><span class="form-check-label">Dùng batch</span></label></div>
                                                <div class="col-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_serial" value="1" @checked($warehouseSetting?->use_serial)><span class="form-check-label">Dùng serial</span></label></div>
                                                <div class="col-12"><button class="btn btn-primary w-100" type="submit">Lưu cài đặt kho</button></div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'locations' ? 'is-active' : '' }}" data-panel-key="locations">
                        <div class="row g-4 align-items-start">
                            <div class="col-xl-4">
                                <div class="warehouse-side-column">
                                    <section class="warehouse-card">
                                        <div class="warehouse-card__header">
                                            <h2 class="warehouse-card__title">Tạo nhanh vị trí kho</h2>
                                            <p class="warehouse-card__hint">Chọn mẫu có sẵn để điền trước mã, tên và kiểu vị trí.</p>
                                        </div>
                                        <div class="warehouse-card__body">
                                            <div class="warehouse-preset-grid mb-4">
                                                @foreach($templates as $templateCode => $template)
                                                    @php
                                                        $firstPreviewNode = data_get($template, 'preview.0');
                                                        $presetPayload = [
                                                            'code' => data_get($firstPreviewNode, 'code'),
                                                            'name' => data_get($firstPreviewNode, 'name'),
                                                            'type' => data_get($firstPreviewNode, 'type'),
                                                            'parent_id' => '',
                                                        ];
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        class="warehouse-preset-btn"
                                                        data-location-preset='@json($presetPayload)'
                                                    >
                                                        <strong>{{ $template['name'] }}</strong>
                                                        <span>{{ $template['description'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>

                                            @if(auth()->user()?->hasPermission('warehouse.locations.manage'))
                                                <form
                                                    id="warehouse-location-form"
                                                    class="warehouse-location-form"
                                                    method="POST"
                                                    action="{{ route('inventory.warehouse.locations.store', $warehouse) }}"
                                                    data-store-action="{{ route('inventory.warehouse.locations.store', $warehouse) }}"
                                                >
                                                    @csrf
                                                    <input type="hidden" name="_method" value="POST" data-location-form-method>
                                                    <input type="hidden" name="status" value="0">

                                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                                        <div>
                                                            <h3 class="warehouse-card__title" data-location-form-title>Tạo vị trí mới</h3>
                                                            <div class="warehouse-card__hint" data-location-form-hint">
                                                                Chọn vị trí cha nếu muốn tạo zone, rack, level hoặc bin bên trong một khu có sẵn.
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-secondary d-none" data-location-form-reset>
                                                            <i class="ti ti-refresh me-1"></i>Về tạo mới
                                                        </button>
                                                    </div>

                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Vị trí cha</label>
                                                            <select name="parent_id" class="form-select" data-location-parent-select>
                                                                <option value="">Tạo ở cấp gốc</option>
                                                                @foreach($locations as $location)
                                                                    <option value="{{ $location->getKey() }}">
                                                                        {{ str_repeat('- ', max(0, $location->level - 1)) }}{{ $location->displayLabel() }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Mã vị trí</label>
                                                            <input type="text" name="code" class="form-control" required data-location-code>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Tên vị trí</label>
                                                            <input type="text" name="name" class="form-control" required data-location-name>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Kiểu vị trí</label>
                                                            <select name="type" class="form-select" required data-location-type>
                                                                @foreach($locationTypes as $typeKey => $typeLabel)
                                                                    <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 d-flex align-items-end">
                                                            <label class="form-check form-switch mb-1">
                                                                <input type="checkbox" class="form-check-input" name="status" value="1" checked data-location-status>
                                                                <span class="form-check-label">Kích hoạt vị trí này</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Mô tả</label>
                                                            <textarea name="description" rows="3" class="form-control" data-location-description></textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="warehouse-system-note">
                                                                <div><strong>Cấp và đường dẫn</strong> sẽ được hệ thống tự tính sau khi lưu.</div>
                                                                <div><strong>Kho hiện tại</strong> luôn lấy ngầm theo kho bạn đang mở, không cần chọn tay.</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex gap-2 flex-wrap">
                                                            <button type="submit" class="btn btn-primary" data-location-form-submit><i class="ti ti-device-floppy me-1"></i>Lưu vị trí</button>
                                                            <button type="button" class="btn btn-outline-secondary" data-location-form-use-selected><i class="ti ti-arrow-up-circle me-1"></i>Dùng vị trí đang chọn làm cha</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                    </section>

                                    @if(auth()->user()?->hasPermission('warehouse.locations.manage'))
                                        <section class="warehouse-card">
                                            <div class="warehouse-card__header">
                                                <h2 class="warehouse-card__title">Mẫu kho có sẵn</h2>
                                                <p class="warehouse-card__hint">Dùng mẫu để sinh nhanh cây vị trí cơ bản rồi tinh chỉnh tiếp.</p>
                                            </div>
                                            <div class="warehouse-card__body">
                                                <div class="row g-3">
                                                    @foreach($templates as $templateCode => $template)
                                                        <div class="col-12">
                                                            <div class="warehouse-template-card">
                                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                                    <div>
                                                                        <strong>{{ $template['name'] }}</strong>
                                                                        <div class="text-muted small mt-1">{{ $template['description'] }}</div>
                                                                    </div>
                                                                    <span class="badge bg-light text-dark">{{ count(data_get($template, 'preview', [])) }} khu</span>
                                                                </div>
                                                                <ul class="warehouse-template-card__tree">
                                                                    @foreach(array_slice(data_get($template, 'preview', []), 0, 5) as $previewNode)
                                                                        <li>{{ data_get($previewNode, 'code') }} - {{ data_get($previewNode, 'name') }}</li>
                                                                    @endforeach
                                                                </ul>
                                                                <div class="d-flex gap-2 flex-wrap">
                                                                    <form method="POST" action="{{ route('inventory.warehouse.templates.apply', $warehouse) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="template_code" value="{{ $templateCode }}">
                                                                        <input type="hidden" name="mode" value="append">
                                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Thêm vào cây hiện tại</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('inventory.warehouse.templates.apply', $warehouse) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="template_code" value="{{ $templateCode }}">
                                                                        <input type="hidden" name="mode" value="overwrite">
                                                                        <button type="submit" class="btn btn-primary btn-sm">Ghi đè cây hiện tại</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </section>
                                    @endif
                                </div>
                            </div>

                            <div class="col-xl-8">
                                <div class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <h2 class="warehouse-card__title">Cây vị trí kho</h2>
                                        <p class="warehouse-card__hint">Các vị trí đã lên sơ đồ sẽ được đánh dấu để người dùng mới dễ theo dõi hơn.</p>
                                    </div>
                                    <div class="warehouse-card__body">
                                        @if($rootLocations->isEmpty())
                                            <div class="warehouse-empty-state">
                                                Kho này chưa có vị trí nào. Bạn có thể dùng mẫu kho ở bên trái hoặc tạo nhanh bằng form.
                                            </div>
                                        @else
                                            <div class="warehouse-tree-list">
                                                @foreach($rootLocations as $location)
                                                    @include('plugins/inventory::warehouse.partials.location-tree-node', [
                                                        'location' => $location,
                                                        'level' => 0,
                                                        'warehouse' => $warehouse,
                                                        'locationMeta' => $locationMeta,
                                                        'selectedLocation' => $selectedLocation,
                                                        'selectedLocationMapItem' => $selectedLocationMapItem,
                                                        'mapItemByLocation' => $mapItemByLocation,
                                                    ])
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'maps' ? 'is-active' : '' }}" data-panel-key="maps">
                        <section class="warehouse-card">
                            <div class="warehouse-card__header">
                                <h2 class="warehouse-card__title">Sơ đồ kho</h2>
                                <p class="warehouse-card__hint">
                                    Cây vị trí là dữ liệu chuẩn. Sơ đồ là lớp hiển thị trực quan để xem bố cục kho, kéo thả vùng và gắn vùng với vị trí.
                                </p>
                            </div>
                            <div class="warehouse-card__body">
                                <div
                                    class="warehouse-map-editor {{ $selectedMap ? 'is-view-mode' : 'is-init-mode' }}"
                                    data-warehouse-map-editor
                                    data-map-mode="{{ $selectedMap ? 'view' : 'init' }}"
                                >
                                    @if($canManageMaps)
                                        <aside class="warehouse-map-toolbox" data-edit-only>
                                            <div class="warehouse-map-toolbox__header">
                                                <h3 class="warehouse-card__title">Công cụ</h3>
                                                <p class="warehouse-card__hint">Chọn công cụ rồi bấm vào canvas, hoặc kéo công cụ sang canvas.</p>
                                            </div>
                                            <div class="warehouse-map-toolbox__body">
                                                <div>
                                                    <div class="warehouse-kicker mb-2">Thêm vùng</div>
                                                    <div class="warehouse-editor-toolbar__group" data-map-toolbox>
                                                        @foreach($mapEditorTools as $toolKey => $tool)
                                                            <button
                                                                type="button"
                                                                class="warehouse-editor-tool"
                                                                draggable="true"
                                                                data-map-tool="{{ $toolKey }}"
                                                                title="Kéo thả vào canvas"
                                                            >
                                                                <i class="{{ $tool['icon'] }}"></i>{{ $tool['label'] }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__divider"></div>

                                                <div>
                                                    <div class="warehouse-kicker mb-2">Thao tác</div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-duplicate><i class="ti ti-copy me-1"></i>Nhân bản</button>
                                                        <button type="button" class="btn btn-outline-danger warehouse-editor-action" data-map-delete><i class="ti ti-trash me-1"></i>Xóa vùng</button>
                                                    </div>
                                                </div>

                                                <details>
                                                    <summary class="fw-bold text-muted">Nâng cao</summary>
                                                    <div class="d-grid gap-2 mt-2">
                                                        <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-draw-toggle><i class="ti ti-pencil-plus me-1"></i>Vẽ liên tiếp</button>
                                                        <button type="button" class="btn btn-outline-secondary warehouse-editor-action is-active" data-map-snap-toggle><i class="ti ti-grid-dots me-1"></i>Snap grid</button>
                                                        <select class="form-select" data-map-grid-size>
                                                            <option value="8">Grid 8px</option>
                                                            <option value="16">Grid 16px</option>
                                                            <option value="24" selected>Grid 24px</option>
                                                            <option value="32">Grid 32px</option>
                                                        </select>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action flex-fill" data-map-zoom-out><i class="ti ti-zoom-out"></i></button>
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action flex-fill" data-map-zoom-in><i class="ti ti-zoom-in"></i></button>
                                                        </div>
                                                    </div>
                                                </details>

                                                <button type="button" class="btn btn-primary warehouse-editor-action" data-map-save><i class="ti ti-device-floppy me-1"></i>Lưu layout</button>
                                            </div>
                                        </aside>
                                    @endif

                                    <aside class="warehouse-map-sidebar">
                                        <div class="warehouse-map-sidebar__header">
                                            <h3 class="warehouse-card__title">Cây vị trí</h3>
                                            <p class="warehouse-card__hint">Bấm vào vị trí để tô sáng vùng tương ứng trên sơ đồ.</p>
                                        </div>
                                        <div class="warehouse-map-sidebar__body">
                                            @if($rootLocations->isEmpty())
                                                <div class="warehouse-map-mini-empty">Chưa có vị trí nào để gắn lên sơ đồ.</div>
                                            @else
                                                <div class="warehouse-tree-list" data-map-tree-panel>
                                                    @foreach($rootLocations as $location)
                                                        @include('plugins/inventory::warehouse.partials.location-tree-node', [
                                                            'location' => $location,
                                                            'level' => 0,
                                                            'warehouse' => $warehouse,
                                                            'locationMeta' => $locationMeta,
                                                            'selectedLocation' => $selectedLocation,
                                                            'selectedLocationMapItem' => $selectedLocationMapItem,
                                                            'mapItemByLocation' => $mapItemByLocation,
                                                        ])
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </aside>

                                    <section class="warehouse-map-canvas-panel">
                                        <div class="warehouse-map-canvas-panel__header">
                                            <div class="warehouse-editor-toolbar">
                                                <div class="warehouse-editor-toolbar__row">
                                                    <div class="warehouse-map-switcher">
                                                        @forelse($warehouse->maps as $map)
                                                            <a
                                                                href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => 'maps', 'map_id' => $map->getKey(), 'location_id' => $selectedLocation?->getKey()]) }}"
                                                                class="btn {{ $selectedMap && (int) $selectedMap->getKey() === (int) $map->getKey() ? 'btn-primary' : 'btn-outline-secondary' }}"
                                                            >
                                                                {{ $map->name }}
                                                            </a>
                                                        @empty
                                                            <span class="btn btn-outline-secondary disabled">Chưa có sơ đồ</span>
                                                        @endforelse
                                                    </div>

                                                    @if($canManageMaps)
                                                        <div class="warehouse-inline-form">
                                                            <form class="warehouse-inline-form" method="POST" action="{{ route('inventory.warehouse.maps.store', $warehouse) }}">
                                                                @csrf
                                                                <input type="hidden" name="map_type" value="floor_plan">
                                                                <input type="hidden" name="width" value="1200">
                                                                <input type="hidden" name="height" value="800">
                                                                <input type="hidden" name="scale_ratio" value="1">
                                                                <input type="hidden" name="is_active" value="1">
                                                                <input type="text" name="name" class="form-control" style="min-width: 180px;" placeholder="Tên sơ đồ mới" required>
                                                                <button type="submit" class="btn btn-outline-secondary">Tạo sơ đồ trống</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($selectedMap && $canManageMaps)
                                                    <div class="warehouse-editor-toolbar__row">
                                                        <div class="warehouse-map-header-actions">
                                                            <button type="button" class="btn btn-outline-primary" data-map-mode-button="edit" data-view-only>
                                                                <i class="ti ti-pencil me-1"></i>Chỉnh sửa sơ đồ
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary" data-map-mode-button="view" data-edit-only>
                                                                <i class="ti ti-eye me-1"></i>Xem sơ đồ
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($canManageMaps)
                                                    <div class="warehouse-editor-toolbar__row">
                                                        <div class="warehouse-editor-toolbar__group" data-map-toolbox>
                                                            @foreach($mapEditorTools as $toolKey => $tool)
                                                                <button
                                                                    type="button"
                                                                    class="warehouse-editor-tool"
                                                                    draggable="true"
                                                                    data-map-tool="{{ $toolKey }}"
                                                                    title="Kéo thả vào canvas"
                                                                >
                                                                    <i class="{{ $tool['icon'] }}"></i>{{ $tool['label'] }}
                                                                </button>
                                                            @endforeach
                                                        </div>

                                                        <div class="warehouse-editor-toolbar__group">
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-draw-toggle><i class="ti ti-pencil-plus me-1"></i>Vẽ liên tiếp</button>
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action is-active" data-map-snap-toggle><i class="ti ti-grid-dots me-1"></i>Snap grid</button>
                                                            <select class="form-select" style="min-width: 108px;" data-map-grid-size>
                                                                <option value="8">Grid 8px</option>
                                                                <option value="16">Grid 16px</option>
                                                                <option value="24" selected>Grid 24px</option>
                                                                <option value="32">Grid 32px</option>
                                                            </select>
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-duplicate><i class="ti ti-copy me-1"></i>Duplicate</button>
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-delete><i class="ti ti-trash me-1"></i>Delete</button>
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-zoom-out><i class="ti ti-zoom-out me-1"></i>Zoom -</button>
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-zoom-in><i class="ti ti-zoom-in me-1"></i>Zoom +</button>
                                                            <button type="button" class="btn btn-primary warehouse-editor-action" data-map-save><i class="ti ti-device-floppy me-1"></i>Lưu layout</button>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="warehouse-editor-toolbar__row">
                                                    <div class="warehouse-map-status">
                                                        <span class="badge bg-light text-dark">
                                                            {{ $selectedMap?->name ?: 'Chưa có sơ đồ' }}
                                                        </span>
                                                        <span class="badge bg-info-lt text-info">{{ $selectedMap?->map_type ?: 'floor_plan' }}</span>
                                                        @if($selectedMapBackgroundUrl)
                                                            <span class="badge bg-secondary-lt text-secondary">Có ảnh nền</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted small" data-map-status-text>
                                                        {{ $selectedMap ? 'Kéo thả module mới vào canvas, chọn item để chỉnh ở panel bên phải.' : 'Tạo sơ đồ hoặc dùng mẫu có sẵn để bắt đầu.' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                                <details class="warehouse-map-guide-wrap" data-edit-only>
                                                    <summary class="fw-bold text-muted px-3 pt-3">Hướng dẫn sử dụng</summary>
                                                    <div class="warehouse-map-guide">
                                                    <div class="warehouse-map-guide__item">
                                                        <i class="ti ti-hand-move"></i>
                                                        <div><strong>Kéo thả</strong> Kéo module từ toolbar vào vùng sơ đồ, rồi kéo item để đổi vị trí.</div>
                                                    </div>
                                                    <div class="warehouse-map-guide__item">
                                                        <i class="ti ti-pencil-plus"></i>
                                                        <div><strong>Vẽ liên tiếp</strong> Bật chế độ vẽ, chọn module, click nhiều điểm trên canvas. Nhấn Esc để thoát.</div>
                                                    </div>
                                                    <div class="warehouse-map-guide__item">
                                                        <i class="ti ti-grid-dots"></i>
                                                        <div><strong>Snap grid</strong> Bật snap để item bám lưới. Có thể đổi grid 8, 16, 24 hoặc 32px.</div>
                                                    </div>
                                                    <div class="warehouse-map-guide__item">
                                                        <i class="ti ti-device-floppy"></i>
                                                        <div><strong>Lưu layout</strong> Chỉnh label, gắn location ở panel phải, sau đó bấm Lưu layout.</div>
                                                    </div>
                                                    </div>
                                                </details>
                                        <div class="warehouse-map-canvas-panel__body">
                                            @if($selectedMap)
                                                <div class="warehouse-map-stats mb-3">
                                                    <div class="warehouse-map-stat">
                                                        <span>Tổng vùng</span>
                                                        <strong data-map-stat-total>{{ number_format($selectedMapItems->count()) }}</strong>
                                                    </div>
                                                    <div class="warehouse-map-stat">
                                                        <span>Đã gắn location</span>
                                                        <strong data-map-stat-linked>{{ number_format($linkedLocationCount) }}</strong>
                                                    </div>
                                                    <div class="warehouse-map-stat">
                                                        <span>Chưa gắn</span>
                                                        <strong data-map-stat-unlinked>{{ number_format($unlinkedLocationCount) }}</strong>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-view-detail mb-3" data-map-view-detail data-view-only>
                                                    <strong>Đang xem sơ đồ</strong>
                                                    <span>Bấm vào một vùng trên sơ đồ hoặc một vị trí trong cây để xem liên kết tương ứng.</span>
                                                </div>

                                                <div class="warehouse-map-stage">
                                                    <div class="warehouse-map-viewport" data-map-viewport>
                                                        <div class="warehouse-map-drop-hint">Kéo module từ toolbar vào đây</div>
                                                        <div class="warehouse-map-canvas-wrap" data-map-canvas-wrap>
                                                            <div
                                                                class="warehouse-map-canvas"
                                                                data-map-canvas
                                                                style="
                                                                    width: {{ $mapWidth }}px;
                                                                    height: {{ $mapHeight }}px;
                                                                    background-image:
                                                                        linear-gradient(90deg, rgba(148, 163, 184, .10) 1px, transparent 1px),
                                                                        linear-gradient(180deg, rgba(148, 163, 184, .10) 1px, transparent 1px)
                                                                        @if($selectedMapBackgroundUrl), url('{{ $selectedMapBackgroundUrl }}') @endif;
                                                                "
                                                            >
                                                                <div class="warehouse-map-canvas__selection" data-map-selection></div>
                                                                @foreach($selectedMapItems as $item)
                                                                    @php
                                                                        $linkedLocation = $item->location;
                                                                        $itemMeta = $mapTypeMeta[$item->item_type] ?? [
                                                                            'icon' => 'ti ti-map-pin',
                                                                            'label' => \Illuminate\Support\Str::headline((string) $item->item_type),
                                                                        ];
                                                                    @endphp
                                                                    <button
                                                                        type="button"
                                                                        class="warehouse-map-canvas__item {{ $linkedLocation ? 'is-linked' : '' }} {{ $item->shape_type === 'label' ? 'warehouse-map-canvas__item--label' : '' }}"
                                                                        data-map-item-id="{{ $item->getKey() }}"
                                                                        style="
                                                                            left: {{ (float) $item->x }}px;
                                                                            top: {{ (float) $item->y }}px;
                                                                            width: {{ max(36, (float) $item->width) }}px;
                                                                            height: {{ max(28, (float) $item->height) }}px;
                                                                            background: {{ $item->shape_type === 'label' ? 'transparent' : ($item->color ?: '#e2e8f0') }};
                                                                            z-index: {{ (int) ($item->z_index ?: 1) }};
                                                                            transform: rotate({{ (float) ($item->rotation ?: 0) }}deg);
                                                                        "
                                                                    >
                                                                        <div class="warehouse-map-canvas__content">
                                                                            <div class="warehouse-map-canvas__title">
                                                                                <i class="{{ $itemMeta['icon'] }}"></i>
                                                                                <span>{{ $item->label ?: $itemMeta['label'] }}</span>
                                                                            </div>
                                                                            <div class="warehouse-map-canvas__meta">
                                                                                {{ $linkedLocation?->displayLabel() ?: $itemMeta['label'] }}
                                                                            </div>
                                                                        </div>
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <details class="warehouse-card mt-3">
                                                    <summary class="warehouse-card__body warehouse-card__title">Chú giải</summary>
                                                    <div class="warehouse-card__body pt-0">
                                                        <div class="text-muted small mb-3">Mở khi cần đối chiếu màu và loại vùng trên sơ đồ.</div>
                                                        <div class="warehouse-map-legend" data-map-legend>
                                                            @foreach($mapLegendGroups as $itemType => $groupedItems)
                                                                @php
                                                                    $legend = $mapTypeMeta[$itemType] ?? [
                                                                        'label' => \Illuminate\Support\Str::headline((string) $itemType),
                                                                        'icon' => 'ti ti-map-pin',
                                                                    ];
                                                                @endphp
                                                                <div class="warehouse-map-legend-item">
                                                                    <span><i class="{{ $legend['icon'] }}"></i>{{ $legend['label'] }}</span>
                                                                    <strong>{{ $groupedItems->count() }}</strong>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </details>
                                            @else
                                                <div class="warehouse-map-init">
                                                    <div>
                                                        <div class="warehouse-kicker">Bắt đầu sơ đồ kho</div>
                                                        <h3 class="warehouse-card__title mt-2">Tạo sơ đồ trực quan cho kho này</h3>
                                                        <p class="warehouse-card__hint mb-0">
                                                            Dùng mẫu có sẵn nếu muốn hệ thống tạo nhanh các vùng cơ bản, hoặc tạo sơ đồ trống nếu kho có bố cục riêng.
                                                        </p>
                                                    </div>
                                                    @if($canManageMaps)
                                                        <div class="warehouse-map-init__actions">
                                                            <form class="warehouse-inline-form" method="POST" action="{{ route('inventory.warehouse.maps.store', $warehouse) }}">
                                                                @csrf
                                                                <input type="hidden" name="map_type" value="floor_plan">
                                                                <input type="hidden" name="width" value="1200">
                                                                <input type="hidden" name="height" value="800">
                                                                <input type="hidden" name="scale_ratio" value="1">
                                                                <input type="hidden" name="is_active" value="1">
                                                                <input type="text" name="name" class="form-control" style="min-width: 220px;" placeholder="Tên sơ đồ mới" required>
                                                                <button type="submit" class="btn btn-primary">Tạo sơ đồ trống</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($canManageMaps && ! $selectedMap)
                                                <div class="warehouse-map-blueprints mt-3">
                                                    <div class="warehouse-card__title">Mẫu sơ đồ có sẵn</div>
                                                    <div class="row g-3">
                                                        @foreach(['simple' => 'Kho cơ bản', 'qc' => 'Kho có QC', 'rack_floor' => 'Kho nhiều rack'] as $blueprintCode => $blueprintLabel)
                                                            <div class="col-md-4">
                                                                <div class="warehouse-map-toolbar-card">
                                                                    <strong>{{ $blueprintLabel }}</strong>
                                                                    <div class="warehouse-map-template-preview" aria-hidden="true"></div>
                                                                    <div class="text-muted small">
                                                                        {{ $blueprintCode === 'simple' ? 'Luồng 1 chiều dễ hiểu, phù hợp khởi tạo nhanh.' : ($blueprintCode === 'qc' ? 'Có thêm khu QC và khu chờ xếp trước lưu trữ.' : 'Nghiêng về rack, aisle và tầng/khu lưu trữ lớn.') }}
                                                                    </div>
                                                                    <form method="POST" action="{{ route('inventory.warehouse.maps.blueprint', $warehouse) }}">
                                                                        @csrf
                                                                        <button class="btn btn-outline-secondary w-100" type="submit" name="blueprint_code" value="{{ $blueprintCode }}">
                                                                            Dùng mẫu này
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </section>

                                    <aside class="warehouse-map-inspector">
                                        <div class="warehouse-map-inspector__header">
                                            <h3 class="warehouse-card__title">Cấu hình vùng đang chọn</h3>
                                            <p class="warehouse-card__hint">Chọn một item trên sơ đồ để chỉnh nhãn, location, kích thước và thông số vận hành.</p>
                                        </div>
                                        <div class="warehouse-map-inspector__body">
                                            <div class="warehouse-map-inspector__empty" data-map-empty-inspector>
                                                <strong>Bắt đầu chỉnh sơ đồ</strong>
                                                <ol class="mb-0 mt-2 ps-3">
                                                    <li>Chọn một công cụ ở cột bên trái.</li>
                                                    <li>Bấm hoặc kéo vào canvas để thêm vùng.</li>
                                                    <li>Bấm vào vùng để đổi tên, màu và gắn location.</li>
                                                </ol>
                                            </div>

                                            <div class="warehouse-hidden" data-map-inspector-content>
                                                <div class="warehouse-map-inspector__section">
                                                    <h4>Thông tin cơ bản</h4>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Nhãn hiển thị
                                                            <input type="text" class="form-control" data-inspector-field="label">
                                                        </label>
                                                        <label>
                                                            Loại item
                                                            <input type="text" class="form-control" data-inspector-field="item_type_label" readonly>
                                                        </label>
                                                        <label>
                                                            Màu nền
                                                            <input type="color" class="form-control form-control-color" data-inspector-field="color">
                                                        </label>
                                                        <label>
                                                            Góc xoay
                                                            <input type="number" step="1" class="form-control" data-inspector-field="rotation">
                                                        </label>
                                                    </div>
                                                    <div class="warehouse-field-grid warehouse-field-grid--single">
                                                        <label>
                                                            Gắn với location
                                                            <select class="form-select" data-inspector-field="location_id">
                                                                <option value="">Chưa gắn location</option>
                                                                @foreach($mapLocationOptions as $locationOption)
                                                                    <option value="{{ $locationOption['id'] }}">{{ $locationOption['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                    </div>
                                                    <label class="form-check mb-0">
                                                        <input type="checkbox" class="form-check-input" data-inspector-field="is_clickable">
                                                        <span class="form-check-label">Cho phép click để nhảy sang tree / chi tiết vị trí</span>
                                                    </label>
                                                </div>

                                                <div class="warehouse-map-inspector__section">
                                                    <h4>Kích thước và toạ độ</h4>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            X
                                                            <input type="number" step="1" class="form-control" data-inspector-field="x">
                                                        </label>
                                                        <label>
                                                            Y
                                                            <input type="number" step="1" class="form-control" data-inspector-field="y">
                                                        </label>
                                                        <label>
                                                            Rộng
                                                            <input type="number" step="1" class="form-control" data-inspector-field="width">
                                                        </label>
                                                        <label>
                                                            Cao
                                                            <input type="number" step="1" class="form-control" data-inspector-field="height">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="simple_shelf">
                                                    <h4>Kệ đơn giản</h4>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Tiền tố
                                                            <input type="text" class="form-control" data-inspector-meta="prefix">
                                                        </label>
                                                        <label>
                                                            Số tầng
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="level_count">
                                                        </label>
                                                        <label>
                                                            Ô mỗi tầng
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="bin_count_per_level">
                                                        </label>
                                                        <label class="form-check d-flex align-items-center gap-2 mt-4">
                                                            <input type="checkbox" class="form-check-input" data-inspector-meta="uses_pallet">
                                                            <span class="form-check-label">Có dùng pallet</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="pallet_rack">
                                                    <h4>Rack pallet</h4>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Tiền tố
                                                            <input type="text" class="form-control" data-inspector-meta="prefix">
                                                        </label>
                                                        <label>
                                                            Số bay
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="bay_count">
                                                        </label>
                                                        <label>
                                                            Số tầng
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="level_count">
                                                        </label>
                                                        <label>
                                                            Vị trí mỗi tầng
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="positions_per_level">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="floor_pallet_area">
                                                    <h4>Khu pallet sàn</h4>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Tiền tố
                                                            <input type="text" class="form-control" data-inspector-meta="prefix">
                                                        </label>
                                                        <label>
                                                            Số hàng
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="row_count">
                                                        </label>
                                                        <label>
                                                            Số cột
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="column_count">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="label">
                                                    <h4>Nhãn chú thích</h4>
                                                    <div class="warehouse-field-grid warehouse-field-grid--single">
                                                        <label>
                                                            Nội dung text
                                                            <input type="text" class="form-control" data-inspector-field="label">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section">
                                                    <h4>Năng lực dự kiến</h4>
                                                    <div class="warehouse-map-inspector__capacity">
                                                        <div>
                                                            <div class="fw-bold">Sức chứa logic</div>
                                                            <small>Dựa trên cấu hình rack / shelf / pallet area</small>
                                                        </div>
                                                        <strong data-inspector-capacity>-</strong>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section">
                                                    <h4>Nhân nhiều bản sao</h4>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Số bản sao
                                                            <input type="number" min="1" value="3" class="form-control" data-inspector-duplicate="count">
                                                        </label>
                                                        <label>
                                                            Tiền tố nhãn
                                                            <input type="text" class="form-control" value="COPY-" data-inspector-duplicate="prefix">
                                                        </label>
                                                        <label>
                                                            Lệch ngang
                                                            <input type="number" value="28" class="form-control" data-inspector-duplicate="offset_x">
                                                        </label>
                                                        <label>
                                                            Lệch dọc
                                                            <input type="number" value="24" class="form-control" data-inspector-duplicate="offset_y">
                                                        </label>
                                                    </div>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <button type="button" class="btn btn-outline-secondary" data-map-batch-duplicate>Nhân nhiều</button>
                                                        <button type="button" class="btn btn-outline-secondary" data-map-focus-tree>Xem trong cây vị trí</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </aside>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'products' ? 'is-active' : '' }}" data-panel-key="products">
                        <div class="warehouse-empty-state">Phần sản phẩm trong kho đang dùng logic catalog hiện có. Có thể tách thành tab chi tiết hơn ở bước tiếp theo.</div>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'policies' ? 'is-active' : '' }}" data-panel-key="policies">
                        <div class="warehouse-empty-state">Chính sách sản phẩm đã có backend. UI chi tiết cho từng policy sẽ được nối tiếp khi cần.</div>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'pallets' ? 'is-active' : '' }}" data-panel-key="pallets">
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <section class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <h2 class="warehouse-card__title">Pallet</h2>
                                        <p class="warehouse-card__hint">Tạo pallet và đặt pallet vào location.</p>
                                    </div>
                                    <div class="warehouse-card__body">
                                        @if(($warehouseSetting?->use_pallet ?? false))
                                            <form method="POST" action="{{ route('inventory.warehouse.pallets.store', $warehouse) }}" class="warehouse-location-form mb-3">
                                                @csrf
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label">Mã pallet</label>
                                                        <input type="text" name="code" class="form-control" placeholder="PLT0001" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Vị trí hiện tại</label>
                                                        <select name="current_location_id" class="form-select">
                                                            <option value="">-- Chưa đặt vị trí --</option>
                                                            @foreach($locations->filter(fn ($location) => in_array($location->type, \Botble\Inventory\Domains\Warehouse\Support\PalletLocationRules::allowedTypes(), true)) as $location)
                                                                <option value="{{ $location->getKey() }}">{{ $location->displayLabel() }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Loại pallet</label>
                                                        <input type="text" name="type" class="form-control" placeholder="standard">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Trạng thái</label>
                                                        <select name="status" class="form-select">
                                                            <option value="empty">Empty</option>
                                                            <option value="open">Open</option>
                                                            <option value="in_use">In use</option>
                                                            <option value="closed">Closed</option>
                                                            <option value="damaged">Damaged</option>
                                                            <option value="locked">Locked</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Ghi chú</label>
                                                        <textarea name="note" rows="2" class="form-control"></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <button class="btn btn-primary w-100" type="submit">Tạo pallet</button>
                                                    </div>
                                                </div>
                                            </form>
                                        @else
                                            <div class="warehouse-empty-state mb-3">Kho này chưa bật pallet. Hãy bật trong cài đặt kho nếu muốn dùng pallet.</div>
                                        @endif
                                    </div>
                                </section>
                            </div>
                            <div class="col-lg-7">
                                <section class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <h2 class="warehouse-card__title">Danh sách pallet</h2>
                                    </div>
                                    <div class="warehouse-card__body">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Mã</th>
                                                        <th>Vị trí</th>
                                                        <th>TT</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($pallets as $pallet)
                                                        <tr>
                                                            <td>{{ $pallet->code }}</td>
                                                            <td>{{ $pallet->currentLocation?->displayLabel() ?: '-' }}</td>
                                                            <td><span class="badge bg-light text-dark">{{ $pallet->status }}</span></td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted py-3">Chưa có pallet nào</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const mapEditorConfig = {!! $mapEditorConfigJson !!} || {};
            const warehouseTabs = document.querySelector('[data-warehouse-tabs]');
            const tabLinks = Array.from(document.querySelectorAll('[data-tab-key]'));
            const tabPanels = Array.from(document.querySelectorAll('[data-panel-key]'));
            const locationForm = document.getElementById('warehouse-location-form');
            const locationNodes = () => Array.from(document.querySelectorAll('[data-location-node]'));
            let selectedLocationId = mapEditorConfig.selected_location_id ? Number(mapEditorConfig.selected_location_id) : null;

            const notify = (message, type = 'success') => {
                if (window.Botble) {
                    if (type === 'error' && typeof window.Botble.showError === 'function') {
                        window.Botble.showError(message);
                        return;
                    }

                    if (type === 'success' && typeof window.Botble.showSuccess === 'function') {
                        window.Botble.showSuccess(message);
                        return;
                    }
                }

                window.alert(message);
            };

            const activateTab = (tabKey) => {
                tabLinks.forEach((link) => {
                    link.classList.toggle('is-active', link.dataset.tabKey === tabKey);
                });

                tabPanels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.dataset.panelKey === tabKey);
                });

                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabKey);
                window.history.replaceState({}, '', url.toString());
            };

            if (warehouseTabs) {
                warehouseTabs.addEventListener('click', (event) => {
                    const link = event.target.closest('[data-tab-key]');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();
                    activateTab(link.dataset.tabKey);
                });
            }

            const getVisibleNode = (locationId) => {
                return locationNodes().find((node) => Number(node.dataset.locationId) === Number(locationId) && node.offsetParent !== null)
                    || locationNodes().find((node) => Number(node.dataset.locationId) === Number(locationId))
                    || null;
            };

            const setActiveLocationNode = (locationId, scroll = false) => {
                selectedLocationId = locationId ? Number(locationId) : null;

                locationNodes().forEach((node) => {
                    node.classList.toggle('is-active', Number(node.dataset.locationId) === selectedLocationId);
                });

                if (scroll && selectedLocationId) {
                    const visibleNode = getVisibleNode(selectedLocationId);

                    if (visibleNode) {
                        visibleNode.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }
            };

            if (selectedLocationId) {
                setActiveLocationNode(selectedLocationId);
            }

            if (locationForm) {
                const formMethodInput = locationForm.querySelector('[data-location-form-method]');
                const formTitle = locationForm.querySelector('[data-location-form-title]');
                const formHint = locationForm.querySelector('[data-location-form-hint]');
                const resetButton = locationForm.querySelector('[data-location-form-reset]');
                const storeAction = locationForm.dataset.storeAction;
                const parentSelect = locationForm.querySelector('[data-location-parent-select]');
                const codeInput = locationForm.querySelector('[data-location-code]');
                const nameInput = locationForm.querySelector('[data-location-name]');
                const typeInput = locationForm.querySelector('[data-location-type]');
                const statusInput = locationForm.querySelector('[data-location-status]');
                const descriptionInput = locationForm.querySelector('[data-location-description]');

                const resetLocationForm = () => {
                    locationForm.action = storeAction;
                    formMethodInput.value = 'POST';
                    formTitle.textContent = 'Tạo vị trí mới';
                    formHint.textContent = 'Chọn vị trí cha nếu muốn tạo zone, rack, level hoặc bin bên trong một khu có sẵn.';
                    if (parentSelect) {
                        parentSelect.value = selectedLocationId || '';
                    }
                    codeInput.value = '';
                    nameInput.value = '';
                    typeInput.value = 'zone';
                    statusInput.checked = true;
                    descriptionInput.value = '';
                    resetButton.classList.add('d-none');
                };

                const applyPayloadToForm = (payload, mode = 'edit') => {
                    activateTab('locations');

                    if (mode === 'edit') {
                        locationForm.action = payload.update_url;
                        formMethodInput.value = 'PUT';
                        formTitle.textContent = `Cập nhật vị trí: ${payload.code}`;
                        formHint.textContent = 'Có thể đổi vị trí cha, mã, tên hoặc mô tả. Hệ thống sẽ tự rebuild lại level và path khi cần.';
                        parentSelect.value = payload.parent_id || '';
                        codeInput.value = payload.code || '';
                        nameInput.value = payload.name || '';
                        typeInput.value = payload.type || 'zone';
                        statusInput.checked = Boolean(payload.status);
                        descriptionInput.value = payload.description || '';
                    } else {
                        resetLocationForm();
                        formTitle.textContent = `Tạo vị trí con dưới ${payload.code}`;
                        formHint.textContent = 'Vị trí cha đã được gán sẵn. Bạn chỉ cần nhập mã, tên và kiểu vị trí.';
                        parentSelect.value = payload.id || '';
                        typeInput.value = payload.type === 'floor' ? 'zone' : 'rack';
                    }

                    resetButton.classList.remove('d-none');
                    locationForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                };

                resetLocationForm();

                resetButton?.addEventListener('click', () => {
                    resetLocationForm();
                });

                locationForm.querySelector('[data-location-form-use-selected]')?.addEventListener('click', () => {
                    if (!selectedLocationId) {
                        notify('Bạn chưa chọn vị trí nào trong cây.', 'error');
                        return;
                    }

                    parentSelect.value = String(selectedLocationId);
                });

                document.querySelectorAll('[data-location-preset]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const payload = JSON.parse(button.dataset.locationPreset || '{}');
                        resetLocationForm();
                        codeInput.value = payload.code || '';
                        nameInput.value = payload.name || '';
                        typeInput.value = payload.type || 'zone';
                        if (payload.parent_id) {
                            parentSelect.value = payload.parent_id;
                        }
                    });
                });

                document.addEventListener('click', (event) => {
                    const editButton = event.target.closest('[data-location-edit]');
                    const addChildButton = event.target.closest('[data-location-add-child]');

                    if (editButton) {
                        event.preventDefault();
                        const node = editButton.closest('[data-location-node]');
                        if (!node) {
                            return;
                        }

                        const payload = JSON.parse(node.dataset.location || '{}');
                        setActiveLocationNode(payload.id, true);
                        applyPayloadToForm(payload, 'edit');
                        return;
                    }

                    if (addChildButton) {
                        event.preventDefault();
                        const node = addChildButton.closest('[data-location-node]');
                        if (!node) {
                            return;
                        }

                        const payload = JSON.parse(node.dataset.location || '{}');
                        setActiveLocationNode(payload.id, true);
                        applyPayloadToForm(payload, 'child');
                    }
                });
            }

            const editorRoot = document.querySelector('[data-warehouse-map-editor]');

            if (!editorRoot || !mapEditorConfig.map) {
                return;
            }

            const viewport = editorRoot.querySelector('[data-map-viewport]');
            const canvasWrap = editorRoot.querySelector('[data-map-canvas-wrap]');
            const canvas = editorRoot.querySelector('[data-map-canvas]');
            const selectionBox = editorRoot.querySelector('[data-map-selection]');
            const statusText = editorRoot.querySelector('[data-map-status-text]');
            const totalStat = editorRoot.querySelector('[data-map-stat-total]');
            const linkedStat = editorRoot.querySelector('[data-map-stat-linked]');
            const unlinkedStat = editorRoot.querySelector('[data-map-stat-unlinked]');
            const saveButton = editorRoot.querySelector('[data-map-save]');
            const deleteButton = editorRoot.querySelector('[data-map-delete]');
            const duplicateButton = editorRoot.querySelector('[data-map-duplicate]');
            const zoomInButton = editorRoot.querySelector('[data-map-zoom-in]');
            const zoomOutButton = editorRoot.querySelector('[data-map-zoom-out]');
            const drawModeButton = editorRoot.querySelector('[data-map-draw-toggle]');
            const snapToggleButton = editorRoot.querySelector('[data-map-snap-toggle]');
            const gridSizeSelect = editorRoot.querySelector('[data-map-grid-size]');
            const batchDuplicateButton = editorRoot.querySelector('[data-map-batch-duplicate]');
            const focusTreeButton = editorRoot.querySelector('[data-map-focus-tree]');
            const emptyInspector = editorRoot.querySelector('[data-map-empty-inspector]');
            const inspectorContent = editorRoot.querySelector('[data-map-inspector-content]');
            const legendRoot = editorRoot.querySelector('[data-map-legend]');
            const viewDetail = editorRoot.querySelector('[data-map-view-detail]');
            const modeButtons = Array.from(editorRoot.querySelectorAll('[data-map-mode-button]'));

            const inspectorFieldElements = Array.from(editorRoot.querySelectorAll('[data-inspector-field]'));
            const inspectorMetaElements = Array.from(editorRoot.querySelectorAll('[data-inspector-meta]'));
            const duplicateElements = Array.from(editorRoot.querySelectorAll('[data-inspector-duplicate]'));
            const sectionElements = Array.from(editorRoot.querySelectorAll('[data-inspector-section]'));
            const capacityElement = editorRoot.querySelector('[data-inspector-capacity]');

            const cloneDeep = (value) => JSON.parse(JSON.stringify(value ?? {}));
            const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
            const toolsByKey = Object.fromEntries((mapEditorConfig.tools || []).map((tool) => [tool.key, tool]));
            const metaByType = mapEditorConfig.meta || {};
            const locationOptions = mapEditorConfig.locations || [];
            const locationById = Object.fromEntries(locationOptions.map((location) => [Number(location.id), location]));

            const normalizeItem = (item, index = 0) => {
                const itemType = item.item_type || 'zone';
                const meta = metaByType[itemType] || {};

                return {
                    id: item.id ?? `tmp-${Date.now()}-${Math.random().toString(36).slice(2)}`,
                    location_id: item.location_id ? Number(item.location_id) : null,
                    item_type: itemType,
                    label: item.label || meta.label || 'Item',
                    shape_type: item.shape_type || 'rect',
                    x: Number(item.x || 0),
                    y: Number(item.y || 0),
                    width: Math.max(36, Number(item.width || 120)),
                    height: Math.max(28, Number(item.height || 90)),
                    rotation: Number(item.rotation || 0),
                    color: item.color || meta.color || '#e2e8f0',
                    z_index: Number(item.z_index || index + 1),
                    is_clickable: item.is_clickable !== false,
                    meta_json: cloneDeep(item.meta_json || {}),
                };
            };

            const state = {
                map: cloneDeep(mapEditorConfig.map),
                items: (mapEditorConfig.items || []).map((item, index) => normalizeItem(item, index)),
                selectedId: mapEditorConfig.selected_item_id ? Number(mapEditorConfig.selected_item_id) : null,
                zoom: 1,
                dirty: false,
                snapEnabled: true,
                gridSize: Number(gridSizeSelect?.value || 24),
                drawMode: false,
                drawTool: null,
                mode: editorRoot.dataset.mapMode || 'view',
                didInitialScroll: false,
                drag: null,
                draggedTool: null,
            };

            const getItem = (itemId = state.selectedId) => state.items.find((item) => String(item.id) === String(itemId)) || null;
            const isEditMode = () => state.mode === 'edit';
            const snapValue = (value) => state.snapEnabled ? Math.round(value / state.gridSize) * state.gridSize : value;
            const snapPoint = (point) => ({
                x: snapValue(point.x),
                y: snapValue(point.y),
            });

            const getLocationLabel = (locationId) => {
                const location = locationById[Number(locationId)];
                return location ? location.label : 'Chưa gắn location';
            };

            const getModuleType = (item) => item?.meta_json?.module_type || item?.item_type || 'zone';

            const getCapacity = (item) => {
                if (!item) {
                    return null;
                }

                const moduleType = getModuleType(item);
                const meta = item.meta_json || {};

                if (moduleType === 'pallet_rack') {
                    return Math.max(1, Number(meta.bay_count || 1)) * Math.max(1, Number(meta.level_count || 1)) * Math.max(1, Number(meta.positions_per_level || 1));
                }

                if (moduleType === 'simple_shelf') {
                    return Math.max(1, Number(meta.level_count || 1)) * Math.max(1, Number(meta.bin_count_per_level || 1));
                }

                if (moduleType === 'floor_pallet_area') {
                    return Math.max(1, Number(meta.row_count || 1)) * Math.max(1, Number(meta.column_count || 1));
                }

                return null;
            };

            const updateModeState = () => {
                editorRoot.dataset.mapMode = state.mode;
                editorRoot.classList.toggle('is-view-mode', state.mode === 'view');
                editorRoot.classList.toggle('is-edit-mode', state.mode === 'edit');
                editorRoot.classList.toggle('is-init-mode', state.mode === 'init');

                if (!isEditMode() && state.drawMode) {
                    state.drawMode = false;
                    state.drawTool = null;
                }

                modeButtons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.mapModeButton === state.mode);
                });

                if (canvas) {
                    canvas.classList.toggle('is-draw-mode', state.drawMode);
                    canvas.style.backgroundSize = state.map.background_url
                        ? `${state.gridSize}px ${state.gridSize}px, ${state.gridSize}px ${state.gridSize}px, cover`
                        : `${state.gridSize}px ${state.gridSize}px, ${state.gridSize}px ${state.gridSize}px`;
                }

                if (drawModeButton) {
                    drawModeButton.classList.toggle('is-active', state.drawMode);
                    drawModeButton.innerHTML = state.drawMode
                        ? '<i class="ti ti-pencil-check me-1"></i>Đang vẽ'
                        : '<i class="ti ti-pencil-plus me-1"></i>Vẽ liên tiếp';
                }

                if (snapToggleButton) {
                    snapToggleButton.classList.toggle('is-active', state.snapEnabled);
                    snapToggleButton.innerHTML = state.snapEnabled
                        ? '<i class="ti ti-grid-dots me-1"></i>Snap grid'
                        : '<i class="ti ti-border-all me-1"></i>Tự do';
                }

                if (gridSizeSelect) {
                    gridSizeSelect.value = String(state.gridSize);
                }

                editorRoot.querySelectorAll('[data-map-tool]').forEach((toolButton) => {
                    toolButton.classList.toggle('is-active', state.drawMode && toolButton.dataset.mapTool === state.drawTool);
                });

                if (statusText) {
                    if (!isEditMode()) {
                        statusText.textContent = 'Chế độ xem: bấm vào vùng trên sơ đồ hoặc vị trí trong cây để xem liên kết.';
                        return;
                    }

                    const fragments = [];

                    if (state.drawMode) {
                        fragments.push(state.drawTool
                            ? `Draw mode đang bật. Bấm trên canvas để vẽ liên tiếp "${toolsByKey[state.drawTool]?.label || state.drawTool}". Nhấn Esc để thoát.`
                            : 'Draw mode đang bật. Chọn một module ở toolbar rồi bấm trên canvas để đặt nhanh nhiều item.');
                    }

                    if (state.snapEnabled) {
                        fragments.push(`Snap grid ${state.gridSize}px đang bật.`);
                    }

                    if (state.dirty) {
                        fragments.push('Bạn đang có thay đổi chưa lưu. Bấm "Lưu layout" để ghi xuống database.');
                    } else if (!state.drawMode) {
                        fragments.push('Sơ đồ đang đồng bộ với dữ liệu hiện tại.');
                    }

                    statusText.textContent = fragments.join(' ');
                }
            };

            const setDirty = (value = true) => {
                state.dirty = value;

                if (statusText) {
                    statusText.textContent = value
                        ? 'Bạn đang có thay đổi chưa lưu. Bấm "Lưu layout" để ghi xuống database.'
                        : 'Sơ đồ đang đồng bộ với dữ liệu hiện tại.';
                }
            };

            const updateSelectionBox = () => {
                const item = getItem();

                if (!item || !selectionBox) {
                    selectionBox?.classList.remove('is-visible');
                    return;
                }

                selectionBox.classList.add('is-visible');
                selectionBox.style.left = `${item.x}px`;
                selectionBox.style.top = `${item.y}px`;
                selectionBox.style.width = `${item.width}px`;
                selectionBox.style.height = `${item.height}px`;
                selectionBox.style.transform = `rotate(${item.rotation}deg)`;
            };

            const renderLegend = () => {
                if (!legendRoot) {
                    return;
                }

                const grouped = state.items.reduce((carry, item) => {
                    carry[item.item_type] = carry[item.item_type] || 0;
                    carry[item.item_type] += 1;
                    return carry;
                }, {});

                legendRoot.innerHTML = '';

                Object.entries(grouped).sort((a, b) => a[0].localeCompare(b[0])).forEach(([itemType, count]) => {
                    const meta = metaByType[itemType] || { label: itemType, icon: 'ti ti-map-pin' };
                    const div = document.createElement('div');
                    div.className = 'warehouse-map-legend-item';
                    div.innerHTML = `<span><i class="${meta.icon}"></i>${meta.label}</span><strong>${count}</strong>`;
                    legendRoot.appendChild(div);
                });
            };

            const renderStats = () => {
                if (totalStat) {
                    totalStat.textContent = String(state.items.length);
                }

                if (linkedStat) {
                    linkedStat.textContent = String(state.items.filter((item) => item.location_id).length);
                }

                if (unlinkedStat) {
                    unlinkedStat.textContent = String(state.items.filter((item) => !item.location_id).length);
                }
            };

            const renderInspector = () => {
                const item = getItem();

                emptyInspector?.classList.toggle('warehouse-hidden', Boolean(item));
                inspectorContent?.classList.toggle('warehouse-hidden', !item);

                if (!item) {
                    if (capacityElement) {
                        capacityElement.textContent = '-';
                    }
                    return;
                }

                const typeMeta = metaByType[item.item_type] || { label: item.item_type };
                const locationId = item.location_id ? String(item.location_id) : '';

                inspectorFieldElements.forEach((element) => {
                    const field = element.dataset.inspectorField;

                    if (field === 'location_id') {
                        element.value = locationId;
                        return;
                    }

                    if (field === 'is_clickable') {
                        element.checked = Boolean(item.is_clickable);
                        return;
                    }

                    if (field === 'item_type_label') {
                        element.value = typeMeta.label;
                        return;
                    }

                    element.value = item[field] ?? '';
                });

                inspectorMetaElements.forEach((element) => {
                    const metaKey = element.dataset.inspectorMeta;
                    const metaValue = item.meta_json?.[metaKey];

                    if (element.type === 'checkbox') {
                        element.checked = Boolean(metaValue);
                    } else {
                        element.value = metaValue ?? '';
                    }
                });

                const moduleType = getModuleType(item);
                sectionElements.forEach((section) => {
                    section.classList.toggle('warehouse-hidden', section.dataset.inspectorSection !== moduleType);
                });

                if (capacityElement) {
                    const capacity = getCapacity(item);
                    capacityElement.textContent = capacity ? `${capacity} vị trí logic` : '-';
                }
            };

            const renderViewDetail = () => {
                if (!viewDetail) {
                    return;
                }

                const item = getItem();
                viewDetail.replaceChildren();

                const title = document.createElement('strong');
                const description = document.createElement('span');

                if (!item) {
                    title.textContent = 'Đang xem sơ đồ';
                    description.textContent = 'Bấm vào một vùng trên sơ đồ hoặc một vị trí trong cây để xem liên kết tương ứng.';
                } else {
                    const typeMeta = metaByType[item.item_type] || { label: item.item_type };
                    title.textContent = item.label || typeMeta.label || 'Vùng chưa đặt tên';
                    description.textContent = item.location_id
                        ? `Đã gắn với ${getLocationLabel(item.location_id)}`
                        : `${typeMeta.label || item.item_type} chưa gắn location.`;
                }

                viewDetail.append(title, description);
            };

            const createItemElement = (item) => {
                const meta = metaByType[item.item_type] || { label: item.item_type, icon: 'ti ti-map-pin' };
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `warehouse-map-canvas__item${String(state.selectedId) === String(item.id) ? ' is-selected' : ''}${item.location_id ? ' is-linked' : ''}${item.shape_type === 'label' ? ' warehouse-map-canvas__item--label' : ''}`;
                button.dataset.mapItemId = String(item.id);
                button.style.left = `${item.x}px`;
                button.style.top = `${item.y}px`;
                button.style.width = `${item.width}px`;
                button.style.height = `${item.height}px`;
                button.style.background = item.shape_type === 'label' ? 'transparent' : item.color;
                button.style.zIndex = String(item.z_index || 1);
                button.style.transform = `rotate(${item.rotation}deg)`;

                const locationLabel = item.location_id ? getLocationLabel(item.location_id) : meta.label;
                button.innerHTML = `
                    <div class="warehouse-map-canvas__content">
                        <div class="warehouse-map-canvas__title">
                            <i class="${meta.icon}"></i>
                            <span>${item.label || meta.label}</span>
                        </div>
                        <div class="warehouse-map-canvas__meta">${locationLabel}</div>
                    </div>
                `;

                if (mapEditorConfig.can_manage && isEditMode()) {
                    const handle = document.createElement('span');
                    handle.className = 'warehouse-map-canvas__handle';
                    handle.dataset.mapResizeHandle = '1';
                    button.appendChild(handle);
                }

                return button;
            };

            const renderCanvas = () => {
                if (!canvas) {
                    return;
                }

                canvas.querySelectorAll('[data-map-item-id]').forEach((element) => element.remove());
                state.items
                    .slice()
                    .sort((a, b) => (a.z_index || 0) - (b.z_index || 0))
                    .forEach((item) => canvas.appendChild(createItemElement(item)));

                canvasWrap.style.transform = `scale(${state.zoom})`;
                updateSelectionBox();
                renderStats();
                renderLegend();
                renderInspector();
                renderViewDetail();
                updateModeState();

                if (!state.didInitialScroll && viewport && state.items.length > 0) {
                    const selectedItem = getItem();
                    const targetItem = selectedItem || state.items.slice().sort((a, b) => (a.x + a.y) - (b.x + b.y))[0];
                    viewport.scrollLeft = Math.max(0, targetItem.x - 48);
                    viewport.scrollTop = Math.max(0, targetItem.y - 48);
                    state.didInitialScroll = true;
                }
            };

            const syncTreeWithSelectedItem = (item, scroll = false) => {
                if (item?.location_id) {
                    setActiveLocationNode(item.location_id, scroll);
                    return;
                }

                if (selectedLocationId) {
                    setActiveLocationNode(selectedLocationId, false);
                }
            };

            const selectItem = (itemId, scrollTree = false) => {
                state.selectedId = itemId ? String(itemId) : null;
                renderCanvas();
                syncTreeWithSelectedItem(getItem(), scrollTree);
            };

            const getCanvasPoint = (clientX, clientY) => {
                const rect = canvas.getBoundingClientRect();
                return {
                    x: clamp((clientX - rect.left) / state.zoom, 0, state.map.width),
                    y: clamp((clientY - rect.top) / state.zoom, 0, state.map.height),
                };
            };

            const createItemFromTool = (toolKey, point) => {
                if (!mapEditorConfig.can_manage || !isEditMode()) {
                    return null;
                }

                const tool = toolsByKey[toolKey];

                if (!tool) {
                    return null;
                }

                const snappedPoint = snapPoint(point);

                const item = normalizeItem({
                    id: `tmp-${Date.now()}-${Math.random().toString(36).slice(2)}`,
                    item_type: tool.item_type,
                    label: tool.label,
                    shape_type: tool.shape_type,
                    x: clamp(snapValue(snappedPoint.x - (tool.width / 2)), 0, Math.max(0, state.map.width - tool.width)),
                    y: clamp(snapValue(snappedPoint.y - (tool.height / 2)), 0, Math.max(0, state.map.height - tool.height)),
                    width: tool.width,
                    height: tool.height,
                    rotation: 0,
                    color: tool.color,
                    z_index: state.items.length + 1,
                    is_clickable: true,
                    meta_json: cloneDeep(tool.meta_json),
                }, state.items.length);

                state.items.push(item);
                state.selectedId = String(item.id);
                setDirty(true);
                renderCanvas();
                return item;
            };

            const applyResizeRules = (item) => {
                const moduleType = getModuleType(item);

                if (moduleType === 'pallet_rack') {
                    const bayCount = Math.max(1, Math.round(item.width / 60));
                    item.meta_json.bay_count = bayCount;
                    item.width = Math.max(120, bayCount * 60);
                    if (state.snapEnabled) {
                        item.x = snapValue(item.x);
                    }
                    return;
                }

                if (moduleType === 'simple_shelf') {
                    const binCount = Math.max(1, Math.round(item.width / 56));
                    item.meta_json.bin_count_per_level = binCount;
                    item.width = Math.max(112, binCount * 56);
                    if (state.snapEnabled) {
                        item.x = snapValue(item.x);
                    }
                    return;
                }

                if (moduleType === 'floor_pallet_area') {
                    const columnCount = Math.max(1, Math.round(item.width / 48));
                    const rowCount = Math.max(1, Math.round(item.height / 42));
                    item.meta_json.column_count = columnCount;
                    item.meta_json.row_count = rowCount;
                    item.width = Math.max(96, columnCount * 48);
                    item.height = Math.max(84, rowCount * 42);
                }

                if (state.snapEnabled) {
                    item.x = snapValue(item.x);
                    item.y = snapValue(item.y);
                }
            };

            const beginPointerInteraction = (event, mode, itemId) => {
                const item = getItem(itemId);
                if (!item) {
                    return;
                }

                const point = getCanvasPoint(event.clientX, event.clientY);
                state.drag = {
                    mode,
                    itemId: String(item.id),
                    startPointerX: point.x,
                    startPointerY: point.y,
                    startX: item.x,
                    startY: item.y,
                    startWidth: item.width,
                    startHeight: item.height,
                };

                event.preventDefault();
            };

            canvas?.addEventListener('pointerdown', (event) => {
                const itemElement = event.target.closest('[data-map-item-id]');
                if (!itemElement) {
                    return;
                }

                const itemId = itemElement.dataset.mapItemId;
                selectItem(itemId, true);

                if (!mapEditorConfig.can_manage || !isEditMode()) {
                    return;
                }

                const isResize = event.target.closest('[data-map-resize-handle]');
                beginPointerInteraction(event, isResize ? 'resize' : 'move', itemId);
            });

            window.addEventListener('pointermove', (event) => {
                if (!state.drag) {
                    return;
                }

                const item = getItem(state.drag.itemId);
                if (!item) {
                    return;
                }

                const point = getCanvasPoint(event.clientX, event.clientY);
                const deltaX = point.x - state.drag.startPointerX;
                const deltaY = point.y - state.drag.startPointerY;

                if (state.drag.mode === 'move') {
                    item.x = clamp(snapValue(state.drag.startX + deltaX), 0, Math.max(0, state.map.width - item.width));
                    item.y = clamp(snapValue(state.drag.startY + deltaY), 0, Math.max(0, state.map.height - item.height));
                } else {
                    item.width = clamp(snapValue(state.drag.startWidth + deltaX), 36, state.map.width);
                    item.height = clamp(snapValue(state.drag.startHeight + deltaY), 28, state.map.height);
                    applyResizeRules(item);
                    item.x = clamp(item.x, 0, Math.max(0, state.map.width - item.width));
                    item.y = clamp(item.y, 0, Math.max(0, state.map.height - item.height));
                }

                setDirty(true);
                renderCanvas();
            });

            window.addEventListener('pointerup', () => {
                state.drag = null;
            });

            canvas?.addEventListener('dragover', (event) => {
                if (!mapEditorConfig.can_manage || !isEditMode()) {
                    return;
                }

                event.preventDefault();
            });

            canvas?.addEventListener('drop', (event) => {
                if (!mapEditorConfig.can_manage || !isEditMode()) {
                    return;
                }

                event.preventDefault();
                const toolKey = event.dataTransfer?.getData('text/plain') || state.draggedTool;

                if (!toolKey) {
                    return;
                }

                createItemFromTool(toolKey, getCanvasPoint(event.clientX, event.clientY));
            });

            canvas?.addEventListener('click', (event) => {
                const clickedItem = event.target.closest('[data-map-item-id]');

                if (clickedItem) {
                    return;
                }

                if (isEditMode() && state.drawMode && state.drawTool) {
                    createItemFromTool(state.drawTool, getCanvasPoint(event.clientX, event.clientY));
                    return;
                }

                state.selectedId = null;
                renderCanvas();
            });

            modeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const nextMode = button.dataset.mapModeButton || 'view';
                    state.mode = nextMode === 'edit' && mapEditorConfig.can_manage ? 'edit' : 'view';
                    renderCanvas();
                });
            });

            editorRoot.querySelectorAll('[data-map-tool]').forEach((toolButton) => {
                toolButton.addEventListener('dragstart', (event) => {
                    if (!isEditMode()) {
                        event.preventDefault();
                        return;
                    }

                    const toolKey = toolButton.dataset.mapTool;
                    state.draggedTool = toolKey;
                    event.dataTransfer?.setData('text/plain', toolKey);
                });

                toolButton.addEventListener('dragend', () => {
                    state.draggedTool = null;
                });

                toolButton.addEventListener('click', () => {
                    if (!mapEditorConfig.can_manage || !isEditMode()) {
                        return;
                    }

                    if (state.drawMode) {
                        state.drawTool = toolButton.dataset.mapTool;
                        updateModeState();
                        return;
                    }

                    createItemFromTool(toolButton.dataset.mapTool, {
                        x: (viewport.scrollLeft / state.zoom) + (viewport.clientWidth / state.zoom / 2),
                        y: (viewport.scrollTop / state.zoom) + (viewport.clientHeight / state.zoom / 2),
                    });
                });
            });

            drawModeButton?.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                state.drawMode = !state.drawMode;

                if (!state.drawMode) {
                    state.drawTool = null;
                } else if (!state.drawTool) {
                    state.drawTool = editorRoot.querySelector('[data-map-tool]')?.dataset.mapTool || null;
                }

                updateModeState();
            });

            snapToggleButton?.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                state.snapEnabled = !state.snapEnabled;
                updateModeState();
            });

            gridSizeSelect?.addEventListener('change', () => {
                if (!isEditMode()) {
                    return;
                }

                state.gridSize = Math.max(4, Number(gridSizeSelect.value || 24));
                updateModeState();
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && state.drawMode) {
                    state.drawMode = false;
                    state.drawTool = null;
                    updateModeState();
                }
            });

            inspectorFieldElements.forEach((element) => {
                const eventName = element.tagName === 'SELECT' || element.type === 'checkbox' || element.type === 'color' ? 'change' : 'input';
                element.addEventListener(eventName, () => {
                    if (!isEditMode()) {
                        return;
                    }

                    const item = getItem();

                    if (!item) {
                        return;
                    }

                    const field = element.dataset.inspectorField;

                    if (field === 'location_id') {
                        item.location_id = element.value ? Number(element.value) : null;
                        setActiveLocationNode(item.location_id, false);
                    } else if (field === 'is_clickable') {
                        item.is_clickable = Boolean(element.checked);
                    } else if (field === 'item_type_label') {
                        return;
                    } else if (['x', 'y', 'width', 'height', 'rotation'].includes(field)) {
                        item[field] = Number(element.value || 0);
                        if (field === 'width' || field === 'height') {
                            item[field] = Math.max(field === 'width' ? 36 : 28, item[field]);
                            applyResizeRules(item);
                        } else if ((field === 'x' || field === 'y') && state.snapEnabled) {
                            item[field] = snapValue(item[field]);
                        }
                    } else {
                        item[field] = element.value;
                    }

                    setDirty(true);
                    renderCanvas();
                });
            });

            inspectorMetaElements.forEach((element) => {
                const eventName = element.type === 'checkbox' ? 'change' : 'input';
                element.addEventListener(eventName, () => {
                    if (!isEditMode()) {
                        return;
                    }

                    const item = getItem();

                    if (!item) {
                        return;
                    }

                    const metaKey = element.dataset.inspectorMeta;
                    item.meta_json = item.meta_json || {};
                    item.meta_json[metaKey] = element.type === 'checkbox'
                        ? Boolean(element.checked)
                        : (element.value === '' ? null : (Number.isNaN(Number(element.value)) || element.value.trim() === '' ? element.value : Number(element.value)));

                    if (['bay_count', 'bin_count_per_level', 'column_count'].includes(metaKey)) {
                        applyResizeRules(item);
                    }

                    setDirty(true);
                    renderCanvas();
                });
            });

            duplicateButton?.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                const item = getItem();
                if (!item) {
                    notify('Bạn cần chọn một item trước khi duplicate.', 'error');
                    return;
                }

                const copy = normalizeItem({
                    ...cloneDeep(item),
                    id: `tmp-${Date.now()}-${Math.random().toString(36).slice(2)}`,
                    location_id: null,
                    label: `${item.label} copy`,
                    x: clamp(snapValue(item.x + 28), 0, Math.max(0, state.map.width - item.width)),
                    y: clamp(snapValue(item.y + 24), 0, Math.max(0, state.map.height - item.height)),
                    z_index: state.items.length + 1,
                }, state.items.length);

                state.items.push(copy);
                selectItem(copy.id, false);
                setDirty(true);
            });

            batchDuplicateButton?.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                const item = getItem();
                if (!item) {
                    notify('Bạn cần chọn một item để nhân nhiều.', 'error');
                    return;
                }

                const duplicateConfig = Object.fromEntries(duplicateElements.map((element) => [element.dataset.inspectorDuplicate, element.value]));
                const count = Math.max(1, Number(duplicateConfig.count || 1));
                const offsetX = Number(duplicateConfig.offset_x || 0);
                const offsetY = Number(duplicateConfig.offset_y || 0);
                const prefix = duplicateConfig.prefix || 'COPY-';

                let lastCopy = null;

                for (let index = 1; index <= count; index += 1) {
                    const copy = normalizeItem({
                        ...cloneDeep(item),
                        id: `tmp-${Date.now()}-${Math.random().toString(36).slice(2)}`,
                        location_id: null,
                        label: `${prefix}${index}`,
                        x: clamp(snapValue(item.x + (offsetX * index)), 0, Math.max(0, state.map.width - item.width)),
                        y: clamp(snapValue(item.y + (offsetY * index)), 0, Math.max(0, state.map.height - item.height)),
                        z_index: state.items.length + 1,
                    }, state.items.length);

                    state.items.push(copy);
                    lastCopy = copy;
                }

                if (lastCopy) {
                    selectItem(lastCopy.id, false);
                    setDirty(true);
                }
            });

            deleteButton?.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                if (!state.selectedId) {
                    notify('Bạn cần chọn một item trước khi xoá.', 'error');
                    return;
                }

                state.items = state.items.filter((item) => String(item.id) !== String(state.selectedId));
                state.selectedId = null;
                setDirty(true);
                renderCanvas();
            });

            focusTreeButton?.addEventListener('click', () => {
                const item = getItem();
                if (!item?.location_id) {
                    notify('Item này chưa gắn location.', 'error');
                    return;
                }

                const node = getVisibleNode(item.location_id);
                if (node) {
                    node.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    setActiveLocationNode(item.location_id, false);
                }
            });

            zoomInButton?.addEventListener('click', () => {
                state.zoom = clamp(Number((state.zoom + 0.1).toFixed(2)), 0.5, 2);
                renderCanvas();
            });

            zoomOutButton?.addEventListener('click', () => {
                state.zoom = clamp(Number((state.zoom - 0.1).toFixed(2)), 0.5, 2);
                renderCanvas();
            });

            saveButton?.addEventListener('click', async () => {
                if (!mapEditorConfig.can_manage || !isEditMode() || !state.map.sync_url) {
                    return;
                }

                saveButton.disabled = true;

                try {
                    const response = await fetch(state.map.sync_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            items: state.items.map((item, index) => ({
                                id: typeof item.id === 'number' ? item.id : null,
                                location_id: item.location_id || null,
                                item_type: item.item_type,
                                label: item.label,
                                shape_type: item.shape_type,
                                x: Math.round(item.x),
                                y: Math.round(item.y),
                                width: Math.round(item.width),
                                height: Math.round(item.height),
                                rotation: Number(item.rotation || 0),
                                color: item.color,
                                z_index: index + 1,
                                is_clickable: Boolean(item.is_clickable),
                                meta_json: cloneDeep(item.meta_json || {}),
                            })),
                        }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        const message = payload?.message || payload?.error || 'Không thể lưu layout sơ đồ kho.';
                        throw new Error(message);
                    }

                    state.items = (payload.data || []).map((item, index) => normalizeItem(item, index));
                    if (state.selectedId) {
                        const selected = getItem(state.selectedId);
                        state.selectedId = selected ? String(selected.id) : null;
                    }

                    setDirty(false);
                    renderCanvas();
                    notify(payload.message || 'Đã lưu bố cục sơ đồ kho.');
                } catch (error) {
                    notify(error.message || 'Không thể lưu bố cục sơ đồ kho.', 'error');
                } finally {
                    saveButton.disabled = false;
                }
            });

            document.addEventListener('click', (event) => {
                const node = event.target.closest('[data-location-node]');

                if (!node) {
                    return;
                }

                if (event.target.closest('a, button, input, select, textarea, label')) {
                    return;
                }

                const locationId = Number(node.dataset.locationId);
                const linkedItem = state.items.find((item) => Number(item.location_id) === locationId);

                setActiveLocationNode(locationId, false);

                if (linkedItem) {
                    activateTab('maps');
                    selectItem(linkedItem.id, false);
                    const itemElement = canvas?.querySelector(`[data-map-item-id="${linkedItem.id}"]`);
                    itemElement?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                } else {
                    state.selectedId = null;
                    renderCanvas();
                }
            });

            if (state.selectedId) {
                selectItem(state.selectedId, false);
            } else {
                renderCanvas();
            }

            setDirty(false);
            updateModeState();
        })();
    </script>
@endsection
