<?php

namespace Botble\Inventory\Domains\Warehouse\Support;

class WarehouseUiRegistry
{
    public static function locationMeta(): array
    {
        return [
            'system' => ['label' => 'Hệ thống', 'icon' => 'ti ti-settings', 'badge' => 'bg-secondary-lt text-secondary', 'accent' => '#64748b'],
            'floor' => ['label' => 'Tầng', 'icon' => 'ti ti-layers-difference', 'badge' => 'bg-indigo-lt text-indigo', 'accent' => '#6366f1'],
            'zone' => ['label' => 'Khu vực', 'icon' => 'ti ti-layout-grid', 'badge' => 'bg-primary-lt text-primary', 'accent' => '#3b82f6'],
            'rack' => ['label' => 'Kệ', 'icon' => 'ti ti-package', 'badge' => 'bg-success-lt text-success', 'accent' => '#16a34a'],
            'level' => ['label' => 'Tầng kệ', 'icon' => 'ti ti-badge', 'badge' => 'bg-teal-lt text-teal', 'accent' => '#0f766e'],
            'bin' => ['label' => 'Ô chứa', 'icon' => 'ti ti-box', 'badge' => 'bg-cyan-lt text-cyan', 'accent' => '#0891b2'],
            'pallet_area' => ['label' => 'Khu pallet', 'icon' => 'ti ti-grid-dots', 'badge' => 'bg-purple-lt text-purple', 'accent' => '#9333ea'],
            'pallet_slot' => ['label' => 'Slot pallet', 'icon' => 'ti ti-square', 'badge' => 'bg-green-lt text-green', 'accent' => '#047857'],
            'receiving' => ['label' => 'Nhận hàng', 'icon' => 'ti ti-inbox', 'badge' => 'bg-info-lt text-info', 'accent' => '#2563eb'],
            'waiting_putaway' => ['label' => 'Chờ xếp', 'icon' => 'ti ti-clock-hour-4', 'badge' => 'bg-primary-lt text-primary', 'accent' => '#7c3aed'],
            'qc_hold' => ['label' => 'Giữ QC', 'icon' => 'ti ti-shield-check', 'badge' => 'bg-warning-lt text-warning', 'accent' => '#d97706'],
            'damaged' => ['label' => 'Hàng lỗi', 'icon' => 'ti ti-alert-triangle', 'badge' => 'bg-danger-lt text-danger', 'accent' => '#dc2626'],
            'rejected' => ['label' => 'Từ chối', 'icon' => 'ti ti-ban', 'badge' => 'bg-danger-lt text-danger', 'accent' => '#b91c1c'],
            'return_area' => ['label' => 'Khu trả hàng', 'icon' => 'ti ti-rotate-clockwise', 'badge' => 'bg-secondary-lt text-secondary', 'accent' => '#475569'],
            'dispatch' => ['label' => 'Xuất hàng', 'icon' => 'ti ti-truck', 'badge' => 'bg-success-lt text-success', 'accent' => '#15803d'],
        ];
    }

    public static function storageModeMeta(): array
    {
        return [
            'direct' => ['label' => 'Hàng để trực tiếp', 'short_label' => 'Không pallet', 'description' => 'Hàng được lưu trực tiếp trong kệ, tầng hoặc ô chứa. Phù hợp kho nhỏ, shop, hàng lẻ.', 'icon' => 'ti ti-shelf'],
            'pallet' => ['label' => 'Hàng đặt trên pallet', 'short_label' => 'Có pallet', 'description' => 'Hàng nằm trên pallet, pallet nằm trong slot/rack. Phù hợp kho lớn, xe nâng, di chuyển nguyên pallet.', 'icon' => 'ti ti-stack-2'],
        ];
    }

    public static function mapTypeMeta(): array
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

