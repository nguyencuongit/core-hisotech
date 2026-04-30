<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\StockBalance;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportItem extends BaseModel
{
    protected $table = 'inv_export_items';

    protected $fillable = [
        'export_id',
        'product_id',
        'product_variation_id',

        'product_name',
        'product_code',

        'document_qty',
        'reserved_qty',
        'picked_qty',
        'packed_qty',
        'shipped_qty',
        'cancelled_qty',

        'unit_id',
        'unit_name',

        'warehouse_location_id',
        'pallet_id',
        'batch_id',
        'goods_receipt_batch_id',
        'stock_balance_id',

        'lot_no',
        'expiry_date',

        'amount',
        'unit_price',

        'note',
    ];

    protected $casts = [
        'export_id' => 'integer',
        'product_id' => 'integer',
        'product_variation_id' => 'integer',
        'document_qty' => 'decimal:4',
        'reserved_qty' => 'decimal:4',
        'picked_qty' => 'decimal:4',
        'packed_qty' => 'decimal:4',
        'shipped_qty' => 'decimal:4',
        'cancelled_qty' => 'decimal:4',
        'unit_id' => 'integer',
        'warehouse_location_id' => 'integer',
        'pallet_id' => 'integer',
        'expiry_date' => 'date',
        'amount' => 'decimal:4',
        'unit_price' => 'decimal:4',
    ];

    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class, 'export_id');
    }

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

    public function stockBalance(): BelongsTo
    {
        return $this->belongsTo(StockBalance::class, 'stock_balance_id');
    }
}
