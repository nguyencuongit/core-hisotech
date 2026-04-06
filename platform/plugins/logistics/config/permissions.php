<?php

return [
    [
        'name' => 'Logistics',
        'flag' => 'logistics.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'logistics.create',
        'parent_flag' => 'logistics.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'logistics.edit',
        'parent_flag' => 'logistics.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'logistics.destroy',
        'parent_flag' => 'logistics.index',
    ],
];
