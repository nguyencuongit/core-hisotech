<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class WarehouseStaff extends BaseModel
{
    protected $table = 'inv_warehouse_staff';

    protected $fillable = [
        'user_id',
        'staff_code',
        'full_name',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
      
    ];
}
