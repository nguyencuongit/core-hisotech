<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function locationTree(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'warehouse_id')->orderBy('path')->orderBy('name');
    }

    public function maps(): HasMany
    {
        return $this->hasMany(WarehouseMap::class, 'warehouse_id');
    }

    public function setting(): HasOne
    {
        return $this->hasOne(WarehouseSetting::class, 'warehouse_id');
    }
}
