<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehouseStaffRequest extends Request
{
    public function rules(): array
    {
        $id = $this->route('warehouseStaff') 
        ? $this->route('warehouseStaff')->id 
        : null;

        return [
            'full_name' => ['required', 'string', 'max:220'],
            'phone' => ['required', 'string', 'max:220'],
            'email' => ['required', 'string', 'max:220'],
            'staff_code' => [
                'required',
                'string',
                'max:220',
                Rule::unique('inv_warehouse_staff', 'staff_code')->ignore($id),
            ],
            'warehouse_id' => ['required', 'array'],
            
        ];
    }
}
