<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseSetting extends BaseModel
{
    protected $table = 'inv_warehouse_settings';

    protected $fillable = [
        'warehouse_id',
        'warehouse_mode',
        'use_pallet',
        'require_pallet',
        'use_qc',
        'use_batch',
        'use_serial',
        'use_map',
        'default_receiving_location_id',
        'default_waiting_putaway_location_id',
        'default_qc_location_id',
        'default_damaged_location_id',
        'default_rejected_location_id',
    ];

    protected $casts = [
        'use_pallet' => 'bool',
        'require_pallet' => 'bool',
        'use_qc' => 'bool',
        'use_batch' => 'bool',
        'use_serial' => 'bool',
        'use_map' => 'bool',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
