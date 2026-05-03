<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'warehouse_id' => 'integer',
        'partner_id' => 'integer',
        'province_id' => 'integer',
        'ward_id' => 'integer',
        'requested_by' => 'integer',
        'reference_id' => 'integer',
        'posting_date' => 'date',
        'document_date' => 'date',
        'received_at' => 'datetime',
        'receiver_id' => 'integer',
        'created_by' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ImportItem::class, 'import_id');
    }
}
