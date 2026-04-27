<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehousePositionRequest extends Request
{
    public function rules(): array
    {

        $id = $this->route('warehousePosition') 
        ? $this->route('warehousePosition') 
        : null;

        return [
            'name' => ['required', 'string', 'max:220'],
            'code' => [
                'required',
                'string',
                'max:220',
                Rule::unique('inv_warehouse_positions', 'code')->ignore($id),
            ],
            'level' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
        ];
    }
}
