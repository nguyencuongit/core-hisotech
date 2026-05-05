<?php

namespace Botble\Inventory\Enums;

enum DocumentStatusEnum: string
{
    case DRAFT = 'draft';            // Nháp
    case CONFIRMED = 'confirmed';    // Đã xác nhận
    case CANCELLED = 'cancelled';    // Hủy

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Nháp',
            self::CONFIRMED => 'Đã xác nhận',
            self::CANCELLED => 'Đã huỷ',
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