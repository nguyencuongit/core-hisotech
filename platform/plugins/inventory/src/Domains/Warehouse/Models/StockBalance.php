<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceiptBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends BaseModel
{
    public $timestamps = false;

    protected $table = 'inv_stock_balances';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'dimension_key',
        'product_id',
        'product_variation_id',
        'warehouse_id',
        'warehouse_location_id',
        'pallet_id',
        'batch_id',
        'goods_receipt_batch_id',
        'quantity',
        'reserved_qty',
        'available_qty',
        'qc_hold_qty',
        'damaged_qty',
        'rejected_qty',
        'average_cost',
        'last_unit_cost',
        'last_movement_at',
        'updated_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'product_variation_id' => 'integer',
        'warehouse_id' => 'integer',
        'warehouse_location_id' => 'integer',
        'pallet_id' => 'integer',
        'quantity' => 'decimal:4',
        'reserved_qty' => 'decimal:4',
        'available_qty' => 'decimal:4',
        'qc_hold_qty' => 'decimal:4',
        'damaged_qty' => 'decimal:4',
        'rejected_qty' => 'decimal:4',
        'average_cost' => 'decimal:4',
        'last_unit_cost' => 'decimal:4',
        'last_movement_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class, 'pallet_id');
    }

    public function goodsReceiptBatch(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptBatch::class, 'goods_receipt_batch_id');
    }
}
