<?php

namespace Botble\Inventory\Domains\Transactions\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

class ImportItem extends BaseModel
{
    protected $table = 'inv_import_items';

    protected $fillable = [
        'import_id',
        'product_id',

        'product_name',
        'product_code',

        'document_qty',
        'received_qty',

        'unit_id',
        'unit_name',

        'warehouse_location_id',

        'amount',

        'lot_no',
        'expiry_date',

        'note',
    ];

    protected $casts = [

    ];

    public function warehouse()
{
    return $this->belongsTo(Warehouse::class, 'warehouse_id');
}
}
