<?php
namespace Botble\Logistics\Enums;

enum ShipmentStatus: string
{
    case READY_TO_SHIP = 'ready_to_be_shipped_out';
    case PICKED = 'picked';
    case DELIVERING = 'delivering';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';

    // label hiển thị UI
    public function label(): string
    {
        return match ($this) {
            self::READY_TO_SHIP => 'Sẵn sàng giao',
            self::PICKED => 'Đã lấy hàng',
            self::DELIVERING => 'Đang giao',
            self::DELIVERED => 'Đã giao',
            self::CANCELED => 'Đã huỷ',
        };
    }
}