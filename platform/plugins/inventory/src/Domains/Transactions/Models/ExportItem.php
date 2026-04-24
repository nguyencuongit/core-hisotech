<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

class ExportItem extends BaseModel
{
    protected $table = 'inv_export_items';

    protected $fillable = [
        'export_id',
        'product_id',

        'product_name',
        'product_code',

        'document_qty',
        'shipped_qty',

        'unit_id',
        'unit_name',

        'warehouse_location_id',

        'lot_no',
        'expiry_date',

        'amount',
        'unit_price',

        'note',
    ];

    protected $casts = [

    ];

    public function warehouse()
{
    return $this->belongsTo(Warehouse::class, 'warehouse_id');
}
}
