<?php

namespace Botble\Inventory\Domains\Supplier\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierBank extends BaseModel
{
    protected $table = 'inv_supplier_banks';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'supplier_id',
        'is_default',
        'bank_name',
        'branch',
        'account_number',
        'account_name',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];

    public $timestamps = false;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
