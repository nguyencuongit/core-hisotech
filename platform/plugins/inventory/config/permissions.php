<?php

return [
    [
        'name' => 'Inventories',
        'flag' => 'inventory',
    ],
    [
        'name' => 'Warehouse positions',
        'flag' => 'warehouse-positions.index',
        'parent_flag' => 'inventory',
    ],
    [
        'name' => 'Create',
        'flag' => 'warehouse-positions.create',
        'parent_flag' => 'warehouse-positions.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'warehouse-positions.edit',
        'parent_flag' => 'warehouse-positions.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'warehouse-positions.destroy',
        'parent_flag' => 'warehouse-positions.index',
    ],
];
