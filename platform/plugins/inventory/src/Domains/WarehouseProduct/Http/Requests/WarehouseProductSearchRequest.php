<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WarehouseProductSearchRequest extends Request
{
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}
