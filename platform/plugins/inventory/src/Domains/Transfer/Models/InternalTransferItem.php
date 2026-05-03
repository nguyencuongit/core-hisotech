<?php

namespace Botble\Inventory\Domains\Transfer\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Botble\Inventory\Domains\Transactions\Models\ImportItem;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\StockBalance;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalTransferItem extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_internal_transfer_items';

    protected $fillable = [
        'transfer_id',
        'export_item_id',
        'import_item_id',
        'stock_balance_id',
        'product_id',
        'product_variation_id',
        'product_code',
        'product_name',
        'requested_qty',
        'exported_qty',
        'received_qty',
        'damaged_qty',
        'shortage_qty',
        'overage_qty',
        'unit_id',
        'unit_name',
        'from_location_id',
        'to_location_id',
        'pallet_id',
        'to_pallet_id',
        'batch_id',
        'goods_receipt_batch_id',
        'lot_no',
        'expiry_date',
        'unit_price',
        'amount',
        'note',
    ];

    protected $casts = [
        'transfer_id' => 'integer',
        'export_item_id' => 'integer',
        'import_item_id' => 'integer',
        'product_id' => 'integer',
        'product_variation_id' => 'integer',
        'unit_id' => 'integer',
        'from_location_id' => 'integer',
        'to_location_id' => 'integer',
        'pallet_id' => 'integer',
        'to_pallet_id' => 'integer',
        'requested_qty' => 'decimal:2',
        'exported_qty' => 'decimal:4',
        'received_qty' => 'decimal:4',
        'damaged_qty' => 'decimal:4',
        'shortage_qty' => 'decimal:4',
        'overage_qty' => 'decimal:4',
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

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function stockBalance(): BelongsTo
    {
        return $this->belongsTo(StockBalance::class, 'stock_balance_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'to_location_id');
    }

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class, 'pallet_id');
    }

    public function toPallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class, 'to_pallet_id');
    }

    public function exportItem(): BelongsTo
    {
        return $this->belongsTo(ExportItem::class, 'export_item_id');
    }

    public function importItem(): BelongsTo
    {
        return $this->belongsTo(ImportItem::class, 'import_item_id');
    }
}
