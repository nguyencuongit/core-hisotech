<?php

namespace Botble\Inventory\Domains\Supplier\Models;

use Botble\Base\Models\BaseModel;
use Botble\Inventory\Enums\SupplierAddressTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierAddress extends BaseModel
{
    protected $table = 'inv_supplier_addresses';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'supplier_id',
        'type',
        'is_default',
        'address',
        'ward_id',
        'district_id',
        'province_id',
        'country_id',
    ];

    protected $casts = [
        'type' => SupplierAddressTypeEnum::class,
        'is_default' => 'bool',
    ];

    public $timestamps = false;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
