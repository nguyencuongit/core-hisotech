@php
    $meta = $locationMeta[$location->type] ?? [
        'label' => \Illuminate\Support\Str::headline((string) $location->type),
        'icon' => 'ti ti-map-pin',
        'badge' => 'bg-secondary-lt text-secondary',
        'accent' => '#64748b',
    ];
    $isSelected = (int) ($selectedLocation?->getKey() ?? 0) === (int) $location->getKey();
    $mapItem = $mapItemByLocation->get($location->getKey());
    $nodeStyle = '--tree-level: ' . $level . '; --tree-accent: ' . ($meta['accent'] ?? '#64748b') . ';';
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
                <span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
                <span class="badge {{ $location->status ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                    {{ $location->status ? 'Đang hoạt động' : 'Tạm tắt' }}
                </span>
                @if($mapItem)
                    <span class="badge bg-info-lt text-info">Đã gắn lên sơ đồ</span>
                @endif
            </div>
            <div class="warehouse-tree-meta">
                <span>Path: {{ $location->path }}</span>
                <span>Cấp: {{ $location->level }}</span>
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
            href="{{ route('inventory.warehouse.show', ['warehouse' => $warehouse->getKey(), 'location_id' => $location->getKey(), 'map_id' => request('map_id')]) }}"
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
