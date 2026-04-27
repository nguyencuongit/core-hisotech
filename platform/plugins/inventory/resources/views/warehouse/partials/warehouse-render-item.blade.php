@php
    $ui = \Botble\Inventory\Domains\Warehouse\Support\WarehouseUiRegistry::class;
    $mode = $mode ?? 'tree';
    $warehouseShow = isset($warehouseShow) && is_array($warehouseShow) ? $warehouseShow : [];
    $locationMeta = $warehouseShow['locationMeta'] ?? $ui::locationMeta();
    $mapTypeMeta = $warehouseShow['mapTypeMeta'] ?? $ui::mapTypeMeta();
    $location = $location ?? null;
    $item = $item ?? null;
    $context = $context ?? [];
@endphp

@if($mode === 'tree' && $location)
    @php
        $meta = $locationMeta[$location->type] ?? ['label' => \Illuminate\Support\Str::headline((string) $location->type), 'icon' => 'ti ti-map-pin', 'badge' => 'bg-secondary-lt text-secondary', 'accent' => '#64748b'];
        $selectedLocation = $context['selectedLocation'] ?? null;
        $mapItemByLocation = $context['mapItemByLocation'] ?? collect();
        $mapItem = $mapItemByLocation->get($location->getKey());
        $moduleType = $mapItem?->item_type ?: $location->type;
        $isRackNode = in_array($location->type, ['rack', 'level', 'bin'], true) || in_array($moduleType, ['pallet_rack', 'simple_shelf', 'rack'], true);
        $storageModeLabel = in_array($moduleType, ['pallet_rack', 'pallet_slot', 'floor_pallet_area'], true) ? 'Pallet' : ($isRackNode ? 'Direct' : null);
        $capacity = $mapItem ? max(1, (int) data_get($mapItem->meta_json ?? [], 'capacity', 0)) : null;
        $isSelected = (int) ($selectedLocation?->getKey() ?? 0) === (int) $location->getKey();
        $payload = [
            'id' => $location->getKey(),
            'parent_id' => $location->parent_id, 'code' => $location->code, 'name' => $location->name,
            'type' => $location->type, 'path' => $location->path, 'level' => $location->level,
            'status' => (bool) $location->status, 'description' => $location->description,
            'map_label' => $mapItem?->label, 'update_url' => route('inventory.warehouse.locations.update', [$warehouse, $location]),
        ];
    @endphp
    <div class="warehouse-tree-node {{ $isSelected ? 'is-active' : '' }}" data-location-node data-location-id="{{ $location->getKey() }}" data-location='@json($payload, JSON_UNESCAPED_UNICODE)' style="--tree-level: {{ max(0, (int) ($level ?? 0)) }}; --tree-accent: {{ $meta['accent'] ?? '#64748b' }};">
        <div class="warehouse-tree-main">
            <span class="warehouse-tree-icon"><i class="{{ $meta['icon'] }}"></i></span>
            <div>
                <div class="warehouse-tree-title-row">
                    <strong>{{ ($isRackNode ? 'Kệ' : $meta['label']) }} · {{ $location->displayLabel() }}</strong>
                    <span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
                    <span class="badge {{ $location->status ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">{{ $location->status ? 'Đang hoạt động' : 'Tạm tắt' }}</span>
                    @if($storageModeLabel)
                        <span class="badge {{ $storageModeLabel === 'Pallet' ? 'bg-success-lt text-success' : 'bg-info-lt text-info' }}">{{ $storageModeLabel }}</span>
                    @endif
                    @if($capacity)
                        <span class="badge bg-info-lt text-info">{{ number_format($capacity) }} vị trí</span>
                    @endif
                </div>
                <div class="warehouse-tree-meta"><span>Path: {{ $location->path }}</span><span>Cấp: {{ $location->level }}</span><span>Loại: {{ $moduleType }}</span></div>
            </div>
        </div>
        <div class="warehouse-tree-actions">
            @if(auth()->user()?->hasPermission('warehouse.locations.manage'))
                <button type="button" class="btn btn-sm btn-light" data-location-edit data-location-node-id="{{ $location->getKey() }}">Sửa</button>
                <button type="button" class="btn btn-sm btn-light" data-location-add-child data-location-node-id="{{ $location->getKey() }}">Thêm con</button>
            @endif
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => 'maps', 'location_id' => $location->getKey(), 'map_id' => request('map_id')]) }}">Xem</a>
        </div>
    </div>
    @if($location->children->isNotEmpty())
        <div class="warehouse-tree-children">
            @foreach($location->children as $child)
                @include('plugins/inventory::warehouse.partials.warehouse-render-item', ['mode' => 'tree', 'location' => $child, 'level' => ($level ?? 0) + 1, 'warehouse' => $warehouse, 'context' => $context, 'warehouseShow' => $warehouseShow])
            @endforeach
        </div>
    @endif
