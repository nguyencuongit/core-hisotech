<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WarehouseSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'warehouse_mode' => ['required', 'in:simple,advanced'],
            'use_pallet' => ['nullable', 'boolean'],
            'require_pallet' => ['nullable', 'boolean'],
            'use_qc' => ['nullable', 'boolean'],
            'use_batch' => ['nullable', 'boolean'],
            'use_serial' => ['nullable', 'boolean'],
            'use_map' => ['nullable', 'boolean'],
            'default_receiving_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'default_waiting_putaway_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'default_qc_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'default_damaged_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'default_rejected_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
        ];
    }
}
