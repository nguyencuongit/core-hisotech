<?php

namespace Botble\Logistics\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class shippingDistrictMapping extends BaseModel
{
    protected $table = 'shipping_district_mappings';

    protected $fillable = [
        'name',
        'state_id',
        'provider',
        'province_id',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];
}
