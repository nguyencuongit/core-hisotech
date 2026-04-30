<?php

namespace Botble\Inventory\Domains\Packing\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingList extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_packing_lists';

    protected $fillable = [
        'export_id',
        'warehouse_id',
        'code',
        'status',
        'packer_id',
        'packed_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancelled_reason',
        'total_packages',
        'total_items',
        'total_weight',
        'total_volume',
        'note',
    ];

    protected $casts = [
        'export_id' => 'integer',
        'warehouse_id' => 'integer',
        'packer_id' => 'integer',
        'packed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancelled_by' => 'integer',
        'total_packages' => 'integer',
        'total_items' => 'decimal:4',
        'total_weight' => 'decimal:4',
        'total_volume' => 'decimal:4',
    ];

    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class, 'export_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packer_id');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackingListItem::class, 'packing_list_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'packing_list_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PackingLog::class, 'packing_list_id');
    }
}
