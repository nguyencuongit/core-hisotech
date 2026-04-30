<?php

namespace Botble\Inventory\Domains\Supplier\Http\Requests;

use Botble\Inventory\Enums\SupplierAddressTypeEnum;
use Botble\Inventory\Enums\SupplierStatusEnum;
use Botble\Inventory\Enums\SupplierTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function rules(): array
    {
        $supplier = $this->route('supplier');
        $supplierId = is_object($supplier) && method_exists($supplier, 'getKey')
            ? $supplier->getKey()
            : $supplier;

        return [
            'code' => ['nullable', 'string', 'max:50', Rule::unique('inv_suppliers', 'code')->ignore($supplierId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(SupplierTypeEnum::values())],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'note' => ['nullable', 'string'],
            'status' => ['required', Rule::in(SupplierStatusEnum::values())],
            'metadata' => ['nullable', 'array'],

            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['required_with:contacts.*', 'nullable', 'string', 'max:255'],
            'contacts.*.position' => ['nullable', 'string', 'max:100'],
            'contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.identity_number' => ['nullable', 'string', 'max:50'],
            'contacts.*.is_primary' => ['nullable', 'boolean'],
            'contacts.*.social_contact' => ['nullable', 'array'],

            'addresses' => ['nullable', 'array'],
            'addresses.*.type' => ['required_with:addresses.*', Rule::in(SupplierAddressTypeEnum::values())],
            'addresses.*.is_default' => ['nullable', 'boolean'],
            'addresses.*.address' => ['required_with:addresses.*', 'nullable', 'string', 'max:255'],
            'addresses.*.ward_id' => ['nullable', 'integer'],
            'addresses.*.district_id' => ['nullable', 'integer'],
            'addresses.*.province_id' => ['nullable', 'integer'],
            'addresses.*.country_id' => ['nullable', 'integer'],

            'banks' => ['nullable', 'array'],
            'banks.*.bank_name' => ['required_with:banks.*', 'nullable', 'string', 'max:255'],
            'banks.*.branch' => ['nullable', 'string', 'max:255'],
            'banks.*.account_number' => ['required_with:banks.*', 'nullable', 'string', 'max:100'],
            'banks.*.account_name' => ['required_with:banks.*', 'nullable', 'string', 'max:255'],
            'banks.*.is_default' => ['nullable', 'boolean'],

            'supplier_products' => ['nullable', 'array'],
            'supplier_products.*.product_id' => ['required_with:supplier_products.*', 'nullable', 'integer', 'exists:ec_products,id'],
            'supplier_products.*.supplier_sku' => ['nullable', 'string', 'max:100'],
            'supplier_products.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'supplier_products.*.moq' => ['nullable', 'integer', 'min:0'],
            'supplier_products.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
