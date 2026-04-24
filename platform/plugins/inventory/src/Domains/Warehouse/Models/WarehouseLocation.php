<?php

namespace Botble\Inventory\Domains\Warehouse\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLocation extends BaseModel
{
    protected $table = 'inv_warehouse_locations';

    protected $fillable = [
        'warehouse_id',
        'parent_id',
        'code',
        'name',
        'type',
        'level',
        'path',
        'status',
        'description',
    ];

    protected $casts = [
        'level' => 'integer',
        'status' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isSystemLocation(): bool
    {
        return in_array($this->type, ['receiving', 'waiting_putaway', 'qc_hold', 'damaged', 'rejected', 'return_area', 'dispatch'], true);
    }

    public function displayLabel(): string
    {
        return trim($this->code . ' - ' . $this->name);
    }
}
