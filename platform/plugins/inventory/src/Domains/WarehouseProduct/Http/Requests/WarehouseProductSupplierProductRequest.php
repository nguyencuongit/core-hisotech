<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WarehouseProductSupplierProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'uuid', 'exists:inv_suppliers,id'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
        ];
    }
}
