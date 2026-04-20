<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class WarehousePosition extends BaseModel
{
    protected $table = 'inv_warehouse_positions';

    protected $fillable = [
        'name',
        'code',
        'level',
        'is_active',
    ];

    protected $casts = [

    ];
}
