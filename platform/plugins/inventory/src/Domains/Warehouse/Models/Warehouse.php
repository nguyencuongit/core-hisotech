<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'warehouse_id');
    }

    public function warehouseProducts(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class, 'warehouse_id');
    }
}
