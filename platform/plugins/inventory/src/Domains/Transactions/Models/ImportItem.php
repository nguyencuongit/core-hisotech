<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportItem extends BaseModel
{
    protected $table = 'inv_import_items';

    protected $fillable = [
        'import_id',
        'product_id',
        'product_variation_id',
        'supplier_product_id',

        'product_name',
        'product_code',

        'document_qty',
        'received_qty',

        'unit_id',
        'unit_name',

        'warehouse_location_id',
        'pallet_id',
        'batch_id',
        'goods_receipt_batch_id',

        'amount',
        'unit_cost',
        'total_cost',
        'qc_status',
        'accepted_qty',
        'rejected_qty',

        'lot_no',
        'expiry_date',

        'note',
    ];

    protected $casts = [
        'import_id' => 'integer',
        'product_id' => 'integer',
        'product_variation_id' => 'integer',
        'document_qty' => 'decimal:4',
        'received_qty' => 'decimal:4',
        'unit_id' => 'integer',
        'warehouse_location_id' => 'integer',
        'pallet_id' => 'integer',
        'amount' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'accepted_qty' => 'decimal:4',
        'rejected_qty' => 'decimal:4',
        'expiry_date' => 'date',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class, 'import_id');
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
}
