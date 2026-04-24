<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Models\SupplierProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WarehouseProduct extends BaseModel
{
    protected $table = 'inv_warehouse_products';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_variation_id',
        'supplier_id',
        'supplier_product_id',
        'is_active',
        'note',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'note' => SafeContent::class,
        'created_by' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class, 'supplier_product_id');
    }

    public function policy(): HasOne
    {
        return $this->hasOne(WarehouseProductPolicy::class, 'warehouse_product_id');
    }
}
