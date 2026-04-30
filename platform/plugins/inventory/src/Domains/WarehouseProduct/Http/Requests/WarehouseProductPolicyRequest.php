<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehouseProductPolicyRequest extends Request
{
    public function rules(): array
    {
        return [
            'preset_code' => ['nullable', Rule::in(array_keys(app(\Botble\Inventory\Domains\Warehouse\Support\WarehousePolicyPresets::class)::all()))],
            'tracking_type' => ['required_without:preset_code', Rule::in(['none', 'batch', 'serial'])],
            'is_expirable' => ['nullable', 'boolean'],
            'require_mfg_date' => ['nullable', 'boolean'],
            'require_expiry_date' => ['nullable', 'boolean'],
            'allow_pallet' => ['nullable', 'boolean'],
            'require_pallet' => ['nullable', 'boolean'],
            'require_qc' => ['nullable', 'boolean'],
            'placement_mode' => ['required_without:preset_code', Rule::in(['assigned_on_receipt', 'putaway_after_receipt'])],
            'allow_mixed_batch_on_pallet' => ['nullable', 'boolean'],
            'allow_receive_without_location' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
