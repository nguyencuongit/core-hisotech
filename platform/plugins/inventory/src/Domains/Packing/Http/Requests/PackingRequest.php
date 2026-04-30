<?php

namespace Botble\Inventory\Domains\Packing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackingRequest extends FormRequest
{
    public function rules(): array
    {
        $packing = $this->route('packing');
        $packingId = is_object($packing) && method_exists($packing, 'getKey')
            ? $packing->getKey()
            : $packing;

        return [
            'code' => ['required', 'string', 'max:191', Rule::unique('inv_packing_lists', 'code')->ignore($packingId)],
            'export_id' => ['required', 'integer', 'exists:inv_exports,id'],
            'warehouse_id' => ['required', 'integer', 'exists:inv_warehouses,id'],
            'status' => ['required', Rule::in(['draft', 'packing', 'packed', 'cancelled'])],
            'packer_id' => ['nullable', 'integer'],
            'packed_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'cancelled_at' => ['nullable', 'date'],
            'cancelled_by' => ['nullable', 'integer', 'exists:users,id'],
            'cancelled_reason' => ['nullable', 'string', 'max:255'],
            'total_packages' => ['nullable', 'integer', 'min:0'],
            'total_items' => ['nullable', 'numeric', 'min:0'],
            'total_weight' => ['nullable', 'numeric', 'min:0'],
            'total_volume' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],

            'packages' => ['nullable', 'array'],
            'packages.*.id' => ['nullable', 'integer', 'exists:inv_packages,id'],
            'packages.*.package_code' => ['nullable', 'string', 'max:120'],
            'packages.*.package_no' => ['nullable', 'integer', 'min:1'],
            'packages.*.package_type_id' => ['nullable', 'string', 'max:50'],
            'packages.*.package_type' => ['nullable', 'string', 'max:50'],
            'packages.*.status' => ['nullable', Rule::in(['open', 'closed', 'cancelled'])],
            'packages.*.length' => ['nullable', 'numeric', 'min:0'],
            'packages.*.width' => ['nullable', 'numeric', 'min:0'],
            'packages.*.height' => ['nullable', 'numeric', 'min:0'],
            'packages.*.dimension_unit' => ['nullable', 'string', 'max:20'],
            'packages.*.volume' => ['nullable', 'numeric', 'min:0'],
            'packages.*.volume_weight' => ['nullable', 'numeric', 'min:0'],
            'packages.*.weight' => ['nullable', 'numeric', 'min:0'],
            'packages.*.weight_unit' => ['nullable', 'string', 'max:20'],
            'packages.*.tracking_code' => ['nullable', 'string', 'max:191'],
            'packages.*.shipping_label_url' => ['nullable', 'string', 'max:500'],
            'packages.*.note' => ['nullable', 'string'],

            'packages.*.items' => ['nullable', 'array'],
            'packages.*.items.*.id' => ['nullable', 'integer', 'exists:inv_packing_list_items,id'],
            'packages.*.items.*.export_item_id' => ['nullable', 'integer', 'exists:inv_export_items,id'],
            'packages.*.items.*.product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'packages.*.items.*.product_variation_id' => ['nullable', 'integer'],
            'packages.*.items.*.product_code' => ['nullable', 'string', 'max:191'],
            'packages.*.items.*.product_name' => ['nullable', 'string', 'max:191'],
            'packages.*.items.*.packed_qty' => ['nullable', 'numeric', 'min:0'],
            'packages.*.items.*.unit_id' => ['nullable', 'integer'],
            'packages.*.items.*.unit_name' => ['nullable', 'string', 'max:191'],
            'packages.*.items.*.warehouse_location_id' => ['nullable', 'integer'],
            'packages.*.items.*.pallet_id' => ['nullable', 'integer'],
            'packages.*.items.*.batch_id' => ['nullable', 'string', 'max:36'],
            'packages.*.items.*.goods_receipt_batch_id' => ['nullable', 'string', 'max:36'],
            'packages.*.items.*.stock_balance_id' => ['nullable', 'string', 'max:36'],
            'packages.*.items.*.storage_item_id' => ['nullable', 'string', 'max:36'],
            'packages.*.items.*.lot_no' => ['nullable', 'string', 'max:191'],
            'packages.*.items.*.expiry_date' => ['nullable', 'date'],
            'packages.*.items.*.note' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
