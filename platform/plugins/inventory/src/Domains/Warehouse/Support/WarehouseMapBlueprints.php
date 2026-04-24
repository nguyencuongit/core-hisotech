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
                'name' => 'Sơ đồ kho nhiều kệ',
                'description' => 'Mô phỏng kho có tầng, zone và nhiều dãy kệ với lối đi trung tâm.',
                'background_image' => null,
                'width' => 1400,
                'height' => 860,
                'items' => [
                    ['item_type' => 'dock', 'shape_type' => 'rect', 'label' => 'Cửa nhập', 'x' => 38, 'y' => 32, 'width' => 210, 'height' => 72, 'color' => '#c7d2fe'],
                    ['item_type' => 'receiving_area', 'shape_type' => 'rect', 'label' => 'Nhận hàng', 'x' => 38, 'y' => 124, 'width' => 250, 'height' => 152, 'color' => '#dbeafe', 'location_code' => 'RECEIVING'],
                    ['item_type' => 'qc_area', 'shape_type' => 'rect', 'label' => 'QC', 'x' => 312, 'y' => 124, 'width' => 214, 'height' => 152, 'color' => '#fef3c7', 'location_code' => 'QC_HOLD'],
                    ['item_type' => 'staging_area', 'shape_type' => 'rect', 'label' => 'Chờ xếp', 'x' => 550, 'y' => 124, 'width' => 214, 'height' => 152, 'color' => '#ede9fe', 'location_code' => 'WAITING_PUTAWAY'],
                    ['item_type' => 'aisle', 'shape_type' => 'rect', 'label' => 'Lối đi chính', 'x' => 792, 'y' => 32, 'width' => 96, 'height' => 744, 'color' => '#e2e8f0'],
                    ['item_type' => 'zone', 'shape_type' => 'rect', 'label' => 'Tầng F1 / Zone A', 'x' => 920, 'y' => 124, 'width' => 430, 'height' => 520, 'color' => '#dcfce7', 'location_code' => 'ZONE-A'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'RACK-01', 'x' => 958, 'y' => 170, 'width' => 140, 'height' => 420, 'color' => '#bbf7d0', 'location_code' => 'RACK-01'],
                    ['item_type' => 'rack', 'shape_type' => 'rect', 'label' => 'RACK-02', 'x' => 1160, 'y' => 170, 'width' => 140, 'height' => 420, 'color' => '#bbf7d0', 'location_code' => 'RACK-02'],
                    ['item_type' => 'label', 'shape_type' => 'label', 'label' => 'L1  L2  L3', 'x' => 970, 'y' => 616, 'width' => 310, 'height' => 36, 'color' => '#0f172a'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Hàng lỗi', 'x' => 920, 'y' => 682, 'width' => 198, 'height' => 94, 'color' => '#fecaca', 'location_code' => 'DAMAGED'],
                    ['item_type' => 'bin_area', 'shape_type' => 'rect', 'label' => 'Từ chối', 'x' => 1152, 'y' => 682, 'width' => 198, 'height' => 94, 'color' => '#fee2e2', 'location_code' => 'REJECTED'],
                ],
            ],
        ];
    }
}
