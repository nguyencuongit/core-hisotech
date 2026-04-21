<?php

namespace Botble\Inventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends BaseModel
{
    protected $table = 'inv_supplier_products';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'supplier_id',
        'product_id',
        'supplier_sku',
        'purchase_price',
        'moq',
        'lead_time_days',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:4',
        'moq' => 'integer',
        'lead_time_days' => 'integer',
    ];

    public $timestamps = false;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
