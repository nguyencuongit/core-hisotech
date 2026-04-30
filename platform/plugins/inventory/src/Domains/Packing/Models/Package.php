<?php

namespace Botble\Inventory\Domains\Packing\Models;

use Botble\Base\Models\BaseModel;
use Botble\ACL\Models\User;
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
        'package_no',
        'package_type_id',
        'status',
        'length',
        'width',
        'height',
        'dimension_unit',
        'volume',
        'volume_weight',
        'weight',
        'weight_unit',
        'sealed_by',
        'sealed_at',
        'tracking_code',
        'shipping_label_url',
        'note',
    ];

    protected $casts = [
        'packing_list_id' => 'integer',
        'package_no' => 'integer',
        'length' => 'decimal:4',
        'width' => 'decimal:4',
        'height' => 'decimal:4',
        'volume' => 'decimal:4',
        'volume_weight' => 'decimal:4',
        'weight' => 'decimal:4',
        'sealed_by' => 'integer',
        'sealed_at' => 'datetime',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackingListItem::class, 'package_id');
    }

    public function legacyItems(): HasMany
    {
        return $this->hasMany(PackingListItem::class, 'packing_id');
    }

    public function sealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sealed_by');
    }
}
