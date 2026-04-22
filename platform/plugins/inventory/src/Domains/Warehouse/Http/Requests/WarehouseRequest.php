<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehouseRequest extends Request
{
    public function rules(): array
    {

        $id = $this->route('warehouse') 
        ? $this->route('warehouse') 
        : null;

        return [
            'name' => ['required', 'string', 'max:220'],
            'type' => ['nullable','string', 'max:220'],
            'address' => ['required', 'string', 'max:220'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:220'],
            'description' => ['nullable', 'string'],
            'code' => [
                'required',
                'string',
                'max:220',
                Rule::unique('inv_warehouses', 'code')->ignore($id),
            ],
            'status' => ['required', 'in:0,1'],
            
        ];
    }
}
