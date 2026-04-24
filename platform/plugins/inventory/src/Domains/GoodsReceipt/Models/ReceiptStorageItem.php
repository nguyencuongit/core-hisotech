<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptStorageItem extends BaseModel
{
    protected $table = 'inv_receipt_storage_items';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'goods_receipt_id',
        'goods_receipt_item_id',
        'goods_receipt_batch_id',
        'warehouse_id',
        'warehouse_location_id',
        'pallet_id',
        'product_id',
        'product_variation_id',
        'tracking_type',
        'status',
        'received_qty',
        'available_qty',
        'qc_hold_qty',
        'damaged_qty',
        'rejected_qty',
        'received_at',
        'qc_at',
        'putaway_at',
        'stored_at',
        'closed_at',
        'posted_at',
        'posted_by',
        'note',
        'meta_json',
        'created_by',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'qc_at' => 'datetime',
        'putaway_at' => 'datetime',
        'stored_at' => 'datetime',
        'closed_at' => 'datetime',
        'posted_at' => 'datetime',
        'note' => SafeContent::class,
        'meta_json' => 'array',
        'received_qty' => 'decimal:4',
        'available_qty' => 'decimal:4',
        'qc_hold_qty' => 'decimal:4',
        'damaged_qty' => 'decimal:4',
        'rejected_qty' => 'decimal:4',
        'posted_by' => 'integer',
        'created_by' => 'integer',
    ];

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
