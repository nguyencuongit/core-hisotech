<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseMapItem extends BaseModel
{
    protected $table = 'inv_warehouse_map_items';

    protected $fillable = [
        'warehouse_map_id',
        'location_id',
        'item_type',
        'label',
        'shape_type',
        'x',
        'y',
        'width',
        'height',
        'rotation',
        'color',
        'z_index',
        'is_clickable',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
        'is_clickable' => 'boolean',
    ];

    public function map(): BelongsTo
    {
        return $this->belongsTo(WarehouseMap::class, 'warehouse_map_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }
}
