<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Supplier\Models\SupplierProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptItem extends BaseModel
{
    protected $table = 'inv_goods_receipt_items';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'goods_receipt_id',
        'product_id',
        'product_variation_id',
        'supplier_product_id',
        'product_name',
        'sku',
        'barcode',
        'ordered_qty',
        'received_qty',
        'rejected_qty',
        'unit_cost',
        'line_total',
        'uom',
        'note',
    ];

    protected $casts = [
        'product_name' => SafeContent::class,
        'note' => SafeContent::class,
        'ordered_qty' => 'decimal:4',
        'received_qty' => 'decimal:4',
        'rejected_qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'line_total' => 'decimal:4',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class, 'supplier_product_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(GoodsReceiptBatch::class, 'goods_receipt_item_id');
    }
}
