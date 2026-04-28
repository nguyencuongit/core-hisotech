<?php

namespace Botble\Inventory\Domains\Transactions\Enums;

enum DocumentStatusEnum: string
{
    case DRAFT = 'draft';            // Nháp1
    case CONFIRMED = 'confirmed';    // Đã xác nhận1
    case PICKING = 'picking';        // Đang lấy hàng (xuất)
    case SHIPPING = 'shipping';      // Đang giao (xuất)
    case RECEIVING = 'receiving';    // Đang nhận (nhập)
    case COMPLETED = 'completed';    // Hoàn thành
    case CANCELLED = 'cancelled';    // Hủy1

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Nháp',
            self::CONFIRMED => 'Đã xác nhận',
            self::PICKING => 'Đang lấy hàng',
            self::SHIPPING => 'Đang giao hàng',
            self::RECEIVING => 'Đang nhận hàng',
            self::COMPLETED => 'Hoàn thành',
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