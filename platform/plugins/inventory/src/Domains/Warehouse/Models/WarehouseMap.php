<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseMap extends BaseModel
{
    protected $table = 'inv_warehouse_maps';

    protected $fillable = [
        'warehouse_id',
        'name',
        'map_type',
        'background_image',
        'width',
        'height',
        'scale_ratio',
        'is_active',
        'created_by',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseMapItem::class, 'warehouse_map_id');
    }
}
