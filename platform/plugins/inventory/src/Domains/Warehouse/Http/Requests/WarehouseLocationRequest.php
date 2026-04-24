<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehouseLocationRequest extends Request
{
    public function rules(): array
    {
        $warehouseId = (int) $this->route('warehouse')?->getKey();
        $locationId = $this->route('warehouseLocation')?->getKey();
        $allowedTypes = [
            'system',
            'floor',
            'zone',
            'rack',
            'level',
            'bin',
            'receiving',
            'waiting_putaway',
            'qc_hold',
            'damaged',
            'rejected',
            'return_area',
            'dispatch',
        ];

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('inv_warehouse_locations', 'id')->where(fn ($query) => $query->where('warehouse_id', $warehouseId)),
                Rule::notIn([$locationId]),
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inv_warehouse_locations', 'code')->where(fn ($query) => $query->where('warehouse_id', $warehouseId))->ignore($locationId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($allowedTypes)],
            'status' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
