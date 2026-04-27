<?php

namespace Botble\Inventory\Domains\Warehouse\Support;

class PalletLocationRules
{
    public const ALLOWED_TYPES = [
        'receiving',
        'waiting_putaway',
        'qc_hold',
        'damaged',
        'rejected',
        'level',
        'bin',
        'pallet_area',
        'pallet_slot',
        'staging_area',
        'dispatch',
    ];

    public static function allowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }

    public static function isAllowed(?string $type): bool
    {
        return $type !== null && in_array($type, self::ALLOWED_TYPES, true);
    }
}
