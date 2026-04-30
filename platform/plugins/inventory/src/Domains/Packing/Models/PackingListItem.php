<?php

namespace Botble\Inventory\Domains\Packing\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\StockBalance;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingListItem extends BaseModel
{
    protected $table = 'inv_packing_list_items';

    protected $fillable = [
        'packing_list_id',
        'packing_id',
        'package_id',
        'export_item_id',
        'product_id',
        'product_variation_id',
        'product_code',
        'product_name',
        'packed_qty',
        'unit_id',
        'unit_name',
        'warehouse_location_id',
        'pallet_id',
        'batch_id',
        'goods_receipt_batch_id',
        'stock_balance_id',
        'storage_item_id',
        'lot_no',
        'expiry_date',
        'note',
    ];

    protected $casts = [
        'packing_list_id' => 'integer',
        'packing_id' => 'integer',
        'package_id' => 'integer',
        'export_item_id' => 'integer',
        'product_id' => 'integer',
        'product_variation_id' => 'integer',
        'packed_qty' => 'decimal:4',
        'unit_id' => 'integer',
        'warehouse_location_id' => 'integer',
        'pallet_id' => 'integer',
        'expiry_date' => 'date',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function legacyPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'packing_id');
    }

    public function exportItem(): BelongsTo
    {
        return $this->belongsTo(ExportItem::class, 'export_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
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
