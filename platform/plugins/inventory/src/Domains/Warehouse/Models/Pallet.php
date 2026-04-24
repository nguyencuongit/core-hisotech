<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pallet extends BaseModel
{
    protected $table = 'inv_pallets';

    protected $fillable = [
        'code',
        'warehouse_id',
        'current_location_id',
        'type',
        'status',
        'note',
        'created_by',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'current_location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(PalletMovement::class, 'pallet_id');
    }
}
