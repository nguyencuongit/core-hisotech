@php
    $supplier = $supplier ?? null;
    $oldContacts = old('contacts', $supplier?->contacts?->toArray() ?? [[]]);
    $oldAddresses = old('addresses', $supplier?->addresses?->toArray() ?? [[]]);
    $oldBanks = old('banks', $supplier?->banks?->toArray() ?? [[]]);
    $oldProducts = old('supplier_products', $supplier?->supplierProducts?->toArray() ?? []);
    $canSelectSupplierStatus = auth()->user()?->isSuperUser() === true;
    $defaultSupplierStatus = $canSelectSupplierStatus
        ? \Botble\Inventory\Enums\SupplierStatusEnum::DRAFT->value
        : \Botble\Inventory\Enums\SupplierStatusEnum::PENDING_APPROVAL->value;
    $selectedSupplierStatus = $canSelectSupplierStatus
        ? old('status', $supplier?->status?->value ?? $defaultSupplierStatus)
        : ($supplier?->status?->value ?? \Botble\Inventory\Enums\SupplierStatusEnum::PENDING_APPROVAL->value);
    $selectedSupplierStatusLabel = \Botble\Inventory\Enums\SupplierStatusEnum::tryFrom($selectedSupplierStatus)?->label()
        ?? \Botble\Inventory\Enums\SupplierStatusEnum::PENDING_APPROVAL->label();
@endphp

