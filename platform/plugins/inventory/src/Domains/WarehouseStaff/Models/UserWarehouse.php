<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

class UserWarehouse extends BaseModel
{
    protected $table = 'inv_user_warehouses';

    protected $fillable = [
        'user_id',
        'warehouse_id',
    ];
    protected $casts = [
      
    ];

    
}
