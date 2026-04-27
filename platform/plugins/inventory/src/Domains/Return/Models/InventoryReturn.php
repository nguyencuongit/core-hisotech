<?php

namespace Botble\Inventory\Domains\Return\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryReturn extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_returns';

    protected $fillable = [
        'code',
        'type',
        'status',
        'warehouse_id',
        'partner_type',
        'partner_id',
        'partner_code',
        'partner_name',
        'partner_phone',
        'reference_type',
        'reference_id',
        'reference_code',
        'reason',
        'requested_by',
        'approved_by',
        'note',
    ];

    protected $casts = [
        'warehouse_id' => 'integer',
        'partner_id' => 'integer',
        'reference_id' => 'integer',
        'requested_by' => 'integer',
        'approved_by' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
