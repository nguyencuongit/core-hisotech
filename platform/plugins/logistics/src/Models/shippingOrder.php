<?php

namespace Botble\Logistics\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Logistics\Enums\ShippingStatus;


class shippingOrder extends BaseModel
{
    protected $table = 'shipping_orders';

    protected $fillable = [
        'order_id',
        'provider',
        'status',
        'code',
        'total_fee',
        'status_name',
        'localion_currenty',
    ];
    protected $casts = [
        'status' => ShippingStatus::class,
    ];
}
