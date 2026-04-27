<?php

namespace Botble\Inventory\Domains\Packing\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_packages';

    protected $fillable = [
        'packing_list_id',
        'package_code',
        'package_type',
        'length',
        'width',
        'height',
        'weight',
        'weight_unit',
        'note',
    ];

    protected $casts = [
        'packing_list_id' => 'integer',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackingListItem::class, 'packing_id');
    }
}