<style>
    .supplier-step-nav .nav-link { border-radius: 999px; }
    .supplier-step-card { border-radius: 20px; border: 1px solid rgba(15,23,42,.08); box-shadow: 0 10px 30px rgba(15,23,42,.06); }
    .supplier-repeat-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:16px; }
    .supplier-section-title { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:16px; }
    .supplier-section-badge { border-radius:999px; padding:6px 10px; background:#eff6ff; color:#1d4ed8; font-weight:600; font-size:12px; }
    .supplier-help { color:#64748b; font-size:13px; }
    .supplier-add-btn { border-radius: 999px; }
</style>

<div class="card supplier-step-card mb-4">
    <div class="card-body">
        <ul class="nav nav-pills supplier-step-nav mb-3" id="supplierStepTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#supplier-step-1" type="button">1. Thông tin NCC</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#supplier-step-2" type="button">2. Sản phẩm cung cấp</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="supplier-step-1">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.code') }}</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $supplier->code ?? '') }}" placeholder="{{ trans('plugins/inventory::inventory.supplier.code_placeholder') }}">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-8"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.name') }} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name ?? '') }}" placeholder="Công ty ABC / Nguyễn Văn A...">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.type.label') }}</label><select name="type" class="form-select @error('type') is-invalid @enderror">@foreach(\Botble\Inventory\Enums\SupplierTypeEnum::cases() as $case)<option value="{{ $case->value }}" @selected(old('type', $supplier->type->value ?? \Botble\Inventory\Enums\SupplierTypeEnum::COMPANY->value) === $case->value)>{{ $case->label() }}</option>@endforeach</select>@error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.status.label') }}</label>
                        @if($canSelectSupplierStatus)
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach(\Botble\Inventory\Enums\SupplierStatusEnum::cases() as $case)
                                    <option value="{{ $case->value }}" @selected($selectedSupplierStatus === $case->value)>{{ $case->label() }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="status" value="{{ $selectedSupplierStatus }}">
                            <div class="form-control bg-light">{{ $selectedSupplierStatusLabel }}</div>
                        @endif
                        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.tax_code') }}</label><input type="text" name="tax_code" class="form-control @error('tax_code') is-invalid @enderror" value="{{ old('tax_code', $supplier->tax_code ?? '') }}">@error('tax_code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.website') }}</label><input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $supplier->website ?? '') }}">@error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.note') }}</label><textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="4">{{ old('note', $supplier->note ?? '') }}</textarea>@error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-1">{{ trans('plugins/inventory::inventory.supplier.contacts') }}</h5>
                        <div class="supplier-help">Mỗi NCC có thể có nhiều liên hệ, chỉ giữ 1 liên hệ chính.</div>
                    </div>
                    <button type="button" class="btn btn-outline-primary supplier-add-btn" data-add-row="contacts">+ Thêm liên hệ</button>
                </div>
                <div id="supplier-contacts-wrapper" class="d-grid gap-3">
                    @foreach($oldContacts as $i => $contact)
                        <div class="supplier-repeat-item" data-row>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Liên hệ #{{ $i + 1 }}</strong>
                                <div class="d-flex gap-2 align-items-center">
                                    <label class="form-check m-0"><input type="checkbox" class="form-check-input" name="contacts[{{ $i }}][is_primary]" value="1" @checked(old("contacts.$i.is_primary", $contact['is_primary'] ?? false))><span class="form-check-label ms-1">{{ trans('plugins/inventory::inventory.supplier.primary') }}</span></label>
                                    <button type="button" class="btn btn-sm btn-light" data-remove-row>&times;</button>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.name') }}</label><input name="contacts[{{ $i }}][name]" class="form-control" value="{{ old("contacts.$i.name", $contact['name'] ?? '') }}"></div>
                                <div class="col-md-4"><label class="form-label">Chức danh</label><input name="contacts[{{ $i }}][position]" class="form-control" value="{{ old("contacts.$i.position", $contact['position'] ?? '') }}"></div>
                                <div class="col-md-2"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.phone') }}</label><input name="contacts[{{ $i }}][phone]" class="form-control" value="{{ old("contacts.$i.phone", $contact['phone'] ?? '') }}"></div>
                                <div class="col-md-2"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.email') }}</label><input name="contacts[{{ $i }}][email]" class="form-control" value="{{ old("contacts.$i.email", $contact['email'] ?? '') }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                    <div>
                        <h5 class="mb-1">{{ trans('plugins/inventory::inventory.supplier.addresses') }}</h5>
                        <div class="supplier-help">Thêm nhiều địa chỉ, chọn 1 địa chỉ mặc định.</div>
                    </div>
                    <button type="button" class="btn btn-outline-primary supplier-add-btn" data-add-row="addresses">+ Thêm địa chỉ</button>
                </div>
                <div id="supplier-addresses-wrapper" class="d-grid gap-3">
                    @foreach($oldAddresses as $i => $address)
                        <div class="supplier-repeat-item" data-row>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Địa chỉ #{{ $i + 1 }}</strong>
                                <div class="d-flex gap-2 align-items-center">
                                    <label class="form-check m-0"><input type="checkbox" class="form-check-input" name="addresses[{{ $i }}][is_default]" value="1" @checked(old("addresses.$i.is_default", $address['is_default'] ?? false))><span class="form-check-label ms-1">{{ trans('plugins/inventory::inventory.supplier.default') }}</span></label>
                                    <button type="button" class="btn btn-sm btn-light" data-remove-row>&times;</button>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3"><label class="form-label">Loại địa chỉ</label><select name="addresses[{{ $i }}][type]" class="form-select">@foreach(\Botble\Inventory\Enums\SupplierAddressTypeEnum::cases() as $case)<option value="{{ $case->value }}" @selected(old("addresses.$i.type", $address['type'] ?? \Botble\Inventory\Enums\SupplierAddressTypeEnum::HEADQUARTER->value) === $case->value)>{{ $case->label() }}</option>@endforeach</select></div>
                                <div class="col-md-9"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.addresses') }}</label><input name="addresses[{{ $i }}][address]" class="form-control" value="{{ old("addresses.$i.address", $address['address'] ?? '') }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                    <div>
                        <h5 class="mb-1">{{ trans('plugins/inventory::inventory.supplier.banks') }}</h5>
                        <div class="supplier-help">Thông tin thanh toán và tài khoản nhận tiền.</div>
                    </div>
                    <button type="button" class="btn btn-outline-primary supplier-add-btn" data-add-row="banks">+ Thêm tài khoản</button>
                </div>
                <div id="supplier-banks-wrapper" class="d-grid gap-3">
                    @foreach($oldBanks as $i => $bank)
                        <div class="supplier-repeat-item" data-row>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Tài khoản #{{ $i + 1 }}</strong>
                                <div class="d-flex gap-2 align-items-center">
                                    <label class="form-check m-0"><input type="checkbox" class="form-check-input" name="banks[{{ $i }}][is_default]" value="1" @checked(old("banks.$i.is_default", $bank['is_default'] ?? false))><span class="form-check-label ms-1">{{ trans('plugins/inventory::inventory.supplier.default') }}</span></label>
                                    <button type="button" class="btn btn-sm btn-light" data-remove-row>&times;</button>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.bank_name') }}</label><input name="banks[{{ $i }}][bank_name]" class="form-control" value="{{ old("banks.$i.bank_name", $bank['bank_name'] ?? '') }}"></div>
                                <div class="col-md-4"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.account_name') }}</label><input name="banks[{{ $i }}][account_name]" class="form-control" value="{{ old("banks.$i.account_name", $bank['account_name'] ?? '') }}"></div>
                                <div class="col-md-4"><label class="form-label">{{ trans('plugins/inventory::inventory.supplier.account_number') }}</label><input name="banks[{{ $i }}][account_number]" class="form-control" value="{{ old("banks.$i.account_number", $bank['account_number'] ?? '') }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-primary" id="go-to-products">Tiếp theo</button>
                </div>
            </div>

            <div class="tab-pane fade" id="supplier-step-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-1">{{ trans('plugins/inventory::inventory.supplier.products') }}</h5>
                        <div class="supplier-help">Chọn và khai báo sản phẩm cung cấp cho NCC này.</div>
                    </div>
                    <button type="button" class="btn btn-outline-primary supplier-add-btn" data-add-row="products">+ Thêm sản phẩm</button>
                </div>

                <div id="supplier-products-wrapper" class="d-grid gap-3"></div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="button" class="btn btn-secondary" id="back-to-info">Quay lại</button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-outline-secondary">{{ trans('core/base::forms.cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ trans('core/base::forms.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="supplier-row-template">
    <div class="supplier-repeat-item" data-row>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong data-row-title></strong>
            <button type="button" class="btn btn-sm btn-light" data-remove-row>&times;</button>
        </div>
        <div class="row g-3" data-row-fields></div>
    </div>
