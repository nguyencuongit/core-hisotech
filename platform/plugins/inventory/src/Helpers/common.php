<?php

use Botble\Inventory\Support\InventoryContext;

if (! function_exists('inventory_context')) {
    function inventory_context(): InventoryContext
    {
        return app(InventoryContext::class);
    }
}

if (! function_exists('inventory_warehouse_ids')) {
    function inventory_warehouse_ids(): array
    {
        return inventory_context()->warehouseIds() ?? [];
    }
}

if (! function_exists('inventory_is_super_admin')) {
    function inventory_is_super_admin(): bool
    {
        return inventory_context()->isSuperAdmin();
    }
}