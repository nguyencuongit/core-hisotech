@php
    $meta = $locationMeta[$location->type] ?? [
        'label' => \Illuminate\Support\Str::headline((string) $location->type),
        'icon' => 'ti ti-map-pin',
        'badge' => 'bg-secondary-lt text-secondary',
        'accent' => '#64748b',
    ];
    $isSelected = (int) ($selectedLocation?->getKey() ?? 0) === (int) $location->getKey();
    $mapItem = $mapItemByLocation->get($location->getKey());
    $treeDepth = max(0, (int) $level);
    $nodeStyle = '--tree-level: ' . $treeDepth . '; --tree-accent: ' . ($meta['accent'] ?? '#64748b') . ';';
    $moduleType = $mapItem?->item_type ?: $location->type;
    $mapMeta = $mapItem?->meta_json ?? [];
    $widthCount = max(1, (int) data_get($mapMeta, 'width_count', data_get($mapMeta, 'positions_per_level', data_get($mapMeta, 'bin_count_per_level', data_get($mapMeta, 'column_count', 4)))));
    $heightCount = max(1, (int) data_get($mapMeta, 'height_count', data_get($mapMeta, 'level_count', 4)));
    $lengthCount = max(1, (int) data_get($mapMeta, 'length_count', data_get($mapMeta, 'rack_count', data_get($mapMeta, 'row_count', 1))));
    $isStandardRack = in_array($moduleType, ['pallet_rack', 'simple_shelf', 'rack'], true)
        && $widthCount === 4
        && $heightCount === 4
        && $lengthCount === 10;
    $isRackNode = in_array($location->type, ['rack', 'level', 'bin'], true) || in_array($moduleType, ['pallet_rack', 'simple_shelf', 'rack'], true);
    $storageModeLabel = in_array($moduleType, ['pallet_rack', 'pallet_slot', 'floor_pallet_area'], true)
        ? 'Pallet'
        : ($isRackNode ? 'Direct' : null);
    $treeLabel = $isRackNode ? 'Kệ' : $meta['label'];
    $capacity = $widthCount * $heightCount * $lengthCount * max(1, (int) data_get($mapMeta, 'pallets_per_position', 1));
    $capacityLabel = $mapItem ? number_format($capacity) . ' vị trí' : null;
    $locationPayload = e(json_encode([
        'id' => $location->getKey(),
        'parent_id' => $location->parent_id,
        'code' => $location->code,
        'name' => $location->name,
        'type' => $location->type,
        'path' => $location->path,
        'level' => $location->level,
        'status' => (bool) $location->status,
        'description' => $location->description,
        'map_label' => $mapItem?->label,
        'update_url' => route('inventory.warehouse.locations.update', [$warehouse, $location]),
    ], JSON_UNESCAPED_UNICODE));
@endphp

<div
    class="warehouse-tree-node {{ $isSelected ? 'is-active' : '' }}"
    data-location-node
    data-location-id="{{ $location->getKey() }}"
    data-location-select
    data-location="{{ $locationPayload }}"
    style="{{ $nodeStyle }}"
>
    <div class="warehouse-tree-main">
        <span class="warehouse-tree-icon"><i class="{{ $meta['icon'] }}"></i></span>
        <div>
            <div class="warehouse-tree-title-row">
                <strong>{{ $location->displayLabel() }}</strong>
                <span class="badge {{ $meta['badge'] }}">{{ $treeLabel }}</span>
                <span class="badge {{ $location->status ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                    {{ $location->status ? 'Đang hoạt động' : 'Tạm tắt' }}
                </span>
                @if($storageModeLabel)
                    <span class="badge {{ str_contains($storageModeLabel, 'pallet') ? 'bg-success-lt text-success' : 'bg-primary-lt text-primary' }}">{{ $storageModeLabel }}</span>
                @endif
                @if($mapItem)
                    <span class="badge {{ $isStandardRack ? 'bg-success-lt text-success' : 'bg-info-lt text-info' }}">{{ $widthCount }}×{{ $heightCount }}×{{ $lengthCount }} · {{ $capacity }}</span>
                @endif
            </div>
            <div class="warehouse-tree-meta">
                <span>Path: {{ $location->path }}</span>
                <span>Cấp: {{ $location->level }}</span>
                @if($mapItem)
                    <span>{{ $moduleType }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="warehouse-tree-actions">
        @if(auth()->user()?->hasPermission('warehouse.locations.manage'))
            <button type="button" class="btn btn-sm btn-light" data-location-edit data-location-node-id="{{ $location->getKey() }}">Sửa</button>
            <button type="button" class="btn btn-sm btn-light" data-location-add-child data-location-node-id="{{ $location->getKey() }}">Thêm con</button>
        @endif
        <a
            class="btn btn-sm btn-outline-secondary"
            href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'tab' => 'maps', 'location_id' => $location->getKey(), 'map_id' => request('map_id')]) }}"
        >
            Xem
        </a>
    </div>
</div>

@if($location->children->isNotEmpty())
    <div class="warehouse-tree-children">
        @foreach($location->children as $child)
            @include('plugins/inventory::warehouse.partials.location-tree-node', [
                'location' => $child,
                'level' => $level + 1,
                'warehouse' => $warehouse,
                'locationMeta' => $locationMeta,
                'selectedLocation' => $selectedLocation,
                'selectedLocationMapItem' => $selectedLocationMapItem,
                'mapItemByLocation' => $mapItemByLocation,
            ])
        @endforeach
    </div>
@endif
