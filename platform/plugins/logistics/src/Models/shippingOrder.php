<?php

namespace Botble\Logistics\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class shippingOrder extends BaseModel
{
    protected $table = 'shipping_orders';

    protected $fillable = [
        'order_id',
        'provider',
        'status',
        'code',
        'total_fee',
        'error',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
}
