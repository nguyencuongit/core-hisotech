<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceipt;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceiptBatch;
use Botble\Inventory\Domains\GoodsReceipt\Models\GoodsReceiptItem;
use Botble\Inventory\Domains\GoodsReceipt\Models\ReceiptStorageItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends BaseModel
{
    public $timestamps = false;

    protected $table = 'inv_stock_transactions';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'transaction_code',
        'type',
        'reference_type',
        'reference_id',
        'reference_item_id',
        'product_id',
        'product_variation_id',
        'warehouse_id',
        'warehouse_location_id',
        'pallet_id',
        'storage_item_id',
        'goods_receipt_id',
        'goods_receipt_item_id',
        'goods_receipt_batch_id',
        'batch_id',
        'quantity',
        'unit_cost',
        'before_qty',
        'after_qty',
        'note',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'before_qty' => 'decimal:4',
        'after_qty' => 'decimal:4',
        'created_at' => 'datetime',
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

    public function storageItem(): BelongsTo
    {
        return $this->belongsTo(ReceiptStorageItem::class, 'storage_item_id');
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class, 'goods_receipt_item_id');
    }

    public function goodsReceiptBatch(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptBatch::class, 'goods_receipt_batch_id');
    }
}
