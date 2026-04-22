<?php

namespace Botble\Inventory\Domains\Supplier\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Enums\SupplierStatusEnum;
use Botble\Inventory\Enums\SupplierTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'inv_suppliers';

    public static function getTypeOfId(): string
    {
        return 'UUID';
    }

    protected $fillable = [
        'code',
        'name',
        'type',
        'tax_code',
        'website',
        'note',
        'status',
        'metadata',
        'created_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approval_note',
        'requires_reapproval',
    ];

    protected $casts = [
        'metadata' => 'array',
        'status' => SupplierStatusEnum::class,
        'type' => SupplierTypeEnum::class,
        'name' => SafeContent::class,
        'note' => SafeContent::class,
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_by' => 'integer',
        'submitted_by' => 'integer',
        'approved_by' => 'integer',
        'requires_reapproval' => 'bool',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(SupplierAddress::class, 'supplier_id');
    }

    public function banks(): HasMany
    {
        return $this->hasMany(SupplierBank::class, 'supplier_id');
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'supplier_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'inv_supplier_products', 'supplier_id', 'product_id')
            ->withPivot(['id', 'supplier_sku', 'purchase_price', 'moq', 'lead_time_days'])
            ->withTimestamps();
    }

    public function primaryContact(): HasMany
    {
        return $this->contacts()->where('is_primary', true);
    }

    public function defaultAddress(): HasMany
    {
        return $this->addresses()->where('is_default', true);
    }

    public function defaultBank(): HasMany
    {
        return $this->banks()->where('is_default', true);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SupplierApproval::class, 'supplier_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
