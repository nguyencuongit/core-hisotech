<?php

namespace Botble\Inventory\Enums;

enum SupplierStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case ACTIVE = 'active';
    case APPROVED = 'approved';
    case INACTIVE = 'inactive';
    case BLACKLISTED = 'blacklisted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => trans('plugins/inventory::inventory.supplier.status.draft'),
            self::PENDING_APPROVAL => trans('plugins/inventory::inventory.supplier.status.pending_approval'),
            self::ACTIVE, self::APPROVED => trans('plugins/inventory::inventory.supplier.status.active'),
            self::INACTIVE => trans('plugins/inventory::inventory.supplier.status.inactive'),
            self::BLACKLISTED => trans('plugins/inventory::inventory.supplier.status.blacklisted'),
            self::REJECTED => trans('plugins/inventory::inventory.supplier.status.rejected'),
        };
    }

    public function toHtml(): string
    {
        $color = match ($this) {
            self::DRAFT, self::PENDING_APPROVAL => 'secondary',
            self::ACTIVE, self::APPROVED => 'success',
            self::INACTIVE => 'warning',
            self::BLACKLISTED, self::REJECTED => 'danger',
        };

        return sprintf('<span class="badge bg-%s">%s</span>', $color, e($this->label()));
    }

    public function isApproved(): bool
    {
        return in_array($this, [self::ACTIVE, self::APPROVED], true);
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
