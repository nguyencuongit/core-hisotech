<?php

namespace Botble\Inventory\Domains\Packing\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingLog extends BaseModel
{
    public $timestamps = false;

    protected $table = 'inv_packing_logs';

    protected $fillable = [
        'packing_list_id',
        'package_id',
        'export_id',
        'export_item_id',
        'product_id',
        'product_variation_id',
        'action',
        'old_qty',
        'new_qty',
        'note',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'packing_list_id' => 'integer',
        'package_id' => 'integer',
        'export_id' => 'integer',
        'export_item_id' => 'integer',
        'product_id' => 'integer',
        'product_variation_id' => 'integer',
        'old_qty' => 'decimal:4',
        'new_qty' => 'decimal:4',
        'created_by' => 'integer',
        'created_at' => 'datetime',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class, 'export_id');
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
