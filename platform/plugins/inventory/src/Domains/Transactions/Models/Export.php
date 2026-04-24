<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Export extends BaseModel
{
    protected $table = 'inv_exports';

    protected $fillable = [
        'type',
        'status',
        'warehouse_id',
        'partner_type',
        'partner_id',
        'partner_code',
        'partner_name',
        'partner_phone',
        'partner_email',
        'partner_address',
        'province_id',
        'ward_id',
        'requested_by',
        'requested_by_name',
        'code',
        'reference_id',
        'reference_code',
        'document_date',
        'posting_date',
        'shipped_at',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'delivery_name',
        'delivery_phone',
        'shipping_unit',
        'tracking_code',
        'shipping_fee',
        'note',
        'created_by',
        'approved_by',
        'approved_at',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
