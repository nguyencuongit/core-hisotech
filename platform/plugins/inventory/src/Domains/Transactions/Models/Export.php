<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'warehouse_id' => 'integer',
        'partner_id' => 'integer',
        'province_id' => 'integer',
        'ward_id' => 'integer',
        'requested_by' => 'integer',
        'reference_id' => 'integer',
        'document_date' => 'date',
        'posting_date' => 'date',
        'shipped_at' => 'datetime',
        'shipping_fee' => 'decimal:4',
        'created_by' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'completed_by' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExportItem::class, 'export_id');
    }
}
