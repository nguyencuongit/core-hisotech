<?php

namespace Botble\Inventory\Domains\Supplier\Permissions;

/**
 * Source of truth for all Supplier-domain permission flags.
 *
 * Every place that checks or declares a Supplier permission MUST import these
 * constants — never hard-code the raw string. See platform/plugins/inventory/SKILL.md
 * section 4.2 "Domain Permissions Centralization" for the rule.
 *
 * Note on naming inconsistency:
 *   The destroy route uses the historical permission flag `inventory.suppliers.delete`
 *   (with `.delete`, not `.destroy`). Renaming would invalidate existing role
 *   assignments in the database, so the constant value is kept as-is and only the
 *   PHP-side identifier is named DESTROY for SKILL compliance.
 */
final class SupplierPermissions
{
    public const INDEX   = 'inventory.suppliers.index';
    public const CREATE  = 'inventory.suppliers.create';
    public const SHOW    = 'inventory.suppliers.show';
    public const EDIT    = 'inventory.suppliers.edit';
    public const DESTROY = 'inventory.suppliers.delete';

    // Sub-resource permissions (declared in config/permissions.php; reserved for finer-grained UI gating)
    public const MANAGE_CONTACTS  = 'inventory.suppliers.manage_contacts';
    public const MANAGE_ADDRESSES = 'inventory.suppliers.manage_addresses';
    public const MANAGE_BANKS     = 'inventory.suppliers.manage_banks';
    public const MANAGE_PRODUCTS  = 'inventory.suppliers.manage_products';

    /**
     * Returns every permission flag declared by this domain.
     * Used to keep config/permissions.php in sync.
     */
    public static function all(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::SHOW,
            self::EDIT,
            self::DESTROY,
            self::MANAGE_CONTACTS,
            self::MANAGE_ADDRESSES,
            self::MANAGE_BANKS,
            self::MANAGE_PRODUCTS,
        ];
    }
}
