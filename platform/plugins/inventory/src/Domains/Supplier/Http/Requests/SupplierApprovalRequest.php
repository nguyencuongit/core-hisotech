<?php

namespace Botble\Inventory\Domains\Supplier\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SupplierApprovalRequest extends Request
{
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
