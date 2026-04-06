<?php

namespace Botble\Logistics\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class shippingOrderInformation extends BaseModel
{
    protected $table = 'shipping_order_information';

    protected $fillable = [
        'shipping_order_id',

        'from_name',
        'from_phone',
        'from_address',
        'from_ward',
        'from_district',
        'from_province',

        // To
        'to_name',
        'to_phone',
        'to_address',
        'to_ward',
        'to_district',
        'to_province',

        // Shipment
        'cod_amount',
        'weight',
        'length',
        'width',
        'height',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'from_ward' => 'integer',
        'from_district' => 'integer',
        'from_province' => 'integer',

        'to_ward' => 'integer',
        'to_district' => 'integer',
        'to_province' => 'integer',

        'cod_amount' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];
}
