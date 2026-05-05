<?php

use Botble\Inventory\Domains\Supplier\Permissions\SupplierPermissions;

return [
    [
        'name' => 'Inventories',
        'flag' => 'inventory',
    ],
    [
        'name' => 'Suppliers',
        'flag' => SupplierPermissions::INDEX,
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => SupplierPermissions::CREATE,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Show',
        'flag' => SupplierPermissions::SHOW,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Edit',
        'flag' => SupplierPermissions::EDIT,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Delete',
        'flag' => SupplierPermissions::DESTROY,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Manage contacts',
        'flag' => SupplierPermissions::MANAGE_CONTACTS,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Manage addresses',
        'flag' => SupplierPermissions::MANAGE_ADDRESSES,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Manage banks',
        'flag' => SupplierPermissions::MANAGE_BANKS,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Manage products',
        'flag' => SupplierPermissions::MANAGE_PRODUCTS,
        'parent_flag' => SupplierPermissions::INDEX,
    ],
    [
        'name' => 'Goods receipts',
        'flag' => 'inventory.goods-receipts.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'inventory.goods-receipts.create',
        'parent_flag' => 'inventory.goods-receipts.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'inventory.goods-receipts.show',
        'parent_flag' => 'inventory.goods-receipts.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'inventory.goods-receipts.edit',
        'parent_flag' => 'inventory.goods-receipts.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'inventory.goods-receipts.delete',
        'parent_flag' => 'inventory.goods-receipts.index',
    ],
        // staff
    [
        'name' => 'warehouse-staff',
        'flag' => 'warehouse-staff.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'warehouse-staff.create',
        'parent_flag' => 'warehouse-staff.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'warehouse-staff.edit',
        'parent_flag' => 'warehouse-staff.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'warehouse-staff.destroy',
        'parent_flag' => 'warehouse-staff.index',
    ],

       // warehouse
    [
        'name' => 'warehouse',
        'flag' => 'warehouse.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'warehouse.create',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'warehouse.edit',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'warehouse.show',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Manage products',
        'flag' => 'warehouse.products.manage',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'warehouse.destroy',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Warehouse locations',
        'flag' => 'warehouse.locations.manage',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Warehouse maps',
        'flag' => 'warehouse.maps.manage',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Warehouse maps',
        'flag' => 'warehouse.maps.manage',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Warehouse product policies',
        'flag' => 'warehouse.product-policies.manage',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Pallets',
        'flag' => 'warehouse.pallets.manage',
        'parent_flag' => 'warehouse.index',
    ],
    // import
    [
        'name' => 'import',
        'flag' => 'transactions-import.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'transactions-import.create',
        'parent_flag' => 'transactions-import.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'transactions-import.edit',
        'parent_flag' => 'transactions-import.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'transactions-import.show',
        'parent_flag' => 'transactions-import.index',
    ],
    [
        'name' => 'Manage products',
        'flag' => 'transactions-import.products.manage',
        'parent_flag' => 'transactions-import.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'transactions-import.destroy',
        'parent_flag' => 'transactions-import.index',
    ],

    // export
    [
        'name' => 'export',
        'flag' => 'transactions-export.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'transactions-export.create',
        'parent_flag' => 'transactions-export.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'transactions-export.edit',
        'parent_flag' => 'transactions-export.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'transactions-export.show',
        'parent_flag' => 'transactions-export.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'transactions-export.destroy',
        'parent_flag' => 'transactions-export.index',
    ],


    // packing
    [
        'name' => 'packing',
        'flag' => 'packing.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'packing.create',
        'parent_flag' => 'packing.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'packing.edit',
        'parent_flag' => 'packing.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'packing.show',
        'parent_flag' => 'packing.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'packing.destroy',
        'parent_flag' => 'packing.index',
    ],

    // transfer
    [
        'name' => 'transfer',
        'flag' => 'transfer.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'transfer.create',
        'parent_flag' => 'transfer.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'transfer.edit',
        'parent_flag' => 'transfer.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'transfer.show',
        'parent_flag' => 'transfer.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'transfer.destroy',
        'parent_flag' => 'transfer.index',
    ],
    [
        'name' => 'Approve transfer',
        'flag' => 'transfer.approve',
        'parent_flag' => 'transfer.index',
    ],
    [
        'name' => 'Export transfer',
        'flag' => 'transfer.export',
        'parent_flag' => 'transfer.index',
    ],
    [
        'name' => 'Receive transfer',
        'flag' => 'transfer.receive',
        'parent_flag' => 'transfer.index',
    ],
    [
        'name' => 'Cancel transfer',
        'flag' => 'transfer.cancel',
        'parent_flag' => 'transfer.index',
    ],

    // report
    [
        'name' => 'report',
        'flag' => 'report.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'report.create',
        'parent_flag' => 'report.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'report.edit',
        'parent_flag' => 'report.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'report.show',
        'parent_flag' => 'report.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'report.destroy',
        'parent_flag' => 'report.index',
    ],
    // return
    [
        'name' => 'return',
        'flag' => 'return.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'return.create',
        'parent_flag' => 'return.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'return.edit',
        'parent_flag' => 'return.index',
    ],
    [
        'name' => 'Show',
        'flag' => 'return.show',
        'parent_flag' => 'return.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'return.destroy',
        'parent_flag' => 'return.index',
    ],
];
