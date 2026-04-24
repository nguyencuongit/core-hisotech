<?php

namespace Botble\Inventory\Domains\Transactions\Enums;

enum ExportTypeEnum: string
{
    case SALE = 'sale';             // Bán hàng
    case TRANSFER = 'transfer';     // Chuyển kho
    case RETURN = 'return';         // Trả nhà cung cấp
    case ADJUSTMENT = 'adjustment'; // Điều chỉnh kho
    case MANUAL = 'manual';         // Khác / thủ công

    public function label(): string
    {
        return match ($this) {
            self::SALE => 'Bán hàng',
            self::TRANSFER => 'Chuyển kho',
            self::RETURN => 'Trả nhà cung cấp',
            self::ADJUSTMENT => 'Điều chỉnh kho',
            self::MANUAL => 'Khác',
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