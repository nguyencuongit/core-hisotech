<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class Import extends BaseModel
{
    protected $table = 'inv_imports';

    protected $fillable = [
        'type',
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

        'doc_code',
        'reference_id',
        'reference_code',

        'posting_date',
        'document_date',
        'received_at',

        'receiver_id',
        'receiver_name',
        'receiver_phone',

        'status',
        'note',

        'lot_no',
        'expiry_date',
    ];

    protected $casts = [
        
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
