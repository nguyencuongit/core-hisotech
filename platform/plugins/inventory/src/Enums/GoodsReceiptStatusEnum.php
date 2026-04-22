<?php

namespace Botble\Inventory\Enums;

enum GoodsReceiptStatusEnum: string
{
    case DRAFT = 'draft';
    case RECEIVING = 'receiving';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => trans('plugins/inventory::inventory.goods_receipt.status.draft'),
            self::RECEIVING => trans('plugins/inventory::inventory.goods_receipt.status.receiving'),
            self::COMPLETED => trans('plugins/inventory::inventory.goods_receipt.status.completed'),
            self::CANCELLED => trans('plugins/inventory::inventory.goods_receipt.status.cancelled'),
        };
    }

    public function toHtml(): string
    {
        $color = match ($this) {
            self::DRAFT => 'secondary',
            self::RECEIVING => 'warning',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };

        return sprintf('<span class="badge bg-%s">%s</span>', $color, e($this->label()));
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
