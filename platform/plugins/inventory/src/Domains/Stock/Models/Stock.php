<?php

namespace Botble\Inventory\Domains\Stock\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_stocks';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'reserved_qty',
        'available_qty',
        'note',
        'unit_id',
        'unit_name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'warehouse_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'decimal:4',
        'reserved_qty' => 'decimal:4',
        'available_qty' => 'decimal:4',
        'unit_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockDetail::class, 'stock_id');
    }
}
