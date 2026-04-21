<?php

namespace Botble\Inventory\Enums;

enum SupplierAddressTypeEnum: string
{
    case HEADQUARTER = 'headquarter';
    case BILLING = 'billing';
    case SHIPPING = 'shipping';

    public function label(): string
    {
        return match ($this) {
            self::HEADQUARTER => trans('plugins/inventory::inventory.supplier.address_type.headquarter'),
            self::BILLING => trans('plugins/inventory::inventory.supplier.address_type.billing'),
            self::SHIPPING => trans('plugins/inventory::inventory.supplier.address_type.shipping'),
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
