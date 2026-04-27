<?php

namespace Botble\Inventory\Domains\Packing\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingListItem extends BaseModel
{
    protected $table = 'inv_packing_list_items';

    protected $fillable = [
        'packing_list_id',
        'packing_id',
        'product_id',
        'product_name',
        'packed_qty',
        'unit_id',
        'unit_name',
        'warehouse_location_id',
        'note',
    ];

    protected $casts = [
        'packing_list_id' => 'integer',
        'packing_id' => 'integer',
        'product_id' => 'integer',
        'packed_qty' => 'decimal:2',
        'unit_id' => 'integer',
        'warehouse_location_id' => 'integer',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'packing_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}
