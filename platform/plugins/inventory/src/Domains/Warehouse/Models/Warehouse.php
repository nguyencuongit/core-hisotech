<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Warehouse extends BaseModel
{
    protected $table = 'inv_warehouses';

    protected $fillable = [
        'name',
        'code',
        'type',
        'manager_id',
        'address',
        'province_id',
        'ward_id',
        'phone',
        'email',
        'status',
        'description',
    ];

    protected $casts = [

    ];
}
