<?php

namespace Botble\Inventory\Domains\Transfer\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalTransfer extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_internal_transfers';

    protected $fillable = [
        'code',
        'status',
        'from_warehouse_id',
        'to_warehouse_id',
        'requested_by',
        'approved_by',
        'exported_by',
        'imported_by',
        'transfer_date',
        'reason',
        'note',
    ];

    protected $casts = [
        'from_warehouse_id' => 'integer',
        'to_warehouse_id' => 'integer',
        'requested_by' => 'integer',
        'approved_by' => 'integer',
        'exported_by' => 'integer',
        'imported_by' => 'integer',
        'transfer_date' => 'date',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InternalTransferItem::class, 'transfer_id');
    }
}
