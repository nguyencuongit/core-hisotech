<?php

namespace Botble\Logistics\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class shippingProvinceMapping extends BaseModel
{
    protected $table = 'shipping_province_mappings';

    protected $fillable = [
        'state_id',
        'provider',
        'province_id',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

}
