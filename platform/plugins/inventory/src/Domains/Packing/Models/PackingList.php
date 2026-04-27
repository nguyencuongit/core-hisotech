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
        'total_packages',
        'total_weight',
        'note',
    ];

    protected $casts = [
        'export_id' => 'integer',
        'warehouse_id' => 'integer',
        'packer_id' => 'integer',
        'packed_at' => 'datetime',
        'total_packages' => 'integer',
        'total_weight' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(PackingListItem::class, 'packing_list_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'packing_list_id');
    }
}
