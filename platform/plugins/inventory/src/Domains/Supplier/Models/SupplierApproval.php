<?php

namespace Botble\Inventory\Domains\Supplier\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierApproval extends BaseModel
{
    protected $table = 'inv_supplier_approvals';

    protected $fillable = [
        'supplier_id',
        'action',
        'from_status',
        'to_status',
        'note',
        'acted_by',
        'acted_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'acted_at' => 'datetime',
    ];

    public $timestamps = false;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

}
