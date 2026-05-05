<?php

namespace Botble\Inventory\Domains\Stock\Models;

use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockDetail extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_stock_details';

    protected $fillable = [
        'stock_id',
        'lot_no',
        'expiry_date',
        'warehouse_location_id',
        'quantity',
        'reserved_qty',
        'available_qty',
        'note',
        'avg_cost',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'stock_id' => 'integer',
        'expiry_date' => 'date',
        'warehouse_location_id' => 'integer',
        'quantity' => 'decimal:4',
        'reserved_qty' => 'decimal:4',
        'available_qty' => 'decimal:4',
        'avg_cost' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}
