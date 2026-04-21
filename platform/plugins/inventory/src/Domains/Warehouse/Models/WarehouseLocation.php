<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class WarehouseLocation extends BaseModel
{
    protected $table = 'inv_warehouse_locations';

    protected $fillable = [
        'warehouse_id',
        'parent_id',
        'code',
        'name',
        'type',
        'level',
        'path',
        'status',
        'description',
    ];

    protected $casts = [

    ];
}
