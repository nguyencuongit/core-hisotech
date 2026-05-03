<?php

namespace Botble\Inventory\Domains\Transfer\Models;

use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Models\Import as InventoryImport;
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
        'export_id',
        'import_id',
        'requested_by',
        'approved_by',
        'exported_by',
        'imported_by',
        'transfer_date',
        'in_transit_at',
        'received_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancelled_reason',
        'reason',
        'note',
    ];

    protected $casts = [
        'from_warehouse_id' => 'integer',
        'to_warehouse_id' => 'integer',
        'export_id' => 'integer',
        'import_id' => 'integer',
        'requested_by' => 'integer',
        'approved_by' => 'integer',
        'exported_by' => 'integer',
        'imported_by' => 'integer',
        'cancelled_by' => 'integer',
        'transfer_date' => 'date',
        'in_transit_at' => 'datetime',
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    public function exportDoc(): BelongsTo
    {
        return $this->belongsTo(Export::class, 'export_id');
    }

    public function importDoc(): BelongsTo
    {
        return $this->belongsTo(InventoryImport::class, 'import_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InternalTransferLog::class, 'transfer_id')->latest('created_at');
    }
}
