<?php
namespace Botble\ViettelPost\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ViettelPostSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'viettel_post_status'               => 'nullable',
            'viettel_post_username'             => 'nullable|string|max:255',
            'viettel_post_password'             => 'nullable|string|max:255',
            'viettel_post_partner_code'         => 'nullable|string|max:50',
            'viettel_post_api_key'              => 'nullable|string|max:255',
            'viettel_post_shop_id'              => 'nullable|string|max:50',
            'viettel_post_customer_id'          => 'nullable|string|max:50',
            'viettel_post_default_service'      => 'nullable|string|max:20',
            'viettel_post_sender_name'          => 'nullable|string|max:255',
            'viettel_post_sender_phone'         => 'nullable|string|max:20',
            'viettel_post_sender_address'       => 'nullable|string|max:255',
            'viettel_post_sender_province_id'   => 'nullable|numeric',
            'viettel_post_sender_district_id'   => 'nullable|numeric',
            'viettel_post_sender_ward_id'       => 'nullable|numeric',
            'viettel_post_auto_create_shipment' => 'nullable',
        ];
    }
}
