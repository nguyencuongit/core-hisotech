@php
    $ui = \Botble\Inventory\Domains\Warehouse\Support\WarehouseUiRegistry::class;

    $renderTreeNode = function (\Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation $location, array $context = []) use ($ui) {
        $meta = $ui::locationMeta()[$location->type] ?? [
            'label' => \Illuminate\Support\Str::headline((string) $location->type),
            'icon' => 'ti ti-map-pin',
            'badge' => 'bg-secondary-lt text-secondary',
            'accent' => '#64748b',
        ];

        $mapItem = $context['mapItemByLocation']->get($location->getKey()) ?? null;
        $moduleType = $mapItem?->item_type ?: $location->type;
        $isRackNode = in_array($location->type, ['rack', 'level', 'bin'], true) || in_array($moduleType, ['pallet_rack', 'simple_shelf', 'rack'], true);
        $storageModeLabel = in_array($moduleType, ['pallet_rack', 'pallet_slot', 'floor_pallet_area'], true) ? 'Pallet' : ($isRackNode ? 'Direct' : null);
        $capacity = $mapItem ? (int) data_get($mapItem->meta_json ?? [], 'capacity', 0) : 0;

        return [
            'meta' => $meta,
            'isRackNode' => $isRackNode,
            'storageModeLabel' => $storageModeLabel,
            'moduleType' => $moduleType,
            'capacity' => $capacity,
            'label' => $isRackNode ? 'Kệ' : $meta['label'],
        ];
    };

    $renderCanvasItem = function ($item) use ($ui) {
        $meta = $ui::mapTypeMeta()[$item->item_type] ?? [
            'label' => \Illuminate\Support\Str::headline((string) $item->item_type),
            'icon' => 'ti ti-map-pin',
        ];

        $moduleType = $item->meta_json['module_type'] ?? $item->item_type;
        $isRackLayout = in_array($moduleType, ['pallet_rack', 'simple_shelf', 'floor_pallet_area', 'rack'], true);

        return [
            'meta' => $meta,
            'moduleType' => $moduleType,
            'isRackLayout' => $isRackLayout,
            'label' => $item->label ?: $meta['label'],
        ];
    };
@endphp