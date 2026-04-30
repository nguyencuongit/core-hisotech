<?php

namespace Botble\Inventory\Domains\Supplier\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SupplierProductSearchRequest extends Request
{
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}