    public static function mapEditorTools(): array
    {
        return [
            'receiving_area' => ['label' => 'Khu nhận hàng', 'icon' => 'ti ti-inbox', 'item_type' => 'receiving_area', 'shape_type' => 'rect', 'width' => 192, 'height' => 96, 'color' => '#dbeafe', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'receiving_area', 'stockable' => false, 'width_count' => 2, 'height_count' => 1, 'length_count' => 1]],
            'qc_area' => ['label' => 'Khu QC', 'icon' => 'ti ti-shield-check', 'item_type' => 'qc_area', 'shape_type' => 'rect', 'width' => 192, 'height' => 96, 'color' => '#fef3c7', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'qc_area', 'stockable' => false, 'width_count' => 2, 'height_count' => 1, 'length_count' => 1]],
            'staging_area' => ['label' => 'Khu chờ xếp', 'icon' => 'ti ti-hourglass', 'item_type' => 'staging_area', 'shape_type' => 'rect', 'width' => 192, 'height' => 96, 'color' => '#ede9fe', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'staging_area', 'stockable' => false, 'width_count' => 2, 'height_count' => 1, 'length_count' => 1]],
            'zone' => ['label' => 'Khu lưu hàng', 'icon' => 'ti ti-layout-grid', 'item_type' => 'zone', 'shape_type' => 'rect', 'width' => 192, 'height' => 192, 'color' => '#dbeafe', 'storage_modes' => ['direct'], 'meta_json' => ['module_type' => 'zone', 'stockable' => true, 'storage_mode' => 'direct', 'width_count' => 2, 'height_count' => 1, 'length_count' => 2]],
            'bin_area' => ['label' => 'Khu ngoại lệ', 'icon' => 'ti ti-alert-triangle', 'item_type' => 'bin_area', 'shape_type' => 'rect', 'width' => 96, 'height' => 96, 'color' => '#fecaca', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'bin_area', 'stockable' => false, 'width_count' => 1, 'height_count' => 1, 'length_count' => 1]],
            'aisle' => ['label' => 'Lối đi', 'icon' => 'ti ti-route', 'item_type' => 'aisle', 'shape_type' => 'rect', 'width' => 96, 'height' => 384, 'color' => '#e2e8f0', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'aisle', 'stockable' => false, 'width_count' => 1, 'length_count' => 4]],
            'simple_shelf' => ['label' => 'Kệ', 'icon' => 'ti ti-shelf', 'item_type' => 'simple_shelf', 'shape_type' => 'rect', 'width' => 256, 'height' => 112, 'color' => '#e0f2fe', 'storage_modes' => ['direct'], 'meta_json' => ['module_type' => 'simple_shelf', 'storage_mode' => 'direct', 'prefix' => 'SHELF-A', 'width_count' => 1, 'height_count' => 2, 'length_count' => 4, 'uses_pallet' => false]],
            'pallet_rack' => ['label' => 'Rack pallet 4 x 4', 'icon' => 'ti ti-stack-2', 'item_type' => 'pallet_rack', 'shape_type' => 'rect', 'width' => 320, 'height' => 150, 'color' => '#dcfce7', 'storage_modes' => ['pallet'], 'meta_json' => ['module_type' => 'pallet_rack', 'storage_mode' => 'pallet', 'prefix' => 'RACK-A', 'level_count' => 4, 'positions_per_level' => 4, 'pallets_per_position' => 1]],
            'pallet_slot' => ['label' => 'Slot pallet', 'icon' => 'ti ti-square', 'item_type' => 'pallet_slot', 'shape_type' => 'rect', 'width' => 110, 'height' => 80, 'color' => '#bbf7d0', 'storage_modes' => ['pallet'], 'meta_json' => ['module_type' => 'pallet_slot', 'storage_mode' => 'pallet', 'pallets_per_position' => 1]],
            'floor_pallet_area' => ['label' => 'Khu pallet sàn', 'icon' => 'ti ti-grid-dots', 'item_type' => 'floor_pallet_area', 'shape_type' => 'rect', 'width' => 260, 'height' => 150, 'color' => '#ede9fe', 'storage_modes' => ['pallet'], 'meta_json' => ['module_type' => 'floor_pallet_area', 'storage_mode' => 'pallet', 'prefix' => 'PALLET-A', 'row_count' => 4, 'column_count' => 4, 'pallets_per_position' => 1]],
            'label' => ['label' => 'Text / Label', 'icon' => 'ti ti-text-size', 'item_type' => 'label', 'shape_type' => 'label', 'width' => 180, 'height' => 52, 'color' => '#0f172a', 'storage_modes' => ['direct', 'pallet'], 'meta_json' => ['module_type' => 'label', 'stockable' => false]],
        ];
    }
}
