<?php

namespace Botble\Logistics\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class shippingProvider extends BaseModel
{
    protected $table = 'shipping_providers';

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'information',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'information' => 'array',
    ];
}
