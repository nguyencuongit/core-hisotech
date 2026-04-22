<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehouseProductRequest extends Request
{
    public function rules(): array
    {
        $isUpdate = $this->route('warehouseProduct') !== null;

        return [
            'product_id' => [$isUpdate ? 'nullable' : 'required', 'integer', 'exists:ec_products,id'],
            'product_variation_id' => ['nullable', 'integer', 'exists:ec_product_variations,id'],
            'default_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'supplier_id' => ['nullable', 'uuid', 'exists:inv_suppliers,id'],
            'supplier_product_id' => ['nullable', 'uuid', 'exists:inv_supplier_products,id'],
            'is_active' => ['nullable', Rule::in(['0', '1', 0, 1, true, false])],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
