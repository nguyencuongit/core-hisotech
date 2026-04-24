<?php

namespace Botble\Inventory\Domains\Warehouse\Support;

class WarehousePolicyPresets
{
    public static function all(): array
    {
        return [
            'standard' => [
                'label' => 'Hàng thường',
                'description' => 'Không batch, không serial, không bắt buộc pallet.',
                'payload' => [
                    'tracking_type' => 'none',
                    'is_expirable' => false,
                    'require_mfg_date' => false,
                    'require_expiry_date' => false,
                    'allow_pallet' => false,
                    'require_pallet' => false,
                    'require_qc' => false,
                    'placement_mode' => 'assigned_on_receipt',
                    'allow_mixed_batch_on_pallet' => false,
                    'allow_receive_without_location' => true,
                    'is_active' => true,
                ],
            ],
            'batch_expiry' => [
                'label' => 'Hàng batch có hạn dùng',
                'description' => 'Quản lý theo batch, có hạn dùng và đi putaway sau khi nhận.',
                'payload' => [
                    'tracking_type' => 'batch',
                    'is_expirable' => true,
                    'require_mfg_date' => false,
                    'require_expiry_date' => true,
                    'allow_pallet' => true,
                    'require_pallet' => false,
                    'require_qc' => true,
                    'placement_mode' => 'putaway_after_receipt',
                    'allow_mixed_batch_on_pallet' => false,
                    'allow_receive_without_location' => false,
                    'is_active' => true,
                ],
            ],
            'batch_basic' => [
                'label' => 'Hàng theo batch',
                'description' => 'Quản lý batch nhưng không bắt buộc hạn dùng.',
                'payload' => [
                    'tracking_type' => 'batch',
                    'is_expirable' => false,
                    'require_mfg_date' => false,
                    'require_expiry_date' => false,
                    'allow_pallet' => false,
                    'require_pallet' => false,
                    'require_qc' => false,
                    'placement_mode' => 'assigned_on_receipt',
                    'allow_mixed_batch_on_pallet' => false,
                    'allow_receive_without_location' => true,
                    'is_active' => true,
                ],
            ],
            'serial' => [
                'label' => 'Hàng serial',
                'description' => 'Quản lý theo serial, không cần pallet.',
                'payload' => [
                    'tracking_type' => 'serial',
                    'is_expirable' => false,
                    'require_mfg_date' => false,
                    'require_expiry_date' => false,
                    'allow_pallet' => false,
                    'require_pallet' => false,
                    'require_qc' => false,
                    'placement_mode' => 'assigned_on_receipt',
                    'allow_mixed_batch_on_pallet' => false,
                    'allow_receive_without_location' => true,
                    'is_active' => true,
                ],
            ],
            'qc_required' => [
                'label' => 'Hàng bắt buộc QC',
                'description' => 'Bắt buộc giữ QC trước khi đưa vào vị trí lưu trữ.',
                'payload' => [
                    'tracking_type' => 'none',
                    'is_expirable' => false,
                    'require_mfg_date' => false,
                    'require_expiry_date' => false,
                    'allow_pallet' => false,
                    'require_pallet' => false,
                    'require_qc' => true,
                    'placement_mode' => 'putaway_after_receipt',
                    'allow_mixed_batch_on_pallet' => false,
                    'allow_receive_without_location' => false,
                    'is_active' => true,
                ],
            ],
            'palletized_bulk' => [
                'label' => 'Hàng cồng kềnh theo pallet',
                'description' => 'Cho phép và bắt buộc pallet, phù hợp hàng cồng kềnh hoặc nguyên kiện.',
                'payload' => [
                    'tracking_type' => 'none',
                    'is_expirable' => false,
                    'require_mfg_date' => false,
                    'require_expiry_date' => false,
                    'allow_pallet' => true,
                    'require_pallet' => true,
                    'require_qc' => false,
                    'placement_mode' => 'putaway_after_receipt',
                    'allow_mixed_batch_on_pallet' => true,
                    'allow_receive_without_location' => false,
                    'is_active' => true,
                ],
            ],
        ];
    }
}
