<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class WarehouseStaffAssignments extends BaseModel
{
    protected $table = 'inv_warehouse_staff_assignments';

    protected $fillable = [
        'staff_id',
        'warehouse_id',
        'position_id',
        'is_primary',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [

    ];
}
