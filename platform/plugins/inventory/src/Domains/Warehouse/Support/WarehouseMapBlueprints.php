<?php

namespace Botble\Inventory\Domains\Warehouse\Support;

class WarehouseMapBlueprints
{
    public static function all(): array
    {
        return [
            'simple' => [
                'name' => 'Sơ đồ kho cơ bản',
                'description' => 'Bố cục một chiều dễ hiểu: nhận hàng, chờ xếp, lưu trữ và khu ngoại lệ.',
                'storage_mode' => 'direct',
                'background_image' => null,
                'width' => 1200,
                'height' => 760,
                'items' => [
                    ['item_type' => 'dock', 'shape_type' => 'rect', 'label' => 'Cửa nhận', 'x' => 36, 'y' => 28, 'width' => 180, 'height' => 72, 'color' => '#c7d2fe'],
                    ['item_type' => 'receiving_area', 'shape_type' => 'rect', 'label' => 'Nhận hàng', 'x' => 36, 'y' => 118, 'width' => 220, 'height' => 150, 'color' => '#dbeafe', 'location_code' => 'RECEIVING'],
                    ['item_type' => 'staging_area', 'shape_type' => 'rect', 'label' => 'Chờ xếp', 'x' => 280, 'y' => 118, 'width' => 220, 'height' => 150, 'color' => '#ede9fe', 'location_code' => 'WAITING_PUTAWAY'],
                    ['item_type' => 'aisle', 'shape_type' => 'rect', 'label' => 'Lối đi chính', 'x' => 528, 'y' => 28, 'width' => 92, 'height' => 640, 'color' => '#e2e8f0'],
                    ['item_type' => 'zone', 'shape_type' => 'rect', 'label' => 'Lưu trữ', 'x' => 648, 'y' => 118, 'width' => 468, 'height' => 392, 'color' => '#dcfce7', 'location_code' => 'STORAGE'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'Dãy A', 'x' => 676, 'y' => 150, 'width' => 128, 'height' => 328, 'color' => '#bbf7d0'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'Dãy B', 'x' => 826, 'y' => 150, 'width' => 128, 'height' => 328, 'color' => '#bbf7d0'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'Dãy C', 'x' => 976, 'y' => 150, 'width' => 112, 'height' => 328, 'color' => '#bbf7d0'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Hàng lỗi', 'x' => 648, 'y' => 544, 'width' => 220, 'height' => 96, 'color' => '#fecaca', 'location_code' => 'DAMAGED'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Từ chối', 'x' => 896, 'y' => 544, 'width' => 220, 'height' => 96, 'color' => '#fee2e2', 'location_code' => 'REJECTED'],
                ],
            ],
            'qc' => [
                'name' => 'Sơ đồ kho có QC',
                'description' => 'Luồng nhận hàng đi qua QC trước khi vào khu chờ xếp và lưu trữ.',
                'storage_mode' => 'direct',
                'background_image' => null,
                'width' => 1200,
                'height' => 760,
                'items' => [
                    ['item_type' => 'dock', 'shape_type' => 'rect', 'label' => 'Cửa nhận', 'x' => 36, 'y' => 28, 'width' => 190, 'height' => 72, 'color' => '#c7d2fe'],
                    ['item_type' => 'receiving_area', 'shape_type' => 'rect', 'label' => 'Nhận hàng', 'x' => 36, 'y' => 118, 'width' => 206, 'height' => 148, 'color' => '#dbeafe', 'location_code' => 'RECEIVING'],
                    ['item_type' => 'qc_area', 'shape_type' => 'rect', 'label' => 'QC giữ hàng', 'x' => 266, 'y' => 118, 'width' => 206, 'height' => 148, 'color' => '#fef3c7', 'location_code' => 'QC_HOLD'],
                    ['item_type' => 'staging_area', 'shape_type' => 'rect', 'label' => 'Chờ xếp', 'x' => 496, 'y' => 118, 'width' => 206, 'height' => 148, 'color' => '#ede9fe', 'location_code' => 'WAITING_PUTAWAY'],
                    ['item_type' => 'aisle', 'shape_type' => 'rect', 'label' => 'Lối đi chính', 'x' => 724, 'y' => 28, 'width' => 88, 'height' => 640, 'color' => '#e2e8f0'],
                    ['item_type' => 'zone', 'shape_type' => 'rect', 'label' => 'Lưu trữ', 'x' => 836, 'y' => 118, 'width' => 286, 'height' => 392, 'color' => '#dcfce7', 'location_code' => 'STORAGE'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'Kệ A', 'x' => 860, 'y' => 152, 'width' => 112, 'height' => 324, 'color' => '#bbf7d0'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'Kệ B', 'x' => 988, 'y' => 152, 'width' => 112, 'height' => 324, 'color' => '#bbf7d0'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Hàng lỗi', 'x' => 36, 'y' => 544, 'width' => 206, 'height' => 96, 'color' => '#fecaca', 'location_code' => 'DAMAGED'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Từ chối', 'x' => 266, 'y' => 544, 'width' => 206, 'height' => 96, 'color' => '#fee2e2', 'location_code' => 'REJECTED'],
                ],
            ],
            'rack_floor' => [
                'name' => 'Sơ đồ kho rack 4x4x10',
                'description' => 'Mô phỏng kho trực quan theo rack chuẩn 4 ô ngang, 4 tầng và 10 dãy rack đồng nhất.',
                'storage_mode' => 'direct',
                'background_image' => null,
                'width' => 1440,
                'height' => 860,
                'items' => [
                    ['item_type' => 'dock', 'shape_type' => 'rect', 'label' => 'Cửa nhập', 'x' => 38, 'y' => 32, 'width' => 210, 'height' => 72, 'color' => '#c7d2fe'],
                    ['item_type' => 'receiving_area', 'shape_type' => 'rect', 'label' => 'Nhận hàng', 'x' => 38, 'y' => 124, 'width' => 250, 'height' => 152, 'color' => '#dbeafe', 'location_code' => 'RECEIVING'],
                    ['item_type' => 'qc_area', 'shape_type' => 'rect', 'label' => 'QC', 'x' => 312, 'y' => 124, 'width' => 214, 'height' => 152, 'color' => '#fef3c7', 'location_code' => 'QC_HOLD'],
                    ['item_type' => 'staging_area', 'shape_type' => 'rect', 'label' => 'Chờ xếp', 'x' => 550, 'y' => 124, 'width' => 214, 'height' => 152, 'color' => '#ede9fe', 'location_code' => 'WAITING_PUTAWAY'],
                    ['item_type' => 'aisle', 'shape_type' => 'rect', 'label' => 'Lối đi chính', 'x' => 792, 'y' => 32, 'width' => 96, 'height' => 744, 'color' => '#e2e8f0'],
                    ['item_type' => 'zone', 'shape_type' => 'rect', 'label' => 'Dãy rack 4x4x10', 'x' => 920, 'y' => 124, 'width' => 430, 'height' => 520, 'color' => '#dcfce7', 'location_code' => 'ZONE-A'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'RACK-01', 'x' => 958, 'y' => 170, 'width' => 140, 'height' => 420, 'color' => '#bbf7d0', 'location_code' => 'RACK-01', 'meta_json' => ['module_type' => 'rack', 'width_count' => 4, 'height_count' => 4, 'length_count' => 10, 'pallets_per_position' => 1]],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'RACK-02', 'x' => 1160, 'y' => 170, 'width' => 140, 'height' => 420, 'color' => '#bbf7d0', 'location_code' => 'RACK-02', 'meta_json' => ['module_type' => 'rack', 'width_count' => 4, 'height_count' => 4, 'length_count' => 10, 'pallets_per_position' => 1]],
                    ['item_type' => 'label', 'shape_type' => 'label', 'label' => '4 × 4 × 10', 'x' => 980, 'y' => 616, 'width' => 300, 'height' => 36, 'color' => '#0f172a'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Hàng lỗi', 'x' => 920, 'y' => 682, 'width' => 198, 'height' => 94, 'color' => '#fecaca', 'location_code' => 'DAMAGED'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Từ chối', 'x' => 1152, 'y' => 682, 'width' => 198, 'height' => 94, 'color' => '#fee2e2', 'location_code' => 'REJECTED'],
                ],
            ],
            'pallet_rack' => [
                'name' => 'Sơ đồ kho pallet rack',
                'description' => 'Dành cho kho dùng pallet: nhận hàng, staging, lối xe nâng và các rack pallet. Rack chỉ sinh slot chứa pallet, không tự sinh pallet.',
                'storage_mode' => 'pallet',
                'background_image' => null,
                'width' => 1500,
                'height' => 900,
                'items' => [
                    ['item_type' => 'dock', 'shape_type' => 'rect', 'label' => 'Cửa nhập', 'x' => 40, 'y' => 36, 'width' => 230, 'height' => 76, 'color' => '#c7d2fe'],
                    ['item_type' => 'receiving_area', 'shape_type' => 'rect', 'label' => 'Khu nhận hàng', 'x' => 40, 'y' => 136, 'width' => 260, 'height' => 150, 'color' => '#dbeafe', 'location_code' => 'RECEIVING'],
                    ['item_type' => 'staging_area', 'shape_type' => 'rect', 'label' => 'Khu chờ xếp pallet', 'x' => 40, 'y' => 318, 'width' => 260, 'height' => 150, 'color' => '#ede9fe', 'location_code' => 'WAITING_PUTAWAY'],
                    ['item_type' => 'qc_area', 'shape_type' => 'rect', 'label' => 'QC giữ pallet', 'x' => 40, 'y' => 500, 'width' => 260, 'height' => 124, 'color' => '#fef3c7', 'location_code' => 'QC_HOLD'],
                    ['item_type' => 'aisle', 'shape_type' => 'rect', 'label' => 'Lối xe nâng', 'x' => 340, 'y' => 36, 'width' => 112, 'height' => 760, 'color' => '#e2e8f0'],
                    ['item_type' => 'pallet_rack', 'shape_type' => 'rect', 'label' => 'RACK-A 4 x 4', 'x' => 500, 'y' => 120, 'width' => 330, 'height' => 160, 'color' => '#dcfce7', 'meta_json' => ['module_type' => 'pallet_rack', 'level_count' => 4, 'positions_per_level' => 4, 'pallets_per_position' => 1]],
                    ['item_type' => 'pallet_rack', 'shape_type' => 'rect', 'label' => 'RACK-B 4 x 4', 'x' => 500, 'y' => 340, 'width' => 330, 'height' => 160, 'color' => '#dcfce7', 'meta_json' => ['module_type' => 'pallet_rack', 'level_count' => 4, 'positions_per_level' => 4, 'pallets_per_position' => 1]],
                    ['item_type' => 'pallet_rack', 'shape_type' => 'rect', 'label' => 'RACK-C 4 x 4', 'x' => 900, 'y' => 120, 'width' => 330, 'height' => 160, 'color' => '#dcfce7', 'meta_json' => ['module_type' => 'pallet_rack', 'level_count' => 4, 'positions_per_level' => 4, 'pallets_per_position' => 1]],
                    ['item_type' => 'pallet_rack', 'shape_type' => 'rect', 'label' => 'RACK-D 4 x 4', 'x' => 900, 'y' => 340, 'width' => 330, 'height' => 160, 'color' => '#dcfce7', 'meta_json' => ['module_type' => 'pallet_rack', 'level_count' => 4, 'positions_per_level' => 4, 'pallets_per_position' => 1]],
                    ['item_type' => 'floor_pallet_area', 'shape_type' => 'rect', 'label' => 'Pallet sàn', 'x' => 500, 'y' => 560, 'width' => 730, 'height' => 170, 'color' => '#ede9fe', 'meta_json' => ['module_type' => 'floor_pallet_area', 'row_count' => 3, 'column_count' => 5, 'pallets_per_position' => 1]],
                    ['item_type' => 'aisle', 'shape_type' => 'rect', 'label' => 'Lối xuất', 'x' => 1280, 'y' => 36, 'width' => 92, 'height' => 760, 'color' => '#e2e8f0'],
                ],
            ],
        ];
    }
}