@endif

@if($mode === 'canvas' && $item)
    @php
        $meta = $mapTypeMeta[$item->item_type] ?? ['label' => \Illuminate\Support\Str::headline((string) $item->item_type), 'icon' => 'ti ti-map-pin'];
        $linkedLocation = $item->location;
        $moduleType = $item->meta_json['module_type'] ?? $item->item_type;
        $widthCount = (int) ($item->meta_json['width_count'] ?? $item->meta_json['positions_per_level'] ?? $item->meta_json['bin_count_per_level'] ?? ($item->meta_json['column_count'] ?? 0));
        $heightCount = (int) ($item->meta_json['height_count'] ?? $item->meta_json['level_count'] ?? ($item->meta_json['row_count'] ?? 0));
        $lengthCount = (int) ($item->meta_json['length_count'] ?? 1);
        $palletsPerPosition = (int) ($item->meta_json['pallets_per_position'] ?? 1);
        $capacityHint = match ($moduleType) {
            'pallet_rack' => max(1, $heightCount ?: 1) . ' tầng • ' . max(1, $widthCount ?: 1) . ' slot/tầng',
            'simple_shelf' => max(1, $widthCount ?: 1) . ' rộng • ' . max(1, $heightCount ?: 1) . ' tầng • ' . max(1, $lengthCount ?: 1) . ' dài',
            'pallet_slot' => '1 slot • ' . max(1, $palletsPerPosition) . ' pallet/slot',
            'floor_pallet_area' => max(1, (int) ($item->meta_json['row_count'] ?? 1)) . ' hàng • ' . max(1, (int) ($item->meta_json['column_count'] ?? 1)) . ' cột',
            default => null,
        };
    @endphp
    <button type="button" class="warehouse-map-canvas__item {{ $linkedLocation ? 'is-linked' : '' }} {{ $item->shape_type === 'label' ? 'warehouse-map-canvas__item--label' : '' }}" data-map-item-id="{{ $item->getKey() }}" data-map-item-tooltip="{{ trim($item->label ?: $meta['label']) }} | {{ $linkedLocation?->displayLabel() ?: $meta['label'] }}{{ $capacityHint ? ' | ' . $capacityHint : '' }}" style="left: {{ (float) $item->x }}px; top: {{ (float) $item->y }}px; width: {{ max(36, (float) $item->width) }}px; height: {{ max(28, (float) $item->height) }}px; background: {{ $item->shape_type === 'label' ? 'transparent' : ($item->color ?: '#e2e8f0') }}; z-index: {{ (int) ($item->z_index ?: 1) }}; transform: rotate({{ (float) ($item->rotation ?: 0) }}deg);">
        <div class="warehouse-map-canvas__content">
            <div class="warehouse-map-canvas__title"><i class="{{ $meta['icon'] }}"></i><span>{{ $item->label ?: $meta['label'] }}</span></div>
            <div class="warehouse-map-canvas__meta">{{ $linkedLocation?->displayLabel() ?: $meta['label'] }}</div>
            @if($capacityHint)<div class="warehouse-map-canvas__capacity-badge">{{ $capacityHint }}</div>@endif
        </div>
    </button>
@endif
