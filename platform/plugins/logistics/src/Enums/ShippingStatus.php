<?php
namespace Botble\Logistics\Enums;

enum ShippingStatus: string
{
    case CREATED = 'created';
    case PICKED = 'picked';
    case SHIPPING = 'shipping';
    case DELIVERED = 'delivered';
    case CANCEL = 'cancel';
    case FAILED = 'failed';

    // label hiển thị UI
    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Tạo đơn thành công',
            self::PICKED => 'Đã lấy hàng',
            self::SHIPPING => 'Đang giao hàng',
            self::DELIVERED => 'Giao hàng thành công',
            self::CANCEL => 'Huỷ giao hàng',
            self::FAILED => 'Giao hàng thất bại',
        };
    }
}