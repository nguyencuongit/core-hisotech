<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Http\Requests;

use Botble\Inventory\Enums\GoodsReceiptStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class GoodsReceiptRequest extends Request
{
    public function rules(): array
    {
        $goodsReceiptId = $this->route('goodsReceipt')?->getKey();

        return [
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inv_goods_receipts', 'code')->ignore($goodsReceiptId),
            ],
            'supplier_id' => ['required', 'uuid', 'exists:inv_suppliers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:inv_warehouses,id'],
            'receipt_date' => ['required', 'date'],
            'status' => ['required', Rule::in(GoodsReceiptStatusEnum::values())],
            'reference_code' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:ec_products,id'],
            'items.*.product_variation_id' => ['nullable', 'integer', 'exists:ec_product_variations,id'],
            'items.*.supplier_product_id' => ['nullable', 'uuid', 'exists:inv_supplier_products,id'],
            'items.*.product_name' => ['nullable', 'string', 'max:191'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.barcode' => ['nullable', 'string', 'max:100'],
            'items.*.ordered_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.received_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.rejected_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.uom' => ['nullable', 'string', 'max:50'],
            'items.*.note' => ['nullable', 'string'],
        ];
    }
}
