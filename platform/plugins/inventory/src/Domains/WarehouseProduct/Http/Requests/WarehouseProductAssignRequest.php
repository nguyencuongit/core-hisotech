<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WarehouseProductAssignRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:ec_products,id'],
            'warehouse_ids' => ['required', 'array', 'min:1'],
            'warehouse_ids.*' => ['required', 'integer', 'distinct', 'exists:inv_warehouses,id'],
        ];
    }
}
