@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $warehouseShow = \Botble\Inventory\Domains\Warehouse\Support\WarehouseShowViewData::make($warehouse, $locations)->toArray();
        $locationTypes = $warehouseShow['locationTypes'];
        $storageModeMeta = $warehouseShow['storageModeMeta'];
        $mapEditorTools = $warehouseShow['mapEditorTools'];
        $templates = $warehouseShow['templates'];
        $selectedMap = $warehouseShow['selectedMap'];
        $selectedMapItems = $warehouseShow['selectedMapItems'];
        $selectedLocation = $warehouseShow['selectedLocation'];
        $selectedLocationMapItem = $warehouseShow['selectedLocationMapItem'];
        $mapItemByLocation = $warehouseShow['mapItemByLocation'];
        $mapWidth = $warehouseShow['mapWidth'];
        $mapHeight = $warehouseShow['mapHeight'];
        $systemLocationCount = $warehouseShow['systemLocationCount'];
        $mappedLocationCount = $warehouseShow['mappedLocationCount'];
        $rootLocations = $warehouseShow['rootLocations'];
        $warehouseSetting = $warehouseShow['warehouseSetting'];
        $selectedStorageMode = $warehouseShow['selectedStorageMode'];
        $mapBlueprints = $warehouseShow['mapBlueprints'];
        $pallets = $warehouseShow['pallets'];
        $canManageMaps = $warehouseShow['canManageMaps'];
        $selectedMapBackgroundUrl = $warehouseShow['selectedMapBackgroundUrl'];
        $mapLocationOptions = $warehouseShow['mapLocationOptions'];
        $selectedMapItemsPayload = $warehouseShow['selectedMapItemsPayload'];
        $mapLegendGroups = $warehouseShow['mapLegendGroups'];
        $linkedLocationCount = $warehouseShow['linkedLocationCount'];
        $unlinkedLocationCount = $warehouseShow['unlinkedLocationCount'];
        $setupCheckpoints = $warehouseShow['setupCheckpoints'];
        $isWarehouseSetupReady = $warehouseShow['isWarehouseSetupReady'];
        $tabs = $warehouseShow['tabs'];
        $activeTab = $warehouseShow['activeTab'];
        $mapEditorConfig = $warehouseShow['mapEditorConfig'];
        $selectedMapId = $warehouseShow['selectedMapId'];
        $selectedLocationId = $warehouseShow['selectedLocationId'];
        $isMapFocusMode = $activeTab === 'maps' && $selectedMap;
        $palletStatusLabels = [
            'empty' => 'Trống',
            'open' => 'Đang mở',
            'in_use' => 'Đang dùng',
            'closed' => 'Đã đóng',
            'damaged' => 'Hư hỏng',
            'locked' => 'Đã khóa',
        ];
        $palletStatusBadges = [
            'empty' => 'bg-secondary-lt text-secondary',
            'open' => 'bg-info-lt text-info',
            'in_use' => 'bg-success-lt text-success',
            'closed' => 'bg-primary-lt text-primary',
            'damaged' => 'bg-danger-lt text-danger',
            'locked' => 'bg-warning-lt text-warning',
        ];
        $warehouseProductRows = $warehouse->warehouseProducts ?? collect();
        $warehouseProductLocationById = $locations->keyBy('id');
        $warehouseProductStockMap = $warehouseProductStock instanceof \Illuminate\Support\Collection ? $warehouseProductStock : collect($warehouseProductStock ?? []);
        $warehouseProductUnitLabels = $warehouseProductUnitLabels instanceof \Illuminate\Support\Collection ? $warehouseProductUnitLabels : collect($warehouseProductUnitLabels ?? []);
        $formatWarehouseQty = static function ($value): string {
            $formatted = number_format((float) ($value ?? 0), 4, '.', ',');

            return rtrim(rtrim($formatted, '0'), '.') ?: '0';
        };
        $warehouseProductStockTotals = $warehouseProductStockMap->reduce(static function (array $carry, $stock): array {
            $carry['quantity'] += (float) ($stock->quantity ?? 0);
            $carry['available'] += (float) ($stock->available_qty ?? 0);
            $carry['reserved'] += (float) ($stock->reserved_qty ?? 0);
            $carry['qc_hold'] += (float) ($stock->qc_hold_qty ?? 0);

            return $carry;
        }, ['quantity' => 0, 'available' => 0, 'reserved' => 0, 'qc_hold' => 0]);
        $warehouseProductLowStockCount = $warehouseProductRows->filter(static function ($warehouseProduct) use ($warehouseProductStockMap): bool {
            $reorderPoint = (float) ($warehouseProduct->reorder_point_qty ?? 0);

            if ($reorderPoint <= 0) {
                return false;
            }

            $stockKey = (int) $warehouseProduct->product_id . ':' . (int) ($warehouseProduct->product_variation_id ?? 0);
            $stock = $warehouseProductStockMap->get($stockKey);

            return (float) ($stock->available_qty ?? 0) <= $reorderPoint;
        })->count();
    @endphp

    <style>
        .warehouse-workspace { margin: -1.25rem; min-height: calc(100vh - 56px); padding: 28px; background: #f8fafc; }
        .warehouse-workspace.is-map-focus { padding: 8px; }
        .warehouse-workspace.is-map-focus .warehouse-hero,
        .warehouse-workspace.is-map-focus [data-map-setup-intro] { display: none !important; }
        .warehouse-workspace.is-map-focus .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card { margin: -8px; border: 0; border-radius: 0; box-shadow: none; }
        .warehouse-workspace.is-map-focus .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card > .warehouse-card__body { padding: 8px; }
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode .warehouse-map-canvas-panel__header,
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode .warehouse-map-stats,
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode .warehouse-map-view-detail,
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode details.warehouse-card.mt-3 { display: none !important; }
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode .warehouse-map-canvas-panel { border-radius: 16px; }
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode .warehouse-map-canvas-panel__body { padding: 0; }
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode .warehouse-map-stage { border: 0; border-radius: 16px; padding: 0; }
        .warehouse-workspace.is-map-focus .warehouse-map-editor.is-view-mode .warehouse-map-viewport { height: calc(100vh - 78px); min-height: calc(100vh - 78px); border-radius: 16px; }
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
        .warehouse-tree-node.is-hovered { border-color: rgba(59, 130, 246, .42); box-shadow: 0 14px 28px rgba(59, 130, 246, .12); transform: translateY(-1px); }
        .warehouse-tree-main { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 12px; align-items: start; cursor: pointer; }
        .warehouse-tree-icon { width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--tree-accent) 12%, #fff); color: var(--tree-accent); font-size: 1.15rem; }
        .warehouse-tree-title-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .warehouse-tree-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; color: #64748b; font-size: .84rem; }
        .warehouse-tree-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .warehouse-tree-children { display: grid; gap: 10px; margin-top: 10px; padding-left: 18px; border-left: 2px dashed rgba(148, 163, 184, .32); }
        .warehouse-empty-state { padding: 28px; border: 1px dashed #d8e1ee; border-radius: 22px; background: #f8fafc; text-align: center; color: #64748b; }
        .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card { margin: -18px -18px -10px; border-radius: 18px; }
        .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card > .warehouse-card__header { display: none; }
        .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card > .warehouse-card__body { padding: 8px; }
        .warehouse-map-editor { display: grid; grid-template-columns: 168px minmax(0, 1fr); grid-template-areas: "tools canvas"; gap: 10px; align-items: start; }
        .warehouse-map-editor.has-selected.is-edit-mode { grid-template-columns: 168px minmax(0, 1fr) 280px; grid-template-areas: "tools canvas inspector"; }
        .warehouse-map-editor.is-view-mode { grid-template-columns: 1fr; grid-template-areas: "canvas"; }
        .warehouse-map-editor.is-init-mode { grid-template-columns: 1fr; grid-template-areas: "canvas"; }
        .warehouse-map-editor.is-locked { pointer-events: none; user-select: none; }
        .warehouse-map-editor.is-view-mode .warehouse-map-toolbox,
        .warehouse-map-editor.is-view-mode .warehouse-map-sidebar,
        .warehouse-map-editor.is-view-mode .warehouse-map-inspector,
        .warehouse-map-editor.is-view-mode [data-edit-only],
        .warehouse-map-editor.is-edit-mode [data-view-only],
        .warehouse-map-editor.is-edit-mode .warehouse-map-sidebar,
        .warehouse-map-editor.is-edit-mode:not(.has-selected) .warehouse-map-inspector,
        .warehouse-map-editor.is-edit-mode .warehouse-map-canvas-panel__header,
        .warehouse-map-editor.is-init-mode .warehouse-map-toolbox,
        .warehouse-map-editor.is-init-mode .warehouse-map-sidebar,
        .warehouse-map-editor.is-init-mode .warehouse-map-inspector,
        .warehouse-map-editor.is-init-mode .warehouse-map-canvas-panel__header,
        .warehouse-map-editor.is-init-mode [data-edit-only],
        .warehouse-map-editor.is-init-mode [data-view-only],
        .warehouse-map-editor.is-init-mode [data-map-editor-only] { display: none !important; }
        .warehouse-map-editor.is-init-mode .warehouse-map-canvas-panel { max-width: 1180px; margin: 0 auto; width: 100%; }
        .warehouse-map-editor.is-init-mode .warehouse-map-canvas-panel { grid-column: 1 / -1; }
        .warehouse-map-editor.is-edit-mode .warehouse-map-view-detail,
        .warehouse-map-editor.is-edit-mode .warehouse-map-stats,
        .warehouse-map-editor.is-edit-mode details.warehouse-card.mt-3 { display: none !important; }
        .warehouse-map-toolbox,
        .warehouse-map-sidebar,
        .warehouse-map-canvas-panel,
        .warehouse-map-inspector { background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; box-shadow: 0 18px 44px rgba(15, 23, 42, .04); }
        .warehouse-map-toolbox { grid-area: tools; position: sticky; top: 8px; }
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
        .warehouse-map-toolbox__body { display: grid; gap: 10px; }
        .warehouse-map-canvas-panel__header,
        .warehouse-map-canvas-panel__body { padding-left: 10px; padding-right: 10px; }
        .warehouse-map-canvas-panel__header { padding-left: 12px; padding-right: 12px; }
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
        .warehouse-editor-tool { border: 1px solid #dbe4f0; background: #fff; border-radius: 14px; padding: 9px 10px; font-weight: 700; color: #0f172a; display: inline-flex; gap: 7px; align-items: center; cursor: grab; font-size: 12px; touch-action: none; user-select: none; }
        .warehouse-editor-tool.is-dragging { cursor: grabbing; opacity: .72; transform: scale(.98); }
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
        .warehouse-map-create-form { display: grid; gap: 14px; min-width: min(720px, 100%); }
        .warehouse-map-create-form--compact { min-width: 0; gap: 10px; }
        .warehouse-map-create-form--compact label { display: grid; gap: 6px; color: #334155; font-size: 12px; font-weight: 700; }
        .warehouse-map-create-form--compact .form-control,
        .warehouse-map-create-form--compact .form-select { min-height: 40px; border-radius: 14px; }
        .warehouse-map-create-form--compact .warehouse-field-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .warehouse-storage-mode-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .warehouse-storage-mode-card { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 12px; align-items: flex-start; padding: 14px; border: 1px solid #dbe4f0; border-radius: 18px; background: #fff; cursor: pointer; transition: .16s ease; }
        .warehouse-storage-mode-card input { margin-top: 4px; }
        .warehouse-storage-mode-card__icon { width: 38px; height: 38px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; color: #2563eb; background: #eff6ff; font-size: 20px; }
        .warehouse-storage-mode-card strong { display: block; color: #0f172a; font-weight: 800; }
        .warehouse-storage-mode-card small { display: block; margin-top: 4px; color: #64748b; line-height: 1.45; }
        .warehouse-storage-mode-card:has(input:checked) { border-color: #2563eb; box-shadow: 0 14px 30px rgba(37, 99, 235, .12); }
        .warehouse-storage-mode-card:has(input:checked) .warehouse-storage-mode-card__icon { color: #fff; background: #2563eb; }
        .warehouse-map-toolbar-card.is-hidden-by-mode { display: none; }
        .warehouse-map-stats { display: flex; gap: 8px; flex-wrap: wrap; }
        .warehouse-map-stat { border: 1px solid #e5e7eb; border-radius: 16px; padding: 10px 12px; background: #f8fafc; }
        .warehouse-map-stat span { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .warehouse-map-stat strong { margin-left: 6px; font-size: 16px; color: #0f172a; }
        .warehouse-map-status { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .warehouse-map-status .badge { border-radius: 999px; }
        .warehouse-map-stage { border: 1px solid #e2e8f0; border-radius: 22px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 8px; }
        .warehouse-map-viewport { position: relative; overflow: auto; width: 100%; min-height: 820px; height: calc(100vh - 150px); max-height: none; border-radius: 18px; border: 1px solid rgba(148, 163, 184, .24); background: #eff6ff; cursor: grab; }
        .warehouse-map-viewport.is-panning,
        .warehouse-map-viewport.is-panning .warehouse-map-canvas,
        .warehouse-map-viewport.is-panning .warehouse-map-canvas__item { cursor: grabbing; }
        .warehouse-map-editor.is-edit-mode .warehouse-map-viewport { min-height: 820px; height: calc(100vh - 82px); }
        .warehouse-map-canvas-wrap { position: relative; transform-origin: top left; transition: transform .16s ease; }
        .warehouse-map-canvas { position: relative; background-size: 32px 32px, 32px 32px, cover; background-position: 0 0, 0 0, center; background-repeat: repeat, repeat, no-repeat; }
        .warehouse-map-canvas.is-draw-mode { cursor: crosshair; }
        .warehouse-map-canvas::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255, 255, 255, .12) 0%, rgba(255, 255, 255, .28) 100%); pointer-events: none; }
        .warehouse-map-drop-hint { position: absolute; inset: 16px auto auto 16px; z-index: 5; padding: 8px 12px; border-radius: 999px; background: rgba(15, 23, 42, .74); color: #fff; font-size: 12px; font-weight: 700; box-shadow: 0 10px 24px rgba(15, 23, 42, .18); }
        .warehouse-map-editor.is-view-mode .warehouse-map-drop-hint { display: none; }
        .warehouse-map-editor.is-tool-dragging .warehouse-map-drop-hint { background: #2563eb; }
        .warehouse-map-editor.is-tool-dragging .warehouse-map-canvas { outline: 2px dashed rgba(37, 99, 235, .42); outline-offset: -8px; }
        .warehouse-map-canvas__item { position: absolute; display: flex; align-items: flex-end; justify-content: flex-start; padding: 12px; border-radius: 18px; border: 2px solid rgba(15, 23, 42, .08); box-shadow: 0 10px 24px rgba(15, 23, 42, .08); color: #0f172a; text-align: left; cursor: pointer; user-select: none; transition: box-shadow .18s ease, border-color .18s ease, transform .18s ease, filter .18s ease, opacity .18s ease; overflow: hidden; transform: rotate(var(--map-item-rotation, 0deg)) translateY(var(--map-item-offset-y, 0)) scale(var(--map-item-scale, 1)); transform-origin: center; }
        .warehouse-map-canvas__item::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,.16) 0%, rgba(255,255,255,.03) 100%); pointer-events: none; }
        .warehouse-map-canvas__item.is-rack-layout { background-image: linear-gradient(180deg, color-mix(in srgb, var(--mode-accent, #4f46e5) 12%, #fff), rgba(255,255,255,0)); }
        .warehouse-map-canvas__item.is-direct-layout { background-image: linear-gradient(180deg, rgba(59,130,246,.12), rgba(255,255,255,0)); }
        .warehouse-map-canvas__item.is-pallet-layout { background-image: linear-gradient(180deg, rgba(34,197,94,.16), rgba(255,255,255,0)); }
        .warehouse-map-canvas__item.is-selected { border-color: var(--mode-accent, #4f46e5); box-shadow: 0 18px 34px color-mix(in srgb, var(--mode-accent, #4f46e5) 24%, transparent); --map-item-offset-y: -1px; --map-item-scale: 1.005; }
        .warehouse-map-canvas__item.is-linked::after { content: ''; position: absolute; top: 10px; right: 10px; width: 10px; height: 10px; border-radius: 999px; background: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, .16); }
        .warehouse-map-canvas__item--aisle { border-color: transparent; box-shadow: none; }
        .warehouse-map-canvas__item--aisle.is-selected { border-color: var(--mode-accent, #4f46e5); box-shadow: 0 12px 24px color-mix(in srgb, var(--mode-accent, #4f46e5) 18%, transparent); }
        .warehouse-map-canvas__item--label { align-items: center; justify-content: center; background: transparent !important; border-style: dashed; box-shadow: none; }
        .warehouse-map-canvas__item--label .warehouse-map-canvas__content { display: flex; align-items: center; justify-content: center; height: 100%; }
        .warehouse-map-canvas__item--label .warehouse-map-canvas__title { font-size: 13px; letter-spacing: .02em; }
        .warehouse-map-canvas__content { display: grid; gap: 4px; position: relative; z-index: 2; width: 100%; text-shadow: 0 1px 2px rgba(255,255,255,.72); }
        .warehouse-map-canvas__title { display: flex; gap: 8px; align-items: center; font-weight: 800; line-height: 1.15; font-size: clamp(12px, 1.1vw, 16px); }
        .warehouse-map-canvas__title span { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .warehouse-map-canvas__meta { color: rgba(15, 23, 42, .84); font-size: 11px; font-weight: 800; line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .warehouse-map-canvas__item--simple_shelf { align-items: stretch; padding: 8px; }
        .warehouse-map-canvas__item--simple_shelf .warehouse-map-canvas__content { height: 100%; align-content: end; gap: 3px; }
        .warehouse-map-shelf-visual { display: none; }
        .warehouse-map-shelf-visual__badge,
        .warehouse-map-shelf-visual__levels,
        .warehouse-map-shelf-visual__level { display: none; }
        .warehouse-map-canvas__item--simple_shelf .warehouse-map-canvas__title { font-size: 12px; line-height: 1.15; }
        .warehouse-map-canvas__item--simple_shelf .warehouse-map-canvas__meta { font-size: 10px; line-height: 1.2; }
        .warehouse-map-canvas__shelf-summary { display: block; width: fit-content; color: #1d4ed8; font-size: 10px; font-weight: 900; line-height: 1.15; }
        .warehouse-map-canvas__shelf-levels { display: block; color: #1d4ed8; font-size: 11px; font-weight: 900; line-height: 1.15; }
        .warehouse-map-canvas__capacity-text { display: block; color: #0f172a; font-size: 11px; font-weight: 900; line-height: 1.15; }
        .warehouse-map-canvas__rack-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; color: #fff; font-size: 11px; font-weight: 900; width: fit-content; box-shadow: inset 0 1px 0 rgba(255,255,255,.28), 0 10px 20px rgba(15, 23, 42, .12); background: linear-gradient(135deg, var(--mode-accent, #2563eb), var(--mode-accent-2, #60a5fa)); }
        .warehouse-map-canvas__item.is-direct-layout .warehouse-map-canvas__rack-badge { background: linear-gradient(135deg, var(--mode-accent, #2563eb), var(--mode-accent-2, #60a5fa)); }
        .warehouse-map-canvas__item.is-pallet-layout .warehouse-map-canvas__rack-badge { background: linear-gradient(135deg, var(--mode-accent, #15803d), var(--mode-accent-2, #4ade80)); }
        .warehouse-map-canvas__item.is-rack-layout .warehouse-map-canvas__rack-grid { border-radius: 14px; background: rgba(255,255,255,.34); padding: 8px; border: 1px solid rgba(255,255,255,.44); }
        .warehouse-map-canvas__item.is-rack-layout:hover { --map-item-offset-y: -2px; --map-item-scale: 1.01; filter: saturate(1.03); }
        .warehouse-map-canvas__rack-grid { display: grid; grid-template-rows: repeat(4, 1fr); gap: 4px; margin-top: 6px; }
        .warehouse-map-canvas__item.is-rack-layout .warehouse-map-canvas__rack-row { padding: 6px 8px; border-radius: 12px; background: rgba(255,255,255,.52); border: 1px solid rgba(255,255,255,.48); }
        .warehouse-map-canvas__rack-row { display: grid; grid-template-columns: 56px minmax(0, 1fr); gap: 6px; align-items: center; }
        .warehouse-map-canvas__rack-row-label { font-size: 10px; font-weight: 800; color: rgba(15, 23, 42, .72); text-transform: uppercase; }
        .warehouse-map-canvas__rack-slots { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 4px; }
        .warehouse-map-canvas__rack-slot { min-height: 18px; border-radius: 8px; border: 1px solid rgba(255,255,255,.74); background: rgba(255,255,255,.54); display: inline-flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 900; color: rgba(15, 23, 42, .8); transition: transform .18s ease, background .18s ease, box-shadow .18s ease; }
        .warehouse-map-canvas__rack-slot.is-hidden { visibility: hidden; }
        .warehouse-map-canvas__item.is-rack-layout .warehouse-map-canvas__rack-slot:hover { transform: translateY(-1px) scale(1.02); background: rgba(255,255,255,.82); box-shadow: 0 8px 18px rgba(15, 23, 42, .08); }
        .warehouse-map-canvas__handle { position: absolute; width: 18px; height: 18px; border-radius: 999px; background: #4f46e5; border: 3px solid #fff; box-shadow: 0 8px 16px rgba(79, 70, 229, .24); cursor: ns-resize; z-index: 3; }
        .warehouse-map-canvas__handle--start { left: 50%; top: -9px; transform: translateX(-50%); }
        .warehouse-map-canvas__handle--end { left: 50%; bottom: -9px; transform: translateX(-50%); }
        .warehouse-map-canvas__item--aisle.is-aisle-horizontal .warehouse-map-canvas__handle { cursor: ew-resize; }
        .warehouse-map-canvas__item.is-resizing { transition: none; }
        .warehouse-map-canvas__selection { position: absolute; border: 2px dashed rgba(79, 70, 229, .42); border-radius: 18px; pointer-events: none; display: none; }
        .warehouse-map-canvas__selection.is-visible { display: block; }
        .warehouse-map-legend { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .warehouse-map-legend-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 16px; border: 1px solid #e5e7eb; background: #fff; transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
        .warehouse-map-legend-item.is-hovered { transform: translateY(-1px); border-color: rgba(59, 130, 246, .36); box-shadow: 0 12px 24px rgba(59, 130, 246, .10); }
        .warehouse-map-popover { position: fixed; z-index: 9998; max-width: 320px; padding: 12px 14px; border-radius: 18px; background: rgba(15, 23, 42, .96); color: #fff; box-shadow: 0 24px 42px rgba(15, 23, 42, .26); pointer-events: none; opacity: 0; transform: translateY(4px); transition: opacity .16s ease, transform .16s ease; }
        .warehouse-map-popover::after { content: ''; position: absolute; left: -7px; top: 20px; width: 14px; height: 14px; background: rgba(15, 23, 42, .96); transform: rotate(45deg); border-radius: 2px; }
        .warehouse-map-popover.is-visible { opacity: 1; transform: translateY(0); }
        .warehouse-map-popover__title { display: flex; gap: 8px; align-items: center; font-weight: 800; }
        .warehouse-map-popover__meta { display: grid; gap: 6px; margin-top: 8px; color: rgba(226, 232, 240, .86); font-size: 12px; }
        .warehouse-map-popover__chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .warehouse-map-canvas__hover-ring { position: absolute; pointer-events: none; z-index: 7; border-radius: 20px; border: 2px solid rgba(59, 130, 246, .42); box-shadow: 0 0 0 6px rgba(59, 130, 246, .10); opacity: 0; transform: scale(.98); transition: opacity .16s ease, transform .16s ease; }
        .warehouse-map-canvas__hover-ring.is-visible { opacity: 1; transform: scale(1); }
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
        .warehouse-map-detail-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .warehouse-map-detail-card { padding: 14px; border: 1px solid #e2e8f0; border-radius: 18px; background: #f8fafc; }
        .warehouse-map-detail-card span { display: block; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
        .warehouse-map-detail-card strong { display: block; margin-top: 6px; color: #0f172a; font-size: 22px; line-height: 1.1; }
        .warehouse-map-detail-list { display: grid; gap: 10px; }
        .warehouse-map-detail-row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid #eef2f7; }
        .warehouse-map-detail-row span { color: #64748b; }
        .warehouse-map-detail-row strong { color: #0f172a; text-align: right; }
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
        .warehouse-map-view-detail { display: grid; gap: 4px; padding: 9px 12px; border-radius: 14px; background: #f8fafc; border: 1px solid #e5e7eb; color: #475569; }
        .warehouse-map-view-detail strong { color: #0f172a; }
        .warehouse-map-focus-bar { display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 8px; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 16px; background: rgba(255, 255, 255, .96); box-shadow: 0 14px 34px rgba(15, 23, 42, .06); }
        .warehouse-map-focus-bar__title { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; color: #475569; font-size: 13px; font-weight: 700; }
        .warehouse-map-focus-bar__actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .warehouse-map-collapse-panel { margin-bottom: 8px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 18px; background: #fff; box-shadow: 0 14px 34px rgba(15, 23, 42, .05); }
        .warehouse-map-collapse-panel__grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .warehouse-map-popup .modal-dialog { max-width: min(720px, calc(100vw - 32px)); }
        .warehouse-map-popup--templates .modal-dialog { max-width: min(860px, calc(100vw - 32px)); }
        .warehouse-map-popup .modal-content { border: 1px solid rgba(74, 85, 104, .18); border-radius: 16px; box-shadow: 0 24px 80px rgba(15, 20, 25, .18); color: #0f1419; overflow: hidden; }
        .warehouse-map-popup .modal-header,
        .warehouse-map-popup .modal-body,
        .warehouse-map-popup .modal-footer { padding-left: 24px; padding-right: 24px; }
        .warehouse-map-popup .modal-header { padding-top: 24px; }
        .warehouse-map-popup .modal-title { color: #0f1419; font-size: 1.25rem; font-weight: 600; letter-spacing: 0; }
        .warehouse-map-popup__metrics { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .warehouse-map-popup__templates { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .warehouse-map-popup .warehouse-map-stat,
        .warehouse-map-popup .warehouse-template-card,
        .warehouse-map-popup .warehouse-wizard-step,
        .warehouse-map-popup .warehouse-system-note { background: #f1f3f5; border: 0; border-radius: 10px; box-shadow: none; }
        .warehouse-map-preview-list { display: grid; gap: 10px; }
        .warehouse-map-preview-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; border: 1px solid #e5e7eb; border-radius: 16px; padding: 10px 12px; }
        .warehouse-map-preview-item span { color: #334155; font-weight: 700; }
        .warehouse-products-tab { display: grid; gap: 16px; }
        .warehouse-products-tab__header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap; padding: 20px; border: 1px solid rgba(74, 85, 104, .18); border-radius: 16px; background: #fff; }
        .warehouse-products-tab__title { margin: 4px 0 6px; color: #0f1419; font-size: 22px; font-weight: 700; letter-spacing: 0; }
        .warehouse-products-tab__hint { max-width: 760px; color: #4a5568; font-size: 14px; line-height: 1.55; }
        .warehouse-products-tab__actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .warehouse-products-tab__actions .btn { border-radius: 10px; min-height: 42px; font-weight: 700; }
        .warehouse-products-tab__stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .warehouse-products-tab__stat { padding: 16px; border: 1px solid rgba(74, 85, 104, .18); border-radius: 16px; background: #fff; }
        .warehouse-products-tab__stat span { display: block; color: #4a5568; font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .warehouse-products-tab__stat strong { display: block; margin-top: 8px; color: #0f1419; font-size: 24px; font-weight: 700; }
        .warehouse-products-table-card { overflow: hidden; border: 1px solid rgba(74, 85, 104, .18); border-radius: 16px; background: #fff; }
        .warehouse-products-table-card .table { margin: 0; color: #0f1419; }
        .warehouse-products-table-card thead th { background: #f1f3f5; border-bottom: 1px solid rgba(74, 85, 104, .18); color: #4a5568; font-size: 12px; font-weight: 700; letter-spacing: 0; text-transform: uppercase; white-space: nowrap; }
        .warehouse-products-table-card tbody td { border-color: rgba(74, 85, 104, .12); padding: 16px; vertical-align: top; }
        .warehouse-products-table-card tbody tr:last-child td { border-bottom: 0; }
        .warehouse-product-main { display: grid; gap: 6px; min-width: 260px; }
        .warehouse-product-name { color: #0f1419; font-weight: 750; line-height: 1.35; }
        .warehouse-product-meta,
        .warehouse-product-subgrid { display: flex; flex-wrap: wrap; gap: 6px; }
        .warehouse-product-chip { display: inline-flex; align-items: center; gap: 6px; width: fit-content; padding: 5px 8px; border-radius: 10px; background: #f1f3f5; color: #4a5568; font-size: 12px; font-weight: 700; }
        .warehouse-product-chip.is-primary { background: rgba(44, 94, 245, .1); color: #2c5ef5; }
        .warehouse-product-chip.is-warning { background: rgba(245, 158, 11, .12); color: #92400e; }
        .warehouse-product-chip.is-success { background: rgba(22, 163, 74, .1); color: #15803d; }
        .warehouse-product-stock { display: grid; gap: 8px; min-width: 180px; }
        .warehouse-product-stock__main { color: #0f1419; font-size: 20px; font-weight: 750; line-height: 1; }
        .warehouse-product-stock__main span { color: #4a5568; font-size: 12px; font-weight: 600; }
        .warehouse-product-detail-list { display: grid; gap: 7px; min-width: 210px; color: #4a5568; font-size: 13px; }
        .warehouse-product-detail-list div { display: flex; justify-content: space-between; gap: 16px; }
        .warehouse-product-detail-list strong { color: #0f1419; text-align: right; }
        .warehouse-hidden { display: none !important; }

        @media (max-width: 1480px) {
            .warehouse-map-editor { grid-template-columns: 164px minmax(0, 1fr); }
            .warehouse-map-editor.has-selected.is-edit-mode { grid-template-columns: 164px minmax(0, 1fr) 260px; }
            .warehouse-map-editor.is-view-mode { grid-template-columns: 1fr; }
            .warehouse-map-guide { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 1200px) {
            .warehouse-hero__grid,
            .warehouse-map-editor,
            .warehouse-map-editor.is-view-mode { grid-template-columns: 1fr; grid-template-areas: "tools" "canvas" "tree" "inspector"; }

            .warehouse-side-column { position: static; }
            .warehouse-map-toolbox { position: static; }
            .warehouse-storage-mode-grid { grid-template-columns: 1fr; }
            .warehouse-map-stats { grid-template-columns: 1fr; }
            .warehouse-map-legend { grid-template-columns: 1fr; }
            .warehouse-map-sidebar__body,
            .warehouse-map-inspector__body { max-height: none; }
            .warehouse-map-init__templates { grid-template-columns: 1fr; }
            .warehouse-map-popup__templates,
            .warehouse-map-popup__metrics { grid-template-columns: 1fr; }
            .warehouse-products-tab__stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 768px) {
            .warehouse-workspace { margin: -1rem; padding: 16px; }
            .warehouse-field-grid { grid-template-columns: 1fr; }
            .warehouse-map-detail-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .warehouse-map-guide { grid-template-columns: 1fr; }
            .warehouse-map-viewport,
            .warehouse-map-editor.is-edit-mode .warehouse-map-viewport { min-height: 620px; height: 72vh; }
            .warehouse-products-tab__stats { grid-template-columns: 1fr; }
            .warehouse-products-tab__header { padding: 16px; }
        }
    </style>

    @if(! $isWarehouseSetupReady)
        @include('plugins/inventory::warehouse.partials.unconfigured')
    @else
    @include('plugins/inventory::warehouse.partials.configured-glassline')
    <div class="warehouse-workspace {{ $isMapFocusMode ? 'is-map-focus' : '' }}">
        <div class="container-fluid warehouse-shell">
            <section class="warehouse-card warehouse-hero">
                <div class="warehouse-hero__grid">
                    <div class="warehouse-hero__content">
                        <div class="warehouse-kicker">Quản lý kho</div>
                        <h1 class="warehouse-title">{{ $warehouse->name }}</h1>
                        <div class="warehouse-subtitle">
                            Bảng vận hành kho đã sẵn sàng: theo dõi sơ đồ, vị trí, sản phẩm và pallet trong cùng một màn hình.
                        </div>
                        <div class="warehouse-header-meta">
                            <span class="badge bg-light text-dark"><i class="ti ti-building-warehouse me-1"></i>{{ $warehouse->code }}</span>
                            @if($warehouse->address)
                                <span class="badge bg-light text-dark"><i class="ti ti-map-pin me-1"></i>{{ $warehouse->address }}</span>
                            @endif
                            <span class="badge {{ $warehouse->status ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">{{ $warehouse->status ? 'Đang hoạt động' : 'Tạm tắt' }}</span>
                            <span class="badge {{ $isWarehouseSetupReady ? 'bg-primary-lt text-primary' : 'bg-warning-lt text-warning' }}">
                                {{ $isWarehouseSetupReady ? 'Đã sẵn sàng vận hành' : 'Cần setup thêm' }}
                            </span>
                        </div>
                        <div class="warehouse-mode-cards mt-4">
                            <div class="warehouse-mode-card {{ $selectedStorageMode === 'direct' ? 'is-active' : '' }}">
                                <div class="warehouse-mode-card__icon"><i class="{{ $storageModeMeta['direct']['icon'] }}"></i></div>
                                <div>
                                    <strong>{{ $storageModeMeta['direct']['short_label'] }}</strong>
                                    <div class="text-muted small">{{ $storageModeMeta['direct']['description'] }}</div>
                                </div>
                            </div>
                            <div class="warehouse-mode-card {{ $selectedStorageMode === 'pallet' ? 'is-active' : '' }}">
                                <div class="warehouse-mode-card__icon"><i class="{{ $storageModeMeta['pallet']['icon'] }}"></i></div>
                                <div>
                                    <strong>{{ $storageModeMeta['pallet']['short_label'] }}</strong>
                                    <div class="text-muted small">{{ $storageModeMeta['pallet']['description'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="warehouse-hero__sidebar">
                        <div class="warehouse-actions">
                            <a href="{{ route('inventory.warehouse.index') }}" class="btn btn-outline-secondary w-100"><i class="ti ti-arrow-left me-1"></i>Quay lại danh sách</a>
                            @if(auth()->user()?->hasPermission('warehouse.edit'))
                                <a href="{{ route('inventory.warehouse.edit', $warehouse) }}" class="btn btn-primary w-100"><i class="ti ti-pencil me-1"></i>Chỉnh sửa kho</a>
                                <button type="button" class="btn btn-outline-primary w-100" data-open-warehouse-settings><i class="ti ti-settings me-1"></i>Cài đặt kho</button>
                            @endif
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-6"><div class="warehouse-metric"><span>Location</span><strong>{{ number_format($warehouse->locations_count) }}</strong></div></div>
                            <div class="col-6"><div class="warehouse-metric"><span>Sơ đồ</span><strong>{{ number_format($warehouse->maps->count()) }}</strong></div></div>
                            <div class="col-6"><div class="warehouse-metric"><span>Pallet</span><strong>{{ number_format($pallets->count()) }}</strong></div></div>
                            <div class="col-6"><div class="warehouse-metric"><span>Mapped</span><strong>{{ number_format($mappedLocationCount) }}</strong></div></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="warehouse-card">
                <div class="warehouse-card__body">
                    <div class="warehouse-tabs d-none" data-warehouse-tabs>
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
                            <div class="col-xl-7">
                                <section class="warehouse-card">
                                    <div class="warehouse-card__header d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <h2 class="warehouse-card__title">Tổng quan kho</h2>
                                            <p class="warehouse-card__hint">Nhìn nhanh trạng thái setup, sức chứa logic và lối vào các bước tiếp theo.</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary" data-open-setup-wizard><i class="ti ti-route me-1"></i>Hướng dẫn setup</button>
                                    </div>
                                    <div class="warehouse-card__body">
                                        <div class="warehouse-overview-grid">
                                            <div class="warehouse-overview-card">
                                                <span>Kiểu kho</span>
                                                <strong>{{ $warehouseSetting?->warehouse_mode ?: 'simple' }}</strong>
                                                <small>{{ $warehouseSetting?->warehouse_mode === 'advanced' ? 'Kho nâng cao, linh hoạt hơn' : 'Kho đơn giản, dễ triển khai' }}</small>
                                            </div>
                                            <div class="warehouse-overview-card">
                                                <span>Mode lưu hàng</span>
                                                <strong>{{ $storageModeMeta[$selectedStorageMode]['short_label'] }}</strong>
                                                <small>{{ $storageModeMeta[$selectedStorageMode]['description'] }}</small>
                                            </div>
                                            <div class="warehouse-overview-card">
                                                <span>Setup state</span>
                                                <strong>{{ $isWarehouseSetupReady ? 'Sẵn sàng' : 'Chưa xong' }}</strong>
                                                <small>{{ $isWarehouseSetupReady ? 'Có thể vào editor map' : 'Chưa thể vào editor map' }}</small>
                                            </div>
                                            <div class="warehouse-overview-card">
                                                <span>Sơ đồ / Pallet</span>
                                                <strong>{{ number_format($warehouse->maps->count()) }} / {{ number_format($pallets->count()) }}</strong>
                                                <small>Số sơ đồ đang có và pallet đã tạo</small>
                                            </div>
                                        </div>
                                        <div class="warehouse-system-note mt-4">
                                            <strong>Gợi ý vận hành:</strong> direct mode nên bắt đầu với zone → shelf → bin. Pallet mode nên bắt đầu với zone → rack → slot.
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <div class="col-xl-5">
                                <section class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <h2 class="warehouse-card__title">Cài đặt hiện tại</h2>
                                        <p class="warehouse-card__hint">Những tuỳ chọn ảnh hưởng trực tiếp đến cách dựng location và map.</p>
                                    </div>
                                    <div class="warehouse-card__body warehouse-settings-list">
                                        <div class="warehouse-settings-item"><span>Kiểu kho</span><strong>{{ $warehouseSetting?->warehouse_mode ?: 'simple' }}</strong></div>
                                        <div class="warehouse-settings-item"><span>Dùng pallet</span><strong>{{ $warehouseSetting?->use_pallet ? 'Có' : 'Không' }}</strong></div>
                                        <div class="warehouse-settings-item"><span>Bắt buộc pallet</span><strong>{{ $warehouseSetting?->require_pallet ? 'Có' : 'Không' }}</strong></div>
                                        <div class="warehouse-settings-item"><span>Dùng QC</span><strong>{{ $warehouseSetting?->use_qc ? 'Có' : 'Không' }}</strong></div>
                                        <div class="warehouse-settings-item"><span>Dùng batch</span><strong>{{ $warehouseSetting?->use_batch ? 'Có' : 'Không' }}</strong></div>
                                        <div class="warehouse-settings-item"><span>Dùng serial</span><strong>{{ $warehouseSetting?->use_serial ? 'Có' : 'Không' }}</strong></div>
                                        <div class="warehouse-settings-item"><span>Dùng sơ đồ</span><strong>{{ $warehouseSetting?->use_map ? 'Có' : 'Không' }}</strong></div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'locations' ? 'is-active' : '' }}" data-panel-key="locations">
                        <div class="row g-4 align-items-start">
                            <div class="col-xl-4">
                                <div class="warehouse-side-column">
                                    <section class="warehouse-card">
                                        <div class="warehouse-card__header">
                                            <h2 class="warehouse-card__title">Cây vị trí chuẩn</h2>
                                            <p class="warehouse-card__hint">Dựng location theo mode đang chọn để map và vận hành đi đúng từ đầu.</p>
                                        </div>
                                        <div class="warehouse-card__body">
                                            <div class="warehouse-system-note mb-3">
                                                @if($selectedStorageMode === 'direct')
                                                    Direct mode nên tạo theo luồng: zone → shelf/rack → level → bin.
                                                @else
                                                    Pallet mode nên tạo theo luồng: zone → rack → level → slot.
                                                @endif
                                            </div>
                                            <div class="warehouse-preset-grid mb-4">
                                                @foreach($templates as $templateCode => $template)
                                                    @php
                                                        $firstPreviewNode = data_get($template, 'preview.0');
                                                        $templatePreview = collect(data_get($template, 'preview', []))
                                                            ->take(5)
                                                            ->map(fn ($node) => trim(data_get($node, 'code') . ' · ' . data_get($node, 'name')))
                                                            ->filter()
                                                            ->values()
                                                            ->all();
                                                        $presetPayload = [
                                                            'code' => data_get($firstPreviewNode, 'code'),
                                                            'name' => data_get($firstPreviewNode, 'name'),
                                                            'type' => data_get($firstPreviewNode, 'type'),
                                                            'parent_id' => '',
                                                        ];
                                                    @endphp
                                                    <button type="button" class="warehouse-preset-btn" data-location-preset='@json($presetPayload)'>
                                                        <strong>{{ $template['name'] }}</strong>
                                                        <span>{{ $template['description'] }}</span>
                                                        <small class="text-muted d-block mt-2">Bấm để điền nhanh form vị trí</small>
                                                        <small class="text-muted d-block">{{ implode(' · ', $templatePreview) }}</small>
                                                    </button>
                                                @endforeach
                                            </div>
                                            @if(auth()->user()?->hasPermission('warehouse.locations.manage'))
                                                <form id="warehouse-location-form" class="warehouse-location-form" method="POST" action="{{ route('inventory.warehouse.locations.store', $warehouse) }}" data-store-action="{{ route('inventory.warehouse.locations.store', $warehouse) }}">
                                                    @csrf
                                                    <input type="hidden" name="_method" value="POST" data-location-form-method>
                                                    <input type="hidden" name="status" value="0">
                                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                                        <div>
                                                            <h3 class="warehouse-card__title" data-location-form-title>Tạo vị trí mới</h3>
                                                            <div class="warehouse-card__hint" data-location-form-hint>Chọn vị trí cha nếu muốn tạo zone, rack, level hoặc bin bên trong một khu có sẵn.</div>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-secondary d-none" data-location-form-reset><i class="ti ti-refresh me-1"></i>Về tạo mới</button>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Vị trí cha</label>
                                                            <select name="parent_id" class="form-select" data-location-parent-select>
                                                                <option value="">Tạo ở cấp gốc</option>
                                                                @foreach($locations as $location)
                                                                    <option value="{{ $location->getKey() }}">{{ str_repeat('- ', max(0, $location->level - 1)) }}{{ $location->displayLabel() }}</option>
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
                                                                <div><strong>Tự suy đoán đúng kiểu vị trí</strong> theo luồng kho hiện tại, giúp user mới không phải nhớ thuật ngữ kỹ thuật.</div>
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
                                        <section class="warehouse-card warehouse-hidden" data-location-templates-panel>
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
                                                    @include('plugins/inventory::warehouse.partials.warehouse-render-item', [
                                                        'mode' => 'tree',
                                                        'location' => $location,
                                                        'level' => 0,
                                                        'warehouse' => $warehouse,
                                                        'context' => ['selectedLocation' => $selectedLocation, 'mapItemByLocation' => $mapItemByLocation],
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
                                <div class="warehouse-map-init mb-4" data-map-setup-intro>
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="warehouse-kicker">Lộ trình setup cho người mới</div>
                                            <h3 class="warehouse-card__title mt-2">Làm từng bước, không cần biết trước logic kho</h3>
                                            <p class="warehouse-card__hint mb-0">
                                                Khi kho chưa thiết lập gì, hệ thống sẽ mở pop-up hướng dẫn để người dùng hoàn tất cài đặt, tạo cây vị trí, rồi mới dựng sơ đồ và pallet nếu cần.
                                            </p>
                                        </div>
                                        <div class="warehouse-map-editor-state">{{ $isWarehouseSetupReady ? 'Đã sẵn sàng' : 'Chưa thiết lập xong' }}</div>
                                    </div>

                                    <div class="warehouse-map-guide mt-3">
                                        @foreach($setupCheckpoints as $checkpoint)
                                            <div class="warehouse-map-guide__item {{ $checkpoint['done'] ? 'is-done' : '' }}">
                                                <i class="{{ $checkpoint['key'] === 'settings' ? 'ti ti-settings' : ($checkpoint['key'] === 'locations' ? 'ti ti-layout-grid' : ($checkpoint['key'] === 'maps' ? 'ti ti-map-2' : 'ti ti-stack-2')) }}"></i>
                                                <div>
                                                    <strong>{{ $checkpoint['title'] }}</strong>
                                                    {{ $checkpoint['description'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="warehouse-map-init__actions mt-3">
                                        <button type="button" class="btn btn-outline-secondary" data-open-warehouse-settings>
                                            <i class="ti ti-settings me-1"></i>Cài đặt kho
                                        </button>
                                        <a href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => 'locations']) }}" class="btn btn-outline-secondary">
                                            <i class="ti ti-layout-grid me-1"></i>Thiết lập cây vị trí
                                        </a>
                                        <a href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => 'maps']) }}" class="btn btn-primary">
                                            <i class="ti ti-map-2 me-1"></i>Bắt đầu sơ đồ kho
                                        </a>
                                        @if($warehouseSetting?->use_pallet)
                                            <a href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => 'pallets']) }}" class="btn btn-outline-success">
                                                <i class="ti ti-stack-2 me-1"></i>Thiết lập pallet
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-outline-success" disabled>
                                                <i class="ti ti-stack-2 me-1"></i>Pallet là tùy chọn
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div
                                    class="warehouse-map-editor {{ $selectedMap ? 'is-view-mode' : 'is-init-mode' }}"
                                    data-warehouse-map-editor
                                    data-map-mode="{{ $selectedMap ? 'view' : 'init' }}"
                                >
                                    @if($canManageMaps)
                                        <aside class="warehouse-map-toolbox" data-edit-only>
                                            <div class="warehouse-map-toolbox__header">
                                                <h3 class="warehouse-card__title">Công cụ</h3>
                                                <p class="warehouse-card__hint">
                                                    {{ $storageModeMeta[$selectedStorageMode]['short_label'] ?? 'Không pallet' }}. Kéo module sang canvas hoặc bấm để thêm nhanh.
                                                </p>
                                            </div>
                                            <div class="warehouse-map-toolbox__body">
                                                <div>
                                                    <div class="warehouse-kicker mb-2">Thêm vùng</div>
                                                    <div class="warehouse-editor-toolbar__group" data-map-toolbox>
                                                        @foreach($mapEditorTools as $toolKey => $tool)
                                                            <button
                                                                type="button"
                                                                class="warehouse-editor-tool"
                                                                draggable="false"
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
                                                        <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-rotate-90><i class="ti ti-rotate-clockwise-2 me-1"></i>Xoay 90°</button>
                                                        <button type="button" class="btn btn-outline-danger warehouse-editor-action" data-map-delete><i class="ti ti-trash me-1"></i>Xóa vùng</button>
                                                    </div>
                                                </div>

                                                <details open>
                                                    <summary class="fw-bold text-muted">Diện tích map</summary>
                                                    <div class="warehouse-map-create-form warehouse-map-create-form--compact mt-2">
                                                        <div class="warehouse-field-grid">
                                                            <label>
                                                                Rộng
                                                                <input type="number" class="form-control" min="480" max="10000" step="1" value="{{ $mapWidth ?: 1200 }}" data-map-size-width>
                                                            </label>
                                                            <label>
                                                                Cao
                                                                <input type="number" class="form-control" min="320" max="10000" step="1" value="{{ $mapHeight ?: 800 }}" data-map-size-height>
                                                            </label>
                                                        </div>
                                                        <div class="text-muted small" data-map-size-help>
                                                            Canvas hiện tại: {{ number_format($mapWidth ?: 1200) }} x {{ number_format($mapHeight ?: 800) }} px.
                                                        </div>
                                                        <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-size-apply>
                                                            Áp dụng kích thước
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary warehouse-editor-action" data-map-auto-expand>
                                                            Tự nới theo bố cục
                                                        </button>
                                                    </div>
                                                </details>

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

                                                <details>
                                                    <summary class="fw-bold text-muted">Tạo sơ đồ mới</summary>
                                                    <form class="warehouse-map-create-form warehouse-map-create-form--compact mt-2" method="POST" action="{{ route('inventory.warehouse.maps.store', $warehouse) }}" data-map-create-form>
                                                        @csrf
                                                        <input type="hidden" name="map_type" value="floor_plan">
                                                        <input type="hidden" name="scale_ratio" value="1">
                                                        <input type="hidden" name="is_active" value="1">
                                                        <label>
                                                            Tên sơ đồ
                                                            <input type="text" name="name" class="form-control" placeholder="Sơ đồ mới" required>
                                                        </label>
                                                        <label>
                                                            Kiểu lưu hàng
                                                            <select name="storage_mode" class="form-select" required>
                                                                @foreach($storageModeMeta as $modeKey => $modeMeta)
                                                                    <option value="{{ $modeKey }}" @selected($selectedStorageMode === $modeKey)>{{ $modeMeta['short_label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                        <div class="warehouse-field-grid">
                                                            <label>
                                                                Rộng
                                                                <input type="number" name="width" class="form-control" min="480" max="10000" value="{{ $mapWidth ?: 1200 }}" required>
                                                            </label>
                                                            <label>
                                                                Cao
                                                                <input type="number" name="height" class="form-control" min="320" max="10000" value="{{ $mapHeight ?: 800 }}" required>
                                                            </label>
                                                        </div>
                                                        <button type="submit" class="btn btn-outline-primary warehouse-editor-action">Tạo sơ đồ</button>
                                                    </form>
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
                                                        @include('plugins/inventory::warehouse.partials.warehouse-render-item', [
                                                            'mode' => 'tree',
                                                            'location' => $location,
                                                            'level' => 0,
                                                            'warehouse' => $warehouse,
                                                            'context' => [
                                                                'selectedLocation' => $selectedLocation,
                                                                'mapItemByLocation' => $mapItemByLocation,
                                                            ],
                                                        ])
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </aside>

                                    <section class="warehouse-map-canvas-panel">
                                        @if(! $isWarehouseSetupReady)
                                            <div class="warehouse-map-lock" data-map-lock-overlay>
                                                <strong>Kho chưa setup xong</strong>
                                                <p>Chỉ xem hướng dẫn trước. Hoàn tất các bước bên trên để mở editor.</p>
                                                <button type="button" class="btn btn-primary" data-open-setup-wizard>Mở hướng dẫn setup</button>
                                            </div>
                                        @endif
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
                                                                <input type="hidden" name="scale_ratio" value="1">
                                                                <input type="hidden" name="is_active" value="1">
                                                                <input type="text" name="name" class="form-control" style="min-width: 180px;" placeholder="Tên sơ đồ mới" required>
                                                                <select name="storage_mode" class="form-select" style="min-width: 150px;" required>
                                                                    @foreach($storageModeMeta as $modeKey => $modeMeta)
                                                                        <option value="{{ $modeKey }}" @selected($selectedStorageMode === $modeKey)>{{ $modeMeta['short_label'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="number" name="width" class="form-control" style="width: 112px;" min="480" max="10000" value="{{ $mapWidth ?: 1200 }}" placeholder="Rộng" required>
                                                                <input type="number" name="height" class="form-control" style="width: 112px;" min="320" max="10000" value="{{ $mapHeight ?: 800 }}" placeholder="Cao" required>
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
                                                                    draggable="false"
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
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-rotate-90><i class="ti ti-rotate-clockwise-2 me-1"></i>Xoay 90°</button>
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
                                                        <span class="badge bg-primary-lt text-primary">
                                                            {{ $storageModeMeta[$selectedStorageMode]['short_label'] ?? 'Không pallet' }}
                                                        </span>
                                                        <span class="badge bg-light text-dark" data-map-size-label>
                                                            {{ number_format($mapWidth ?: 1200) }} x {{ number_format($mapHeight ?: 800) }} px
                                                        </span>
                                                        <span class="badge bg-dark-lt text-dark" data-map-zoom-indicator>Zoom 100%</span>
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

                                        @if($selectedMap && $canManageMaps)
                                            <form method="POST" action="{{ route('inventory.warehouse.maps.sync', [$warehouse, $selectedMap]) }}" data-map-sync-form class="d-none">
                                                @csrf
                                                @method('POST')
                                                <input type="hidden" name="items" data-map-sync-items>
                                            </form>
                                        @endif

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
                                                <div class="warehouse-map-focus-bar">
                                                    <div class="warehouse-map-focus-bar__title">
                                                        <span class="badge bg-light text-dark">{{ $selectedMap->name }}</span>
                                                        <span class="badge bg-primary-lt text-primary">{{ $storageModeMeta[$selectedStorageMode]['short_label'] ?? 'Không pallet' }}</span>
                                                        <span class="badge bg-light text-dark" data-map-size-label>{{ number_format($mapWidth ?: 1200) }} x {{ number_format($mapHeight ?: 800) }} px</span>
                                                        <span class="badge bg-dark-lt text-dark" data-map-zoom-indicator>Zoom 100%</span>
                                                    </div>
                                                    <div class="warehouse-map-focus-bar__actions">
                                                        @if($canManageMaps)
                                                            <button type="button" class="btn btn-primary warehouse-editor-action" data-map-mode-button="edit" data-view-only>
                                                                <i class="ti ti-pencil me-1"></i>Cập nhật lại map
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-mode-button="view" data-edit-only>
                                                                <i class="ti ti-eye me-1"></i>Xem full map
                                                            </button>
                                                            <button type="button" class="btn btn-primary warehouse-editor-action" data-map-save data-edit-only>
                                                                <i class="ti ti-device-floppy me-1"></i>Lưu layout
                                                            </button>
                                                        @endif
                                                        <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-map-toggle-panel="warehouse-map-info-panel">
                                                            <i class="ti ti-info-circle me-1"></i>Thông tin kho
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-open-setup-wizard>
                                                            <i class="ti ti-route me-1"></i>Lộ trình setup
                                                        </button>
                                                        @if(auth()->user()?->hasPermission('warehouse.edit'))
                                                            <button type="button" class="btn btn-outline-secondary warehouse-editor-action" data-open-warehouse-settings>
                                                                <i class="ti ti-settings me-1"></i>Cài đặt kho
                                                            </button>
                                                        @endif
                                                        @if(auth()->user()?->hasPermission('warehouse.locations.manage'))
                                                            <button type="button" class="btn btn-outline-primary warehouse-editor-action" data-map-toggle-panel="warehouse-map-templates-panel">
                                                                <i class="ti ti-template me-1"></i>Mẫu kho có sẵn
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

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
                                                                    @include('plugins/inventory::warehouse.partials.warehouse-render-item', [
                                                                        'mode' => 'canvas',
                                                                        'item' => $item,
                                                                        'warehouse' => $warehouse,
                                                                    ])
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
                                                            <form class="warehouse-map-create-form" method="POST" action="{{ route('inventory.warehouse.maps.store', $warehouse) }}" data-map-create-form>
                                                                @csrf
                                                                <input type="hidden" name="map_type" value="floor_plan">
                                                                <input type="hidden" name="scale_ratio" value="1">
                                                                <input type="hidden" name="is_active" value="1">
                                                                <div class="warehouse-field-grid">
                                                                    <label>
                                                                        Tên sơ đồ
                                                                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Sơ đồ kho chính" required>
                                                                    </label>
                                                                    <label>
                                                                        Kích thước nhanh
                                                                        <select class="form-select" data-map-size-preset>
                                                                            <option value="1200x800">Kho nhỏ - 1200 x 800</option>
                                                                            <option value="1600x1000">Kho vừa - 1600 x 1000</option>
                                                                            <option value="2200x1400">Kho lớn - 2200 x 1400</option>
                                                                            <option value="custom">Tự nhập</option>
                                                                        </select>
                                                                    </label>
                                                                    <label>
                                                                        Chiều rộng sơ đồ
                                                                        <input type="number" name="width" class="form-control" min="480" max="10000" value="1200" required data-map-width-input>
                                                                    </label>
                                                                    <label>
                                                                        Chiều cao sơ đồ
                                                                        <input type="number" name="height" class="form-control" min="320" max="10000" value="800" required data-map-height-input>
                                                                    </label>
                                                                </div>

                                                                <div>
                                                                    <div class="warehouse-kicker mb-2">Kho này lưu hàng theo cách nào?</div>
                                                                    <div class="warehouse-storage-mode-grid">
                                                                        @foreach($storageModeMeta as $modeKey => $modeMeta)
                                                                            <label class="warehouse-storage-mode-card" data-storage-mode-card="{{ $modeKey }}">
                                                                                <input type="radio" name="storage_mode" value="{{ $modeKey }}" @checked($selectedStorageMode === $modeKey) required>
                                                                                <span class="warehouse-storage-mode-card__icon"><i class="{{ $modeMeta['icon'] }}"></i></span>
                                                                                <span>
                                                                                    <strong>{{ $modeMeta['label'] }}</strong>
                                                                                    <small>{{ $modeMeta['description'] }}</small>
                                                                                </span>
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                </div>

                                                                <button type="submit" class="btn btn-primary">Tạo sơ đồ trống</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($canManageMaps && ! $selectedMap)
                                                <div class="warehouse-map-blueprints mt-3">
                                                    <div class="warehouse-card__title">Mẫu sơ đồ có sẵn</div>
                                                    <div class="text-muted small mb-3">Mẫu sẽ đổi theo kiểu lưu hàng bạn chọn ở form tạo sơ đồ.</div>
                                                    <div class="row g-3">
                                                        @foreach($mapBlueprints as $blueprintCode => $blueprint)
                                                            <div class="col-md-4">
                                                                <div class="warehouse-map-toolbar-card" data-blueprint-card data-blueprint-mode="{{ $blueprint['storage_mode'] ?? 'direct' }}">
                                                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                                                        <strong>{{ $blueprint['name'] }}</strong>
                                                                        <span class="badge bg-light text-dark">{{ $storageModeMeta[$blueprint['storage_mode'] ?? 'direct']['short_label'] ?? 'Không pallet' }}</span>
                                                                    </div>
                                                                    <div class="warehouse-map-template-preview" aria-hidden="true"></div>
                                                                    <div class="text-muted small">
                                                                        {{ $blueprint['description'] ?? 'Mẫu sơ đồ kho có sẵn.' }}
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
                                            <p class="warehouse-card__hint">Chọn một item trên sơ đồ để chỉnh nhãn, kích thước và xem chi tiết vận hành.</p>
                                        </div>
                                        <div class="warehouse-map-inspector__body">
                                            <div class="warehouse-map-inspector__empty" data-map-empty-inspector>
                                                <strong>Bắt đầu chỉnh sơ đồ</strong>
                                                <ol class="mb-0 mt-2 ps-3">
                                                    <li>Chọn một công cụ ở cột bên trái.</li>
                                                    <li>Bấm hoặc kéo vào canvas để thêm vùng.</li>
                                                    <li>Bấm vào vùng để đổi tên, màu, kích thước hoặc xem chi tiết.</li>
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
                                                    </div>
                                                    <div class="warehouse-field-grid warehouse-field-grid--single warehouse-hidden">
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
                                                    <button type="button" class="btn btn-outline-primary warehouse-editor-action w-100" data-map-show-detail>
                                                        <i class="ti ti-list-details me-1"></i>Xem chi tiết
                                                    </button>
                                                    <label class="form-check mb-0">
                                                        <input type="checkbox" class="form-check-input" data-inspector-field="is_clickable">
                                                        <span class="form-check-label">Cho phép click để nhảy sang tree / chi tiết vị trí</span>
                                                    </label>
                                                </div>

                                                <div class="warehouse-map-inspector__section" data-inspector-layout-section>
                                                    <h4>Kích thước layout</h4>
                                                    <small>Map chỉ hiển thị mặt bằng rộng x dài của khối. Ô ngang nở sang phải, chiều dài chạy dọc xuống dưới. Số tầng/chiều cao là sức chứa logic, không làm khối cao thêm trên map. Với kệ/rack, có thể bấm xác nhận để hệ thống tự sinh level còn thiếu.</small>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Ô ngang
                                                            <input type="number" min="1" step="1" class="form-control" data-inspector-meta="width_count">
                                                        </label>
                                                        <label>
                                                            Số tầng
                                                            <input type="number" min="1" step="1" class="form-control" data-inspector-meta="height_count">
                                                        </label>
                                                        <label>
                                                            Chiều dài
                                                            <input type="number" min="1" step="1" class="form-control" data-inspector-meta="length_count">
                                                        </label>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary warehouse-editor-action w-100 mt-2" data-map-sync-levels>
                                                        <i class="ti ti-layers-subtract me-1"></i>Xác nhận tạo tầng
                                                    </button>
                                                    <small class="d-block mt-2" data-map-sync-levels-help>Chọn kệ/rack trên map, gắn với zone hoặc rack, nhập số tầng rồi bấm nút này để tạo level tương ứng.</small>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="aisle">
                                                    <h4>Lối đi</h4>
                                                    <small>Lối đi luôn rộng mặc định 1 ô. Kéo chấm ở góc dưới của khối để tăng hoặc giảm chiều dài. Dùng nút Xoay 90° để đổi hướng ngang/dọc.</small>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="simple_shelf">
                                                    <h4>Kệ</h4>
                                                    <small>Direct mode: 1 x 2 x 4 nghĩa là rộng 1 ô trên mặt bằng, có 2 tầng và dài 4 ô chứa. Cứ 4 ô grid nhỏ trên map được tính là 1 ô chứa.</small>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Tiền tố
                                                            <input type="text" class="form-control" data-inspector-meta="prefix">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="pallet_rack">
                                                    <h4>Rack pallet</h4>
                                                    <small>Pallet mode: rack nở theo ô ngang, tầng và chiều dài dãy rack. Hệ thống chỉ sinh slot chứa pallet, không tự sinh pallet.</small>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Tiền tố
                                                            <input type="text" class="form-control" data-inspector-meta="prefix">
                                                        </label>
                                                        <label>
                                                            Số tầng
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="height_count">
                                                        </label>
                                                        <label>
                                                            Ô ngang / tầng
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="width_count">
                                                        </label>
                                                        <label>
                                                            Dài dãy rack
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="length_count">
                                                        </label>
                                                        <label>
                                                            Pallet mỗi slot
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="pallets_per_position">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="warehouse-map-inspector__section warehouse-hidden" data-inspector-section="pallet_slot">
                                                    <h4>Slot pallet</h4>
                                                    <small>Slot là vị trí chứa pallet. Pallet thật chỉ tạo khi nhập hàng, quét pallet hoặc tạo pallet riêng.</small>
                                                    <div class="warehouse-field-grid">
                                                        <label>
                                                            Pallet mỗi slot
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="pallets_per_position">
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
                                                        <label>
                                                            Pallet mỗi vị trí
                                                            <input type="number" min="1" class="form-control" data-inspector-meta="pallets_per_position">
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
                                                            <small>Dựa trên ô ngang, số tầng và chiều dài của vùng</small>
                                                        </div>
                                                        <strong data-inspector-capacity>-</strong>
                                                    </div>
                                                    <div class="warehouse-map-inspector__mini-spec mt-3" data-inspector-mini-spec>
                                                        <div class="warehouse-map-inspector__mini-spec-title">Sơ đồ mini theo module</div>
                                                        <div class="warehouse-map-inspector__mini-spec-body" data-inspector-mini-spec-body>-</div>
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
                        <section class="warehouse-products-tab">
                            <div class="warehouse-products-tab__header">
                                <div>
                                    <div class="warehouse-kicker">Sản phẩm trong kho</div>
                                    <h2 class="warehouse-products-tab__title">Danh sách sản phẩm đã gắn với {{ $warehouse->name }}</h2>
                                    <div class="warehouse-products-tab__hint">
                                        Dữ liệu lấy trực tiếp từ cấu hình sản phẩm kho và số dư tồn kho hiện tại, nên kho 9002 sẽ hiển thị các dòng đã có trong bảng inv_warehouse_products.
                                    </div>
                                </div>
                                <div class="warehouse-products-tab__actions">
                                    <a href="{{ route('inventory.warehouse-products.index', ['warehouse_id' => $warehouse->getKey(), 'status' => 'in_warehouse']) }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-layout-grid me-1"></i>Mở catalog
                                    </a>
                                    @if(auth()->user()?->hasPermission('warehouse.products.manage'))
                                        <a href="{{ route('inventory.warehouse-products.index', ['warehouse_id' => $warehouse->getKey(), 'status' => 'all']) }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i>Gắn thêm sản phẩm
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="warehouse-products-tab__stats">
                                <div class="warehouse-products-tab__stat">
                                    <span>Tổng dòng cấu hình</span>
                                    <strong>{{ number_format($warehouseProductRows->count()) }}</strong>
                                </div>
                                <div class="warehouse-products-tab__stat">
                                    <span>Đang hoạt động</span>
                                    <strong>{{ number_format((int) ($warehouse->active_warehouse_products_count ?? $warehouseProductRows->where('is_active', true)->count())) }}</strong>
                                </div>
                                <div class="warehouse-products-tab__stat">
                                    <span>Tồn khả dụng</span>
                                    <strong>{{ $formatWarehouseQty($warehouseProductStockTotals['available']) }}</strong>
                                </div>
                                <div class="warehouse-products-tab__stat">
                                    <span>Dưới điểm đặt hàng</span>
                                    <strong>{{ number_format($warehouseProductLowStockCount) }}</strong>
                                </div>
                            </div>

                            @if($warehouseProductRows->isEmpty())
                                <div class="warehouse-empty-state">
                                    Kho này chưa có sản phẩm nào trong bảng cấu hình. Vào catalog để gắn sản phẩm cho kho trước.
                                </div>
                            @else
                                <div class="warehouse-products-table-card">
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Sản phẩm</th>
                                                    <th>Tồn kho</th>
                                                    <th>Vị trí / NCC</th>
                                                    <th>Chính sách</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($warehouseProductRows as $warehouseProduct)
                                                    @php
                                                        $product = $warehouseProduct->product;
                                                        $productName = data_get($product, 'name') ?: 'Sản phẩm #' . $warehouseProduct->product_id;
                                                        $productSku = data_get($product, 'sku') ?: 'SKU chưa có';
                                                        $variationName = data_get($warehouseProduct->productVariation, 'name')
                                                            ?: data_get($warehouseProduct->productVariation, 'title');
                                                        $stockKey = (int) $warehouseProduct->product_id . ':' . (int) ($warehouseProduct->product_variation_id ?? 0);
                                                        $stock = $warehouseProductStockMap->get($stockKey);
                                                        $availableQty = (float) ($stock->available_qty ?? 0);
                                                        $quantity = (float) ($stock->quantity ?? 0);
                                                        $reservedQty = (float) ($stock->reserved_qty ?? 0);
                                                        $qcHoldQty = (float) ($stock->qc_hold_qty ?? 0);
                                                        $reorderPointQty = (float) ($warehouseProduct->reorder_point_qty ?? 0);
                                                        $isLowStock = $reorderPointQty > 0 && $availableQty <= $reorderPointQty;
                                                        $defaultLocation = $warehouseProductLocationById->get((int) ($warehouseProduct->default_location_id ?? 0));
                                                        $defaultLocationLabel = $defaultLocation
                                                            ? (method_exists($defaultLocation, 'displayLabel') ? $defaultLocation->displayLabel() : trim(($defaultLocation->code ? $defaultLocation->code . ' - ' : '') . $defaultLocation->name))
                                                            : null;
                                                        $unitName = $warehouseProductUnitLabels->get((int) ($warehouseProduct->default_unit_id ?? 0));
                                                        $supplierLabel = data_get($warehouseProduct->supplier, 'name') ?: data_get($warehouseProduct->supplierProduct, 'name');
                                                        $ruleChips = collect([
                                                            ($warehouseProduct->is_batch_required ?? false) ? 'Batch' : null,
                                                            ($warehouseProduct->is_serial_required ?? false) ? 'Serial' : null,
                                                            ($warehouseProduct->is_pallet_required ?? false) ? 'Pallet' : null,
                                                            ($warehouseProduct->allow_negative_stock ?? false) ? 'Cho âm kho' : null,
                                                        ])->filter();
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="warehouse-product-main">
                                                                <div class="warehouse-product-name">{{ $productName }}</div>
                                                                <div class="warehouse-product-meta">
                                                                    <span class="warehouse-product-chip is-primary">{{ $productSku }}</span>
                                                                    @if($variationName)
                                                                        <span class="warehouse-product-chip">{{ $variationName }}</span>
                                                                    @endif
                                                                    <span class="warehouse-product-chip">ID {{ $warehouseProduct->product_id }}</span>
                                                                    <span class="warehouse-product-chip {{ $warehouseProduct->is_active ? 'is-success' : 'is-warning' }}">
                                                                        {{ $warehouseProduct->is_active ? 'Đang dùng' : 'Tạm tắt' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="warehouse-product-stock">
                                                                <div class="warehouse-product-stock__main">
                                                                    {{ $formatWarehouseQty($availableQty) }}
                                                                    <span>{{ $unitName ?: 'đơn vị' }}</span>
                                                                </div>
                                                                <div class="warehouse-product-subgrid">
                                                                    <span class="warehouse-product-chip">Tổng {{ $formatWarehouseQty($quantity) }}</span>
                                                                    <span class="warehouse-product-chip">Giữ {{ $formatWarehouseQty($reservedQty) }}</span>
                                                                    @if($qcHoldQty > 0)
                                                                        <span class="warehouse-product-chip is-warning">QC {{ $formatWarehouseQty($qcHoldQty) }}</span>
                                                                    @endif
                                                                    @if($isLowStock)
                                                                        <span class="warehouse-product-chip is-warning">Cần nhập</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="warehouse-product-detail-list">
                                                                <div><span>Vị trí mặc định</span><strong>{{ $defaultLocationLabel ?: 'Chưa gắn' }}</strong></div>
                                                                <div><span>Nhà cung cấp</span><strong>{{ $supplierLabel ?: 'Chưa chọn' }}</strong></div>
                                                                <div><span>Đơn vị</span><strong>{{ $unitName ?: 'Chưa chọn' }}</strong></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="warehouse-product-detail-list">
                                                                <div><span>Min</span><strong>{{ $formatWarehouseQty($warehouseProduct->min_stock_qty ?? 0) }}</strong></div>
                                                                <div><span>Max</span><strong>{{ $formatWarehouseQty($warehouseProduct->max_stock_qty ?? 0) }}</strong></div>
                                                                <div><span>Reorder</span><strong>{{ $formatWarehouseQty($warehouseProduct->reorder_point_qty ?? 0) }}</strong></div>
                                                                @if($ruleChips->isNotEmpty())
                                                                    <div class="warehouse-product-subgrid">
                                                                        @foreach($ruleChips as $ruleChip)
                                                                            <span class="warehouse-product-chip">{{ $ruleChip }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </section>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'policies' ? 'is-active' : '' }}" data-panel-key="policies">
                        <div class="warehouse-empty-state">Chính sách sản phẩm đã có backend. UI chi tiết cho từng policy sẽ được nối tiếp khi cần.</div>
                    </div>

                    <div class="warehouse-panel {{ $activeTab === 'pallets' ? 'is-active' : '' }}" data-panel-key="pallets">
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <section class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <h2 class="warehouse-card__title">Pallet</h2>
                                                <p class="warehouse-card__hint">Tạo pallet và đặt pallet vào location.</p>
                                            </div>
                                            <span class="badge {{ ($warehouseSetting?->use_pallet ?? false) ? 'bg-success-lt text-success' : 'bg-warning-lt text-warning' }}">
                                                {{ ($warehouseSetting?->use_pallet ?? false) ? 'Đã bật pallet' : 'Chưa bật pallet' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="warehouse-card__body">
                                        <div class="warehouse-system-note mb-3">
                                            <strong>Quy tắc pallet mode:</strong> tạo slot/rack trên map trước, pallet chỉ là vật chứa chạy vào slot đó. Không tự sinh pallet khi dựng sơ đồ.
                                        </div>
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
                                                            @foreach($palletStatusLabels as $statusValue => $statusLabel)
                                                                <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                                                            @endforeach
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
                                            <div class="warehouse-empty-state mb-3">
                                                Kho này chưa bật pallet. Hãy bật trong cài đặt kho nếu muốn dùng pallet.
                                            </div>
                                            <button type="button" class="btn btn-outline-primary w-100" data-open-warehouse-settings>Cài đặt kho</button>
                                        @endif
                                    </div>
                                </section>
                            </div>
                            <div class="col-lg-7">
                                <section class="warehouse-card">
                                    <div class="warehouse-card__header">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <h2 class="warehouse-card__title">Danh sách pallet</h2>
                                                <p class="warehouse-card__hint">Danh sách pallet đang vận hành trong kho.</p>
                                            </div>
                                            <div class="warehouse-card__hint text-end">
                                                <div><strong>{{ number_format($pallets->count()) }}</strong> pallet</div>
                                                <div><strong>{{ number_format($pallets->where('status', 'in_use')->count()) }}</strong> đang dùng</div>
                                            </div>
                                        </div>
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
                                                            <td>
                                                                <span class="badge {{ $palletStatusBadges[$pallet->status] ?? 'bg-light text-dark' }}">
                                                                    {{ $palletStatusLabels[$pallet->status] ?? $pallet->status }}
                                                                </span>
                                                            </td>
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

    <div class="modal fade" id="warehouseMapItemDetailModal" tabindex="-1" aria-hidden="true" data-map-detail-modal>
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="warehouse-kicker">Chi tiết module sơ đồ</div>
                        <h5 class="modal-title mb-1" data-map-detail-title>Vùng đang chọn</h5>
                        <div class="text-muted small" data-map-detail-subtitle>Thông tin vận hành và sức chứa logic.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="warehouse-map-detail-grid mb-3" data-map-detail-stats></div>
                    <div class="warehouse-card border-0 shadow-none">
                        <div class="warehouse-card__body p-0">
                            <div class="warehouse-map-detail-list" data-map-detail-rows></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    @if($selectedMap)
        <div class="modal fade warehouse-map-popup" id="warehouse-map-info-panel" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <div class="warehouse-kicker">Thông tin kho</div>
                            <h5 class="modal-title mb-1">{{ $warehouse->name }}</h5>
                            <div class="text-muted small">{{ $warehouse->code }}{{ $warehouse->address ? ' - ' . $warehouse->address : '' }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <div class="warehouse-map-popup__metrics">
                            <div class="warehouse-map-stat"><span>Location</span><strong>{{ number_format($warehouse->locations_count) }}</strong></div>
                            <div class="warehouse-map-stat"><span>Sơ đồ</span><strong>{{ number_format($warehouse->maps->count()) }}</strong></div>
                            <div class="warehouse-map-stat"><span>Pallet</span><strong>{{ number_format($pallets->count()) }}</strong></div>
                            <div class="warehouse-map-stat"><span>Mapped</span><strong>{{ number_format($mappedLocationCount) }}</strong></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade warehouse-map-popup warehouse-map-popup--templates" id="warehouse-map-templates-panel" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <div class="warehouse-kicker">Mẫu kho có sẵn</div>
                            <h5 class="modal-title mb-1">Chọn mẫu để sinh nhanh cây vị trí</h5>
                            <div class="text-muted small">Áp dụng vào cây hiện tại hoặc ghi đè nếu muốn dựng lại từ mẫu chuẩn.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <div class="warehouse-map-popup__templates">
                            @foreach($templates as $templateCode => $template)
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
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade warehouse-map-popup" id="warehouseSettingsModal" tabindex="-1" aria-hidden="true" data-warehouse-settings-modal>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="warehouse-kicker">Cài đặt kho</div>
                        <h5 class="modal-title mb-1">Chỉnh nhanh cấu hình kho</h5>
                        <div class="text-muted small">Chỉnh ngay trong popup để không phải chuyển sang màn riêng.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <form method="POST" action="{{ route('inventory.warehouse.settings.store', $warehouse) }}" class="warehouse-location-form" data-warehouse-settings-form>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Kiểu kho</label>
                                <select name="warehouse_mode" class="form-select">
                                    <option value="simple" @selected(($warehouseSetting?->warehouse_mode ?? 'simple') === 'simple')>Kho đơn giản</option>
                                    <option value="advanced" @selected(($warehouseSetting?->warehouse_mode ?? 'simple') === 'advanced')>Kho nâng cao</option>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_pallet" value="1" @checked($warehouseSetting?->use_pallet)><span class="form-check-label">Dùng pallet</span></label></div>
                            <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="require_pallet" value="1" @checked($warehouseSetting?->require_pallet)><span class="form-check-label">Bắt buộc pallet</span></label></div>
                            <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_qc" value="1" @checked($warehouseSetting?->use_qc)><span class="form-check-label">Dùng QC</span></label></div>
                            <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_map" value="1" @checked($warehouseSetting?->use_map)><span class="form-check-label">Dùng sơ đồ</span></label></div>
                            <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_batch" value="1" @checked($warehouseSetting?->use_batch)><span class="form-check-label">Dùng batch</span></label></div>
                            <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="use_serial" value="1" @checked($warehouseSetting?->use_serial)><span class="form-check-label">Dùng serial</span></label></div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button class="btn btn-primary" type="submit">Lưu cài đặt kho</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade warehouse-map-popup" id="warehouseSetupWizardModal" tabindex="-1" aria-hidden="true" data-setup-wizard-modal>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content warehouse-wizard-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="warehouse-kicker">Wizard setup kho</div>
                        <h5 class="modal-title mb-1">Thiết lập kho theo lộ trình chuẩn</h5>
                        <div class="text-muted small">Dẫn user từ cài đặt → cây vị trí → sơ đồ kho → pallet.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="warehouse-wizard-hero mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary-lt text-primary">Setup guide</span>
                                <span class="badge bg-light text-muted">{{ $isWarehouseSetupReady ? 'Đã sẵn sàng' : 'Đang cần hoàn tất' }}</span>
                            </div>
                            <h3 class="warehouse-card__title mt-3">Làm đúng thứ tự để kho mới không bị rối</h3>
                            <p class="warehouse-card__hint mb-0">Mình sẽ tự gợi ý bước tiếp theo còn thiếu, và chỉ mở map editor khi đủ điều kiện.</p>
                        </div>
                        <div class="warehouse-wizard-progress">
                            <div class="warehouse-wizard-progress__bar"><span data-setup-progress></span></div>
                            <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                                <span>Tiến độ setup</span>
                                <strong data-setup-wizard-cta>Đang kiểm tra</strong>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        @foreach($setupCheckpoints as $checkpoint)
                            <div class="col-md-6" data-setup-step>
                                <div class="warehouse-wizard-step {{ $checkpoint['done'] ? 'is-done' : '' }}">
                                    <div class="warehouse-wizard-step__icon">
                                        <i class="{{ $checkpoint['key'] === 'settings' ? 'ti ti-settings' : ($checkpoint['key'] === 'locations' ? 'ti ti-layout-grid' : ($checkpoint['key'] === 'maps' ? 'ti ti-map-2' : 'ti ti-stack-2')) }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <strong>{{ $checkpoint['title'] }}</strong>
                                                <div class="text-muted small mt-1">{{ $checkpoint['description'] }}</div>
                                            </div>
                                            <span class="badge {{ $checkpoint['done'] ? 'bg-success-lt text-success' : 'bg-warning-lt text-warning' }}">
                                                {{ $checkpoint['done'] ? 'Xong' : 'Chưa xong' }}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap mt-3">
                                            @if($checkpoint['key'] === 'settings')
                                                <button type="button" class="btn btn-sm {{ $checkpoint['done'] ? 'btn-outline-secondary' : 'btn-primary' }}" data-open-warehouse-settings>
                                                    Đi tới bước này
                                                </button>
                                            @else
                                                <a href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => $checkpoint['key'] === 'pallets' ? 'pallets' : $checkpoint['key']]) }}" class="btn btn-sm {{ $checkpoint['done'] ? 'btn-outline-secondary' : 'btn-primary' }}">
                                                    Đi tới bước này
                                                </a>
                                            @endif
                                            @if($checkpoint['key'] === 'pallets' && ! ($warehouseSetting?->use_pallet ?? false))
                                                <button type="button" class="btn btn-sm btn-outline-success" disabled>Tùy chọn</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="warehouse-system-note mt-3">
                        <strong>Quy tắc tự động:</strong> nếu chưa hoàn tất cài đặt hoặc cây vị trí, hệ thống sẽ tự ưu tiên tab còn thiếu và khóa map editor.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <div class="text-muted small">User mới chỉ cần bấm theo gợi ý, không cần biết trước nghiệp vụ kho.</div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Để sau</button>
                        <button type="button" class="btn btn-primary" data-open-warehouse-settings>Bắt đầu bước tiếp theo</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('plugins/inventory::warehouse.partials.show-map-editor-script', ['warehouseShow' => $warehouseShow])
    @endif
@endsection
