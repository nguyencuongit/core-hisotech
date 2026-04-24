<?php

namespace Botble\Inventory\Domains\Transactions\Enums;

enum ImportTypeEnum: string
{
    case PURCHASE = 'purchase';       // Nhập mua
    case TRANSFER = 'transfer';       // Nhập chuyển kho
    case RETURN = 'return';           // Khách trả hàng
    case ADJUSTMENT = 'adjustment';   // Điều chỉnh tăng kho
    case MANUAL = 'manual';           // Khác / thủ công

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Nhập mua',
            self::TRANSFER => 'Nhập chuyển kho',
            self::RETURN => 'Khách trả hàng',
            self::ADJUSTMENT => 'Điều chỉnh tăng kho',
            self::MANUAL => 'Khác',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $item) => [
                $item->value => $item->label(),
            ])
            ->toArray();
    }

    public function partnerType(): ?string
    {
        return match ($this) {
            self::PURCHASE => 'supplier',
            self::TRANSFER => 'warehouse',
            self::RETURN => 'customer',
            self::ADJUSTMENT, self::MANUAL => null,
        };
    }
}