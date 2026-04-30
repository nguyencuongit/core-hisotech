<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseProductPolicy extends BaseModel
{
    protected $table = 'inv_warehouse_product_policies';

    protected $fillable = [
        'warehouse_product_id',
        'tracking_type',
        'is_expirable',
        'require_mfg_date',
        'require_expiry_date',
        'allow_pallet',
        'require_pallet',
        'require_qc',
        'placement_mode',
        'allow_mixed_batch_on_pallet',
        'allow_receive_without_location',
        'is_active',
    ];

    protected $casts = [
        'is_expirable' => 'bool',
        'require_mfg_date' => 'bool',
        'require_expiry_date' => 'bool',
        'allow_pallet' => 'bool',
        'require_pallet' => 'bool',
        'require_qc' => 'bool',
        'allow_mixed_batch_on_pallet' => 'bool',
        'allow_receive_without_location' => 'bool',
        'is_active' => 'bool',
    ];

    public function warehouseProduct(): BelongsTo
    {
        return $this->belongsTo(WarehouseProduct::class, 'warehouse_product_id');
    }
}
