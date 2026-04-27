<?php

namespace Botble\Inventory\Domains\Warehouse\Support;

class WarehouseTemplateRegistry
{
    public static function all(): array
    {
        return [
            'simple' => [
                'name' => 'Kho đơn giản',
                'description' => 'Phù hợp kho nhỏ, triển khai nhanh với các khu hệ thống cơ bản.',
                'default_map_blueprint' => 'simple',
                'preview' => [
                    ['code' => 'RECEIVING', 'name' => 'Khu nhận hàng', 'type' => 'receiving'],
                    ['code' => 'WAITING_PUTAWAY', 'name' => 'Chờ putaway', 'type' => 'waiting_putaway'],
                    ['code' => 'STORAGE', 'name' => 'Khu lưu trữ', 'type' => 'zone', 'children' => [
                        ['code' => 'STORAGE-A', 'name' => 'Dãy lưu trữ A', 'type' => 'rack'],
                    ]],
                    ['code' => 'DAMAGED', 'name' => 'Hàng hư hỏng', 'type' => 'damaged'],
                    ['code' => 'REJECTED', 'name' => 'Hàng từ chối', 'type' => 'rejected'],
                ],
            ],
            'qc' => [
                'name' => 'Kho có QC',
                'description' => 'Có thêm khu kiểm tra chất lượng trước khi xếp hàng vào lưu trữ.',
                'default_map_blueprint' => 'qc',
                'preview' => [
                    ['code' => 'RECEIVING', 'name' => 'Khu nhận hàng', 'type' => 'receiving'],
                    ['code' => 'QC_HOLD', 'name' => 'Khu QC', 'type' => 'qc_hold'],
                    ['code' => 'WAITING_PUTAWAY', 'name' => 'Chờ putaway', 'type' => 'waiting_putaway'],
                    ['code' => 'STORAGE', 'name' => 'Khu lưu trữ', 'type' => 'zone'],
                    ['code' => 'DAMAGED', 'name' => 'Hàng hư hỏng', 'type' => 'damaged'],
                    ['code' => 'REJECTED', 'name' => 'Hàng từ chối', 'type' => 'rejected'],
                ],
            ],
            'rack_floor' => [
                'name' => 'Kho rack 4x4x10',
                'description' => 'Bố cục rack trực quan: 4 ô ngang, 4 tầng và 10 dãy rack theo chiều dài.',
                'default_map_blueprint' => 'rack_floor',
                'preview' => [
                    ['code' => 'RECEIVING', 'name' => 'Khu nhận hàng', 'type' => 'receiving'],
                    ['code' => 'WAITING_PUTAWAY', 'name' => 'Chờ putaway', 'type' => 'waiting_putaway'],
                    ['code' => 'QC_HOLD', 'name' => 'Khu QC', 'type' => 'qc_hold'],
                    ['code' => 'DAMAGED', 'name' => 'Hàng hư hỏng', 'type' => 'damaged'],
                    ['code' => 'REJECTED', 'name' => 'Hàng từ chối', 'type' => 'rejected'],
                    ['code' => 'RACK-CHAIN', 'name' => 'Dãy rack 4x4x10', 'type' => 'rack', 'children' => [
                        ['code' => 'RACK-01', 'name' => 'Rack 01', 'type' => 'rack', 'children' => [
                            ['code' => 'RACK-01-L1', 'name' => 'Tầng 1', 'type' => 'level'],
                            ['code' => 'RACK-01-L2', 'name' => 'Tầng 2', 'type' => 'level'],
                            ['code' => 'RACK-01-L3', 'name' => 'Tầng 3', 'type' => 'level'],
                            ['code' => 'RACK-01-L4', 'name' => 'Tầng 4', 'type' => 'level'],
                        ]],
                        ['code' => 'RACK-02', 'name' => 'Rack 02', 'type' => 'rack', 'children' => [
                            ['code' => 'RACK-02-L1', 'name' => 'Tầng 1', 'type' => 'level'],
                            ['code' => 'RACK-02-L2', 'name' => 'Tầng 2', 'type' => 'level'],
                            ['code' => 'RACK-02-L3', 'name' => 'Tầng 3', 'type' => 'level'],
                            ['code' => 'RACK-02-L4', 'name' => 'Tầng 4', 'type' => 'level'],
                        ]],
                        ['code' => 'RACK-03', 'name' => 'Rack 03', 'type' => 'rack', 'children' => [
                            ['code' => 'RACK-03-L1', 'name' => 'Tầng 1', 'type' => 'level'],
                            ['code' => 'RACK-03-L2', 'name' => 'Tầng 2', 'type' => 'level'],
                            ['code' => 'RACK-03-L3', 'name' => 'Tầng 3', 'type' => 'level'],
                            ['code' => 'RACK-03-L4', 'name' => 'Tầng 4', 'type' => 'level'],
                        ]],
                    ]],
                ],
            ],
        ];
    }

    public static function get(string $code): ?array
    {
        return static::all()[$code] ?? null;
    }
}
