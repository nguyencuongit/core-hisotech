<?php

namespace Botble\Inventory\Domains\Transfer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        $transfer = $this->route('transfer');
        $transferId = is_object($transfer) && method_exists($transfer, 'getKey')
            ? $transfer->getKey()
            : $transfer;

        return [
            'workflow_action' => ['nullable', Rule::in(['save', 'save_draft', 'confirm', 'export', 'complete', 'cancel'])],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('inv_internal_transfers', 'code')->ignore($transferId)],
            'status' => ['nullable', Rule::in(['draft', 'confirmed', 'exporting', 'importing', 'completed', 'cancelled'])],
            'from_warehouse_id' => ['required', 'integer', 'exists:inv_warehouses,id'],
            'to_warehouse_id' => ['required', 'integer', 'exists:inv_warehouses,id', 'different:from_warehouse_id'],
            'requested_by' => ['nullable', 'integer'],
            'approved_by' => ['nullable', 'integer'],
            'exported_by' => ['nullable', 'integer'],
            'imported_by' => ['nullable', 'integer'],
            'transfer_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],

            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:inv_internal_transfer_items,id'],
            'items.*.stock_balance_id' => ['nullable', 'string', 'max:36', 'exists:inv_stock_balances,id'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'items.*.product_variation_id' => ['nullable', 'integer'],
            'items.*.product_code' => ['nullable', 'string', 'max:120'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.requested_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.exported_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.received_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.damaged_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.shortage_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.overage_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_id' => ['nullable', 'integer'],
            'items.*.unit_name' => ['nullable', 'string', 'max:120'],
            'items.*.from_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'items.*.to_location_id' => ['nullable', 'integer', 'exists:inv_warehouse_locations,id'],
            'items.*.pallet_id' => ['nullable', 'integer', 'exists:inv_pallets,id'],
            'items.*.to_pallet_id' => ['nullable', 'integer', 'exists:inv_pallets,id'],
            'items.*.batch_id' => ['nullable', 'string', 'max:36'],
            'items.*.goods_receipt_batch_id' => ['nullable', 'string', 'max:36'],
            'items.*.lot_no' => ['nullable', 'string', 'max:120'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
