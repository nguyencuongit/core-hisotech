<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Enums\GoodsReceiptStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends BaseModel
{
    protected $table = 'inv_goods_receipts';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'code',
        'supplier_id',
        'warehouse_id',
        'receipt_date',
        'status',
        'reference_code',
        'created_by',
        'approved_by',
        'approved_at',
        'note',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
    ];

    protected $casts = [
        'status' => GoodsReceiptStatusEnum::class,
        'receipt_date' => 'date',
        'approved_at' => 'datetime',
        'note' => SafeContent::class,
        'subtotal' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'created_by' => 'integer',
        'approved_by' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class, 'goods_receipt_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
