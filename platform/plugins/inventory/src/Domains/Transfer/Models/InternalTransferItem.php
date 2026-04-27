<?php

namespace Botble\Inventory\Domains\Transfer\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalTransferItem extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_internal_transfer_items';

    protected $fillable = [
        'transfer_id',
        'product_id',
        'product_code',
        'product_name',
        'requested_qty',
        'unit_id',
        'unit_name',
        'from_location_id',
        'to_location_id',
        'lot_no',
        'expiry_date',
        'unit_price',
        'amount',
        'note',
    ];

    protected $casts = [
        'transfer_id' => 'integer',
        'product_id' => 'integer',
        'unit_id' => 'integer',
        'from_location_id' => 'integer',
        'to_location_id' => 'integer',
        'requested_qty' => 'decimal:2',
        'expiry_date' => 'date',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InternalTransfer::class, 'transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'to_location_id');
    }
}
