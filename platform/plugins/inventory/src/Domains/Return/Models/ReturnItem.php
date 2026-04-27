<?php

namespace Botble\Inventory\Domains\Return\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnItem extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_return_items';

    protected $fillable = [
        'return_id',
        'product_id',
        'product_code',
        'product_name',
        'quantity',
        'unit_id',
        'unit_name',
        'condition',
        'reason',
        'reference_item_id',
        'unit_price',
        'amount',
        'note',
    ];

    protected $casts = [
        'return_id' => 'integer',
        'product_id' => 'integer',
        'unit_id' => 'integer',
        'reference_item_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function return(): BelongsTo
    {
        return $this->belongsTo(InventoryReturn::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