</template>

<script>
(function () {
    const step2 = document.querySelector('#supplier-step-2');
    const step1 = document.querySelector('#supplier-step-1');
    const goToProducts = document.getElementById('go-to-products');
    const backToInfo = document.getElementById('back-to-info');

    if (goToProducts && step2 && step1) {
        goToProducts.addEventListener('click', () => {
            const tab = bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#supplier-step-2"]'));
            tab.show();
            step2.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    if (backToInfo) {
        backToInfo.addEventListener('click', () => {
            const tab = bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#supplier-step-1"]'));
            tab.show();
            step1.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    const configs = {
        contacts: {
            wrapper: '#supplier-contacts-wrapper',
            title: 'Liên hệ',
            fields: [
                { name: 'name', label: '{{ trans('plugins/inventory::inventory.supplier.name') }}' },
                { name: 'position', label: 'Chức danh' },
                { name: 'phone', label: '{{ trans('plugins/inventory::inventory.supplier.phone') }}' },
                { name: 'email', label: '{{ trans('plugins/inventory::inventory.supplier.email') }}' },
            ],
        },
        addresses: {
            wrapper: '#supplier-addresses-wrapper',
            title: 'Địa chỉ',
            fields: [
                { name: 'type', label: 'Loại địa chỉ', type: 'select' },
                { name: 'address', label: '{{ trans('plugins/inventory::inventory.supplier.addresses') }}' },
            ],
        },
        banks: {
            wrapper: '#supplier-banks-wrapper',
            title: 'Tài khoản',
            fields: [
                { name: 'bank_name', label: '{{ trans('plugins/inventory::inventory.supplier.bank_name') }}' },
                { name: 'account_name', label: '{{ trans('plugins/inventory::inventory.supplier.account_name') }}' },
                { name: 'account_number', label: '{{ trans('plugins/inventory::inventory.supplier.account_number') }}' },
            ],
        },
        products: {
            wrapper: '#supplier-products-wrapper',
            title: 'Sản phẩm',
            fields: [
                { name: 'product_id', label: '{{ trans('plugins/inventory::inventory.supplier.product') }}', type: 'select' },
                { name: 'purchase_price', label: '{{ trans('plugins/inventory::inventory.supplier.purchase_price') }}' },
                { name: 'moq', label: '{{ trans('plugins/inventory::inventory.supplier.moq') }}' },
                { name: 'lead_time_days', label: '{{ trans('plugins/inventory::inventory.supplier.lead_time_days') }}' },
            ],
        },
    };

    const productSelectDebug = ['127.0.0.1', 'localhost'].includes(window.location.hostname)
        || window.supplierProductDebug === true
        || localStorage.getItem('supplierProductDebug') === '1';

    function logProductSelect(message, payload = {}) {
        if (! productSelectDebug) {
            return;
        }

        console.log('[supplier-products]', message, payload);
    }

    function getSupplierCodeValue() {
        return document.querySelector('input[name="code"]')?.value || '';
    }

    function syncSupplierSkuFields() {
        const supplierCode = getSupplierCodeValue();

        document.querySelectorAll('input[data-sync-supplier-code="1"]').forEach(function (input) {
            input.value = supplierCode;
        });

        logProductSelect('synced supplier sku from supplier code', {
            supplierCode,
            count: document.querySelectorAll('input[data-sync-supplier-code="1"]').length,
        });
    }

    function createFieldInput(group, idx, field) {
        const col = document.createElement('div');
        col.className = field.name === 'address' || field.name === 'name' || field.name === 'account_name' ? 'col-md-9' : 'col-md-4';
        if (group === 'products') {
            col.className = field.name === 'product_id' ? 'col-md-4' : 'col-md-2';
        }
        if (group === 'contacts') {
            col.className = field.name === 'name' ? 'col-md-4' : (field.name === 'position' ? 'col-md-4' : 'col-md-2');
        }
        if (group === 'banks') {
            col.className = field.name === 'bank_name' ? 'col-md-4' : 'col-md-4';
        }
        if (group === 'addresses') {
            col.className = field.name === 'type' ? 'col-md-3' : 'col-md-9';
        }

        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = field.label;

        const inputNamePrefix = group === 'products' ? 'supplier_products' : group;
        const isSelectField = field.type === 'select' || field.name === 'product_id';
        const input = document.createElement(isSelectField ? 'select' : 'input');
        input.className = field.name === 'product_id' ? 'form-select supplier-product-select' : 'form-control';
        input.name = `${inputNamePrefix}[${idx}][${field.name}]`;

        if (! isSelectField) input.type = 'text';
        if (field.name === 'product_id') {
            input.setAttribute('data-selected', '');
            input.setAttribute('data-selected-text', '');
            input.setAttribute('data-selected-image', '');
            logProductSelect('created product field', {
                index: idx,
                tagName: input.tagName,
                name: input.name,
            });
        }

        col.appendChild(label);
        col.appendChild(input);

        if (field.name === 'product_id') {
            const textInput = document.createElement('input');
            textInput.type = 'hidden';
            textInput.name = `${inputNamePrefix}[${idx}][product_text]`;

            const imageInput = document.createElement('input');
            imageInput.type = 'hidden';
            imageInput.name = `${inputNamePrefix}[${idx}][product_image]`;

            const supplierSkuInput = document.createElement('input');
            supplierSkuInput.type = 'hidden';
            supplierSkuInput.name = `${inputNamePrefix}[${idx}][supplier_sku]`;
            supplierSkuInput.value = getSupplierCodeValue();
            supplierSkuInput.setAttribute('data-sync-supplier-code', '1');

            col.appendChild(textInput);
            col.appendChild(imageInput);
            col.appendChild(supplierSkuInput);
        }

        return col;
    }

    document.querySelectorAll('[data-add-row]').forEach(btn => {
        btn.addEventListener('click', () => {
            const group = btn.getAttribute('data-add-row');
            const wrapper = document.querySelector(configs[group].wrapper);
            const count = wrapper.querySelectorAll('[data-row]').length;
            const tpl = document.getElementById('supplier-row-template').content.cloneNode(true);
            const item = tpl.querySelector('[data-row]');
            item.querySelector('[data-row-title]').textContent = `${configs[group].title} #${count + 1}`;
            const fieldsWrap = item.querySelector('[data-row-fields]');
            configs[group].fields.forEach(field => fieldsWrap.appendChild(createFieldInput(group, count, field)));
            wrapper.appendChild(tpl);
            logProductSelect('added row', {
                group,
                index: count,
                productSelectTag: item.querySelector('.supplier-product-select')?.tagName || null,
            });
            initProductSelects(item);
            syncSupplierSkuFields();
        });
    });

    function getProductText(product) {
        const relatedProduct = product?.product || {};
        const name = relatedProduct.name ? String(relatedProduct.name) : '';
        const sku = relatedProduct.sku ? String(relatedProduct.sku) : '';

        if (product?.product_text) {
            return product.product_text;
        }

        if (name && sku) {
            return `${name} (${sku})`;
        }

        return name || sku || product?.product_id || '';
    }

    function getProductImage(product) {
        return product?.product_image || product?.product?.image || '';
    }

    function getInputFieldName(inputName) {
        return inputName.match(/\[([^\]]+)\]$/)?.[1] || '';
    }

    function renderInitialProducts() {
        const wrapper = document.querySelector('#supplier-products-wrapper');
        if (! wrapper) return;
        const products = @json($oldProducts);
        if (! Array.isArray(products) || products.length === 0) {
            return;
        }

        wrapper.innerHTML = '';
        products.forEach((product, i) => {
            const tpl = document.getElementById('supplier-row-template').content.cloneNode(true);
            const item = tpl.querySelector('[data-row]');
            item.querySelector('[data-row-title]').textContent = `Sản phẩm #${i + 1}`;
            const fieldsWrap = item.querySelector('[data-row-fields]');
            configs.products.fields.forEach(field => fieldsWrap.appendChild(createFieldInput('products', i, field)));
            wrapper.appendChild(tpl);
            initProductSelects(item);
            const productSelect = item.querySelector('.supplier-product-select');
            if (product && product.product_id) {
                const option = new Option(getProductText(product), product.product_id, true, true);
                option.setAttribute('data-image', getProductImage(product));
                $(productSelect).append(option).trigger('change');
                syncProductSelection(productSelect);
            }
            const inputs = item.querySelectorAll('input, select');
            inputs.forEach(function (input) {
                const name = getInputFieldName(input.name);
                if (!name || name === 'product_id' || name === 'product_image') return;
                if (product && Object.prototype.hasOwnProperty.call(product, name)) {
                    input.value = product[name] ?? '';
                }
            });
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.matches('[data-remove-row]')) {
            const row = e.target.closest('[data-row]');
            if (row) row.remove();
        }
    });

    function syncProductSelection(el, data = null) {
        const row = el.closest('[data-row]');

        if (! row) {
            return;
        }

        const selectedOption = el.options[el.selectedIndex];
        const selectedId = data?.id || selectedOption?.value || '';
        const text = selectedId ? (data?.text || selectedOption?.text || selectedId) : '';
        const image = selectedId ? (data?.image || selectedOption?.getAttribute('data-image') || '') : '';
        const hiddenText = row.querySelector('input[name$="[product_text]"]');
        const hiddenImage = row.querySelector('input[name$="[product_image]"]');

        if (selectedOption && selectedId) {
            selectedOption.text = text;
            selectedOption.setAttribute('data-image', image);
        }

        if (hiddenText) hiddenText.value = text;
        if (hiddenImage) hiddenImage.value = image;

        logProductSelect('sync selection', {
            selectName: el.name,
            selectedId,
            text,
            image,
            optionText: selectedOption?.text || '',
            hiddenText: hiddenText?.value || '',
            hiddenImage: hiddenImage?.value || '',
        });
    }

    function initProductSelects(scope = document) {
        if (typeof $ === 'undefined' || ! $.fn || ! $.fn.select2) {
            logProductSelect('select2 unavailable', {
                hasJquery: typeof $ !== 'undefined',
                hasSelect2: typeof $ !== 'undefined' && !! $.fn?.select2,
            });
            return;
        }

        scope.querySelectorAll('.supplier-product-select').forEach(function (el) {
            if ($(el).data('select2')) {
                logProductSelect('select2 already initialized', {
                    name: el.name,
                    tagName: el.tagName,
                });
                return;
            }

            logProductSelect('init select2', {
                name: el.name,
                tagName: el.tagName,
                ajaxUrl: '{{ route('inventory.suppliers.products.search') }}',
            });

            $(el).select2({
                width: '100%',
                placeholder: '{{ trans('plugins/inventory::inventory.supplier.product') }}',
                allowClear: true,
                minimumInputLength: 0,
                templateResult: function (item) {
                    if (! item.id) return item.text;
                    const image = item.image ? `<img src="${item.image}" style="width:28px;height:28px;object-fit:cover;border-radius:8px;margin-right:8px;">` : '';
                    const price = item.price ? `<small class="text-muted d-block">Giá: ${item.price}</small>` : '';
                    return $(`<span class="d-flex align-items-center">${image}<span><span>${item.text}</span>${price}</span></span>`);
                },
                templateSelection: function (item) {
                    return item.text || item.element?.text || item.id || '';
                },
                ajax: {
                    url: '{{ route('inventory.suppliers.products.search') }}',
                    dataType: 'json',
                    delay: 150,
                    data: function (params) {
                        logProductSelect('ajax query', {
                            term: params.term || '',
                            page: params.page || 1,
                        });

                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        const results = data?.results || data?.data?.results || data?.data || [];
                        const normalized = Array.isArray(results) ? results : [];

                        logProductSelect('ajax results', {
                            raw: data,
                            count: normalized.length,
                            first: normalized[0] || null,
                        });

                        return {
                            results: normalized,
                        };
                    },
                    cache: true,
                },
            });

            $(el).on('select2:select', function (e) {
                logProductSelect('select2 selected', e.params.data);
                syncProductSelection(el, e.params.data);
            }).on('select2:clear', function () {
                logProductSelect('select2 cleared', {
                    name: el.name,
                });
                syncProductSelection(el);
            });

            const selected = el.getAttribute('data-selected');
            const selectedText = el.getAttribute('data-selected-text');
            const selectedImage = el.getAttribute('data-selected-image');
            if (selected) {
                const option = new Option(selectedText || selected, selected, true, true);
                option.setAttribute('data-image', selectedImage || '');
                $(el).append(option).trigger('change');
                syncProductSelection(el);
            }

            el.addEventListener('change', function () {
                logProductSelect('native change', {
                    name: el.name,
                    value: el.value,
                    selectedIndex: el.selectedIndex,
                    selectedText: el.options[el.selectedIndex]?.text || '',
                });
                syncProductSelection(el);
            });
        });
    }

    document.querySelector('input[name="code"]')?.addEventListener('input', syncSupplierSkuFields);

    renderInitialProducts();
    syncSupplierSkuFields();
    initProductSelects();
})();
</script>
