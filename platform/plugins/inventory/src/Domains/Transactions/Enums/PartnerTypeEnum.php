<?php

namespace Botble\Inventory\Domains\Transactions\Enums;

enum PartnerTypeEnum: string
{
    case CUSTOMER = 'customer';
    case SUPPLIER = 'supplier';
    case WAREHOUSE = 'warehouse';

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Khách hàng',
            self::SUPPLIER => 'Nhà cung cấp',
            self::WAREHOUSE => 'Kho nội bộ',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($item) => [
                $item->value => $item->label(),
            ])
            ->toArray();
    }
}