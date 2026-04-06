<?php

namespace Botble\Logistics\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CreateOrderShippingRequest extends Request
{
    public function rules(): array
    {
        return [
            // Provider
            'provider' => ['required', 'string'],
            'order_id' => ['required', 'integer'],
            // FROM
            'from_name' => ['required', 'string', 'max:255'],
            'from_phone' => ['required', 'string', 'max:20'],
            'from_address' => ['required', 'string', 'max:500'],
            'from_province' => ['required', 'integer'],
            'from_district' => ['required', 'integer'],

            // TO
            'to_name' => ['required', 'string', 'max:255'],
            'to_phone' => ['required', 'string', 'max:20'],
            'to_address' => ['required', 'string', 'max:500'],
            'to_province' => ['required', 'integer'],
            'to_district' => ['required', 'integer'],

            // PACKAGE
            'weight' => ['required', 'integer', 'min:1'],
            'length' => ['required', 'integer', 'min:1'],
            'width' => ['required', 'integer', 'min:1'],
            'height' => ['required', 'integer', 'min:1'],

            //product 
            'products' => ['required', 'array'],
            'products.*.name' => ['required', 'string'],
            'products.*.qty' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['required'],
            'products.*.height' => ['nullable'],
            'products.*.length' => ['nullable'],
            'products.*.width' => ['nullable'],
            'products.*.weight' => ['nullable'],

            'cod_amount' => ['required', 'integer'],
        ];
    }

    /**
     * Custom message lỗi
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute không được để trống',
            'integer' => ':attribute phải là số',
            'min' => ':attribute phải lớn hơn 0',
            'max' => ':attribute không được vượt quá :max ký tự',
        ];
    }

    /**
     * Đổi tên field cho dễ hiểu (UI)
     */
    public function attributes(): array
    {
        return [
            'provider' => 'Đơn vị vận chuyển',

            'from_name' => 'Tên người gửi',
            'from_phone' => 'SĐT người gửi',
            'from_address' => 'Địa chỉ người gửi',
            'from_province' => 'Tỉnh/Thành người gửi',
            'from_district' => 'Quận/Huyện người gửi',

            'to_name' => 'Tên người nhận',
            'to_phone' => 'SĐT người nhận',
            'to_address' => 'Địa chỉ người nhận',
            'to_province' => 'Tỉnh/Thành người nhận',
            'to_district' => 'Quận/Huyện người nhận',

            'weight' => 'Khối lượng',
            'length' => 'Chiều dài',
            'width' => 'Chiều rộng',
            'height' => 'Chiều cao',


            'cod_amount' => 'tiền thu code'
        ];
    }

    /**
     * Auto cast dữ liệu trước khi validate
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'from_province' => (int) $this->from_province,
            'from_district' => (int) $this->from_district,

            'to_province' => (int) $this->to_province,
            'to_district' => (int) $this->to_district,

            'weight' => (int) $this->weight,
            'length' => (int) $this->length,
            'width' => (int) $this->width,
            'height' => (int) $this->height,
        ]);
    }
}