<?php

namespace Botble\Inventory\Enums;

enum SupplierTypeEnum: string
{
    case COMPANY = 'company';
    case INDIVIDUAL = 'individual';

    public function label(): string
    {
        return match ($this) {
            self::COMPANY => trans('plugins/inventory::inventory.supplier.type.company'),
            self::INDIVIDUAL => trans('plugins/inventory::inventory.supplier.type.individual'),
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
