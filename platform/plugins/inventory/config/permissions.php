<?php

return [
    [
        'name' => 'Inventories',
        'flag' => 'inventory',
    ],
    [
        'name' => 'Suppliers',
        'flag' => 'inventory.suppliers.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'inventory.suppliers.create',
        'parent_flag' => 'inventory.suppliers.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'inventory.suppliers.show',
        'parent_flag' => 'inventory.suppliers.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'inventory.suppliers.edit',
        'parent_flag' => 'inventory.suppliers.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'inventory.suppliers.delete',
        'parent_flag' => 'inventory.suppliers.index',
    ],
    [
        'name' => 'Manage contacts',
        'flag' => 'inventory.suppliers.manage_contacts',
        'parent_flag' => 'inventory.suppliers.index',
    ],
    [
        'name' => 'Manage addresses',
        'flag' => 'inventory.suppliers.manage_addresses',
        'parent_flag' => 'inventory.suppliers.index',
    ],
    [
        'name' => 'Manage banks',
        'flag' => 'inventory.suppliers.manage_banks',
        'parent_flag' => 'inventory.suppliers.index',
    ],
    [
        'name' => 'Manage products',
        'flag' => 'inventory.suppliers.manage_products',
        'parent_flag' => 'inventory.suppliers.index',
    ],
];
