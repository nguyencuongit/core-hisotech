<?php

namespace Botble\Inventory\Domains\Transfer\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalTransferLog extends BaseModel
{
    public $timestamps = false;

    protected $table = 'inv_internal_transfer_logs';

    protected $fillable = [
        'transfer_id',
        'action',
        'old_status',
        'new_status',
        'note',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'transfer_id' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'datetime',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InternalTransfer::class, 'transfer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
