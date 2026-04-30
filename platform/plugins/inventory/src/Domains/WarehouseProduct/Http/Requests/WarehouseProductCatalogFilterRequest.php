<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehouseProductCatalogFilterRequest extends Request
{
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['all', 'in_warehouse', 'without_warehouse'])],
            'warehouse_id' => ['nullable', 'integer', 'exists:inv_warehouses,id'],
        ];
    }
}
