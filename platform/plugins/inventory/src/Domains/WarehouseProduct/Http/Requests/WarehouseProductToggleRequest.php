<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Validator;

class WarehouseProductToggleRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:inv_warehouses,id'],
            'add_product_ids' => ['nullable', 'array'],
            'add_product_ids.*' => ['required', 'integer', 'distinct', 'exists:ec_products,id'],
            'remove_product_ids' => ['nullable', 'array'],
            'remove_product_ids.*' => ['required', 'integer', 'distinct', 'exists:ec_products,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('product_id')) {
                return;
            }

            $addProductIds = array_filter((array) $this->input('add_product_ids', []));
            $removeProductIds = array_filter((array) $this->input('remove_product_ids', []));

            if ($addProductIds !== [] || $removeProductIds !== []) {
                return;
            }

            $validator->errors()->add(
                'product_id',
                trans('plugins/inventory::inventory.warehouse_product.validation.no_products_selected')
            );
        });
    }
}
