<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaffAssignments;


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

    public static function getStaffsWithUser()
    {
        return DB::table('inv_warehouse_staff as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->select(
                's.id',
                's.user_id',
                's.staff_code',
                's.full_name',
                's.phone',
                's.email',
                's.status',
                'u.name as user_name',
                'u.email as user_email'
            )
            ->get();
    }
    public static function findWithUser(int $id)
    {
        return DB::table('inv_warehouse_staff as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->select(
                's.id',
                's.user_id',
                's.staff_code',
                's.full_name',
                's.phone',
                's.email',
                's.status',
                'u.name as user_name',
                'u.email as user_email'
            )
            ->where('s.id', $id)
            ->first();
    }

   public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
    public function assignments()
    {
        return $this->hasMany(WarehouseStaffAssignments::class, 'staff_id');
    }
}
