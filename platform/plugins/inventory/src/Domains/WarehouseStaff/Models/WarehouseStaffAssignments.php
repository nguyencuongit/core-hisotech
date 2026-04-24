<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaff;

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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    public function staff()
    {
        return $this->belongsTo(WarehouseStaff::class, 'staff_id');
    }
}
