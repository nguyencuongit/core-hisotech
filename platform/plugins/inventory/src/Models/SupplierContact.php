<?php

namespace Botble\Inventory\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierContact extends BaseModel
{
    protected $table = 'inv_supplier_contacts';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'supplier_id',
        'is_primary',
        'name',
        'position',
        'phone',
        'email',
        'identity_number',
        'social_contact',
    ];

    protected $casts = [
        'social_contact' => 'array',
        'is_primary' => 'bool',
    ];

    public $timestamps = false;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
