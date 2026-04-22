@php
    $goodsReceipt = $goodsReceipt ?? null;
    $oldItems = old('items', $goodsReceipt?->items?->toArray() ?? [[]]);
    $selectedStatus = old('status', $goodsReceipt?->status?->value ?? \Botble\Inventory\Enums\GoodsReceiptStatusEnum::DRAFT->value);
@endphp

<style>
    .goods-receipt-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; }
    .goods-receipt-items table { min-width: 1120px; }
    .goods-receipt-items .product-cell { min-width: 280px; }
    .goods-receipt-items .number-cell { width: 120px; }
    .goods-receipt-summary { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
    .goods-receipt-summary .amount { font-size: 20px; font-weight: 700; }
</style>

<div class="goods-receipt-card p-3 mb-3">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.code') }}</label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $goodsReceipt->code ?? '') }}" placeholder="{{ trans('plugins/inventory::inventory.goods_receipt.code_placeholder') }}">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-5">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.supplier') }} <span class="text-danger">*</span></label>
            <select name="supplier_id" id="goods-receipt-supplier" class="form-select @error('supplier_id') is-invalid @enderror">
                <option value="">{{ trans('plugins/inventory::inventory.goods_receipt.select_supplier') }}</option>
                @foreach($suppliers as $id => $label)
                    <option value="{{ $id }}" @selected(old('supplier_id', $goodsReceipt->supplier_id ?? '') == $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.warehouse') }} <span class="text-danger">*</span></label>
            <select name="warehouse_id" id="goods-receipt-warehouse" class="form-select @error('warehouse_id') is-invalid @enderror">
                <option value="">{{ trans('plugins/inventory::inventory.goods_receipt.select_warehouse') }}</option>
                @foreach($warehouses as $id => $label)
                    <option value="{{ $id }}" @selected(old('warehouse_id', $goodsReceipt->warehouse_id ?? '') == $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.receipt_date') }} <span class="text-danger">*</span></label>
            <input type="date" name="receipt_date" class="form-control @error('receipt_date') is-invalid @enderror" value="{{ old('receipt_date', $goodsReceipt?->receipt_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
            @error('receipt_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.status.label') }}</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.reference_code') }}</label>
            <input type="text" name="reference_code" class="form-control @error('reference_code') is-invalid @enderror" value="{{ old('reference_code', $goodsReceipt->reference_code ?? '') }}">
            @error('reference_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.discount_amount') }}</label>
            <input type="number" min="0" step="0.0001" name="discount_amount" id="goods-receipt-discount" class="form-control @error('discount_amount') is-invalid @enderror" value="{{ old('discount_amount', $goodsReceipt->discount_amount ?? 0) }}">
            @error('discount_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.tax_amount') }}</label>
            <input type="number" min="0" step="0.0001" name="tax_amount" id="goods-receipt-tax" class="form-control @error('tax_amount') is-invalid @enderror" value="{{ old('tax_amount', $goodsReceipt->tax_amount ?? 0) }}">
            @error('tax_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">{{ trans('plugins/inventory::inventory.goods_receipt.note') }}</label>
            <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3">{{ old('note', $goodsReceipt->note ?? '') }}</textarea>
            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="goods-receipt-card goods-receipt-items p-3">
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <div>
            <h4 class="mb-1">{{ trans('plugins/inventory::inventory.goods_receipt.items') }}</h4>
            <div class="text-muted small">{{ trans('plugins/inventory::inventory.goods_receipt.items_help') }}</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" id="goods-receipt-load-supplier-products">
                {{ trans('plugins/inventory::inventory.goods_receipt.load_supplier_products') }}
            </button>
            <button type="button" class="btn btn-primary" id="goods-receipt-add-item">
                {{ trans('plugins/inventory::inventory.goods_receipt.add_product') }}
            </button>
        </div>
    </div>

    @error('items')<div class="alert alert-danger">{{ $message }}</div>@enderror

    <div class="table-responsive">
        <table class="table table-vcenter">
            <thead>
                <tr>
                    <th class="product-cell">{{ trans('plugins/inventory::inventory.goods_receipt.product') }}</th>
                    <th>{{ trans('plugins/inventory::inventory.goods_receipt.sku') }}</th>
                    <th>{{ trans('plugins/inventory::inventory.goods_receipt.barcode') }}</th>
                    <th class="number-cell">{{ trans('plugins/inventory::inventory.goods_receipt.ordered_qty') }}</th>
                    <th class="number-cell">{{ trans('plugins/inventory::inventory.goods_receipt.received_qty') }}</th>
                    <th class="number-cell">{{ trans('plugins/inventory::inventory.goods_receipt.rejected_qty') }}</th>
                    <th class="number-cell">{{ trans('plugins/inventory::inventory.goods_receipt.unit_cost') }}</th>
                    <th>{{ trans('plugins/inventory::inventory.goods_receipt.uom') }}</th>
                    <th class="number-cell">{{ trans('plugins/inventory::inventory.goods_receipt.line_total') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="goods-receipt-items-body"></tbody>
        </table>
    </div>

    <div class="row justify-content-end mt-3">
        <div class="col-md-4">
            <div class="goods-receipt-summary">
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ trans('plugins/inventory::inventory.goods_receipt.subtotal') }}</span>
                    <strong id="goods-receipt-subtotal">0</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ trans('plugins/inventory::inventory.goods_receipt.discount_amount') }}</span>
                    <strong id="goods-receipt-discount-display">0</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ trans('plugins/inventory::inventory.goods_receipt.tax_amount') }}</span>
                    <strong id="goods-receipt-tax-display">0</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span>{{ trans('plugins/inventory::inventory.goods_receipt.total_amount') }}</span>
                    <span class="amount" id="goods-receipt-total">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route('inventory.goods-receipts.index') }}" class="btn btn-outline-secondary">{{ trans('core/base::forms.cancel') }}</a>
    <button type="submit" class="btn btn-primary">{{ trans('core/base::forms.save') }}</button>
