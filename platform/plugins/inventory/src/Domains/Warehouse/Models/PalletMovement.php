<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalletMovement extends BaseModel
{
    protected $table = 'inv_pallet_movements';

    protected $fillable = [
        'pallet_id',
        'warehouse_id',
        'from_location_id',
        'to_location_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class, 'pallet_id');
    }
}
