<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Models;

use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptBatch extends BaseModel
{
    protected $table = 'inv_goods_receipt_batches';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'goods_receipt_item_id',
        'batch_no',
        'serial_no',
        'manufactured_at',
        'expired_at',
        'received_qty',
        'unit_cost',
        'warehouse_location_id',
        'status',
    ];

    protected $casts = [
        'manufactured_at' => 'datetime',
        'expired_at' => 'datetime',
        'received_qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class, 'goods_receipt_item_id');
    }

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}