</div>

<template id="goods-receipt-item-template">
    <tr data-row>
        <td class="product-cell">
            <select class="form-select goods-receipt-product-select" data-field="product_id"></select>
            <input type="hidden" data-field="product_variation_id">
            <input type="hidden" data-field="supplier_product_id">
            <input type="hidden" data-field="product_name">
        </td>
        <td><input type="text" class="form-control" data-field="sku"></td>
        <td><input type="text" class="form-control" data-field="barcode"></td>
        <td><input type="number" min="0" step="0.0001" class="form-control" data-field="ordered_qty"></td>
        <td><input type="number" min="0" step="0.0001" class="form-control" data-field="received_qty"></td>
        <td><input type="number" min="0" step="0.0001" class="form-control" data-field="rejected_qty"></td>
        <td><input type="number" min="0" step="0.0001" class="form-control" data-field="unit_cost"></td>
        <td><input type="text" class="form-control" data-field="uom"></td>
        <td><input type="text" class="form-control" data-field="line_total" readonly></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-icon btn-light" data-remove-row>&times;</button></td>
    </tr>
</template>

<script>
(function () {
    const rowsBody = document.getElementById('goods-receipt-items-body');
    const rowTemplate = document.getElementById('goods-receipt-item-template');
    const addButton = document.getElementById('goods-receipt-add-item');
    const supplierSelect = document.getElementById('goods-receipt-supplier');
    const loadSupplierProductsButton = document.getElementById('goods-receipt-load-supplier-products');
    const discountInput = document.getElementById('goods-receipt-discount');
    const taxInput = document.getElementById('goods-receipt-tax');
    const oldItems = @json($oldItems);
    const productSearchUrl = @json(route('inventory.goods-receipts.products.search'));
    const supplierProductsUrl = @json(route('inventory.goods-receipts.supplier-products'));

    function fieldName(index, field) {
        return `items[${index}][${field}]`;
    }

    function numberValue(value) {
        const parsed = parseFloat(value || 0);

        return Number.isFinite(parsed) ? parsed : 0;
    }

    function money(value) {
        return new Intl.NumberFormat().format(Math.max(value, 0));
    }

    function productText(item) {
        if (item.display_text) {
            return item.display_text;
        }

        const product = item.product || {};
        const name = item.product_name || product.name || '';
        const sku = item.sku || product.sku || '';

        if (name && sku) {
            return `${name} (${sku})`;
        }

        return name || sku || item.product_id || '';
    }

    function setRowNames() {
        rowsBody.querySelectorAll('[data-row]').forEach(function (row, index) {
            row.querySelectorAll('[data-field]').forEach(function (input) {
                input.name = fieldName(index, input.dataset.field);
            });
        });
    }

    function recalculate() {
        let subtotal = 0;

        rowsBody.querySelectorAll('[data-row]').forEach(function (row) {
            const orderedQty = numberValue(row.querySelector('[data-field="ordered_qty"]').value);
            const receivedQty = numberValue(row.querySelector('[data-field="received_qty"]').value);
            const unitCost = numberValue(row.querySelector('[data-field="unit_cost"]').value);
            const lineQty = receivedQty > 0 ? receivedQty : orderedQty;
            const lineTotal = lineQty * unitCost;

            row.querySelector('[data-field="line_total"]').value = money(lineTotal);
            subtotal += lineTotal;
        });

        const discount = numberValue(discountInput?.value);
        const tax = numberValue(taxInput?.value);
        const total = Math.max(subtotal - discount + tax, 0);

        document.getElementById('goods-receipt-subtotal').textContent = money(subtotal);
        document.getElementById('goods-receipt-discount-display').textContent = money(discount);
        document.getElementById('goods-receipt-tax-display').textContent = money(tax);
        document.getElementById('goods-receipt-total').textContent = money(total);
    }

    function syncProduct(row, data) {
        if (! data) {
            return;
        }

        row.querySelector('[data-field="product_id"]').value = data.product_id || data.id || '';
        row.querySelector('[data-field="product_variation_id"]').value = data.product_variation_id || '';
        row.querySelector('[data-field="supplier_product_id"]').value = data.supplier_product_id || '';
        row.querySelector('[data-field="product_name"]').value = data.product_name || data.text || '';
        row.querySelector('[data-field="sku"]').value = data.sku || '';
        row.querySelector('[data-field="barcode"]').value = data.barcode || '';

        const unitCostInput = row.querySelector('[data-field="unit_cost"]');
        if (! unitCostInput.value || numberValue(unitCostInput.value) === 0) {
            unitCostInput.value = data.unit_cost || 0;
        }

        recalculate();
    }

    function initProductSelect(row) {
        const select = row.querySelector('.goods-receipt-product-select');

        if (typeof $ === 'undefined' || ! $.fn || ! $.fn.select2 || $(select).data('select2')) {
            return;
        }

        $(select).select2({
            width: '100%',
            placeholder: @json(trans('plugins/inventory::inventory.goods_receipt.product')),
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: productSearchUrl,
                dataType: 'json',
                delay: 150,
                data: function (params) {
                    return {
                        q: params.term || '',
                        warehouse_id: document.getElementById('goods-receipt-warehouse')?.value || ''
                    };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                cache: true,
            },
        });

        $(select).on('select2:select', function (event) {
            syncProduct(row, event.params.data);
        }).on('select2:clear', function () {
            syncProduct(row, {
                product_id: '',
                product_variation_id: '',
                supplier_product_id: '',
                product_name: '',
                sku: '',
                barcode: '',
                unit_cost: 0,
            });
        });
    }

    function addRow(item = {}) {
        const fragment = rowTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-row]');
        const select = row.querySelector('[data-field="product_id"]');

        rowsBody.appendChild(fragment);
        setRowNames();

        const appendedRow = rowsBody.lastElementChild;
        initProductSelect(appendedRow);

        if (item.product_id) {
            const option = new Option(productText(item), item.product_id, true, true);
            select.appendChild(option);

            if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                $(select).trigger('change');
            }
        }

        appendedRow.querySelectorAll('[data-field]').forEach(function (input) {
            const field = input.dataset.field;

            if (field === 'product_id') {
                input.value = item.product_id || '';
                return;
            }

            if (field === 'product_name') {
                input.value = item.product_name || item.product?.name || '';
                return;
            }

            if (field === 'sku') {
                input.value = item.sku || item.product?.sku || '';
                return;
            }

            if (field === 'barcode') {
                input.value = item.barcode || item.product?.barcode || '';
                return;
            }

            input.value = item[field] ?? '';
        });

        recalculate();
    }

    addButton?.addEventListener('click', function () {
        addRow({ ordered_qty: 1, received_qty: 0, rejected_qty: 0, unit_cost: 0 });
    });

    rowsBody.addEventListener('input', function (event) {
        if (event.target.matches('[data-field="ordered_qty"], [data-field="received_qty"], [data-field="unit_cost"]')) {
            recalculate();
        }
    });

    rowsBody.addEventListener('click', function (event) {
        if (event.target.matches('[data-remove-row]')) {
            event.target.closest('[data-row]')?.remove();
            setRowNames();
            recalculate();
        }
    });

    [discountInput, taxInput].forEach(function (input) {
        input?.addEventListener('input', recalculate);
    });

    loadSupplierProductsButton?.addEventListener('click', function () {
        const supplierId = supplierSelect?.value || '';

        if (! supplierId) {
            return;
        }

        const warehouseId = document.getElementById('goods-receipt-warehouse')?.value || '';

        fetch(`${supplierProductsUrl}?supplier_id=${encodeURIComponent(supplierId)}&warehouse_id=${encodeURIComponent(warehouseId)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((data) => {
                const suggestions = data.results || [];

                if (! suggestions.length) {
                    return;
                }

                rowsBody.innerHTML = '';
                suggestions.forEach(addRow);
                setRowNames();
                recalculate();
            });
    });

    if (Array.isArray(oldItems) && oldItems.length) {
        oldItems.forEach(addRow);
    } else {
        addRow({ ordered_qty: 1, received_qty: 0, rejected_qty: 0, unit_cost: 0 });
    }

    setRowNames();
    recalculate();
})();
</script>
