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
        ? old('status', $supplier?->getRawOriginal('status') ?? $defaultSupplierStatus)
        : ($supplier?->getRawOriginal('status') ?? \Botble\Inventory\Enums\SupplierStatusEnum::PENDING_APPROVAL->value);
    $selectedSupplierStatusLabel = \Botble\Inventory\Enums\SupplierStatusEnum::tryFrom((string) $selectedSupplierStatus)?->label()
        ?? \Botble\Inventory\Enums\SupplierStatusEnum::PENDING_APPROVAL->label();
    $selectedSupplierType = old('type', $supplier?->getRawOriginal('type') ?? \Botble\Inventory\Enums\SupplierTypeEnum::COMPANY->value);
    $addressTypeOptions = collect(\Botble\Inventory\Enums\SupplierAddressTypeEnum::cases())
        ->map(fn ($case) => ['value' => $case->value, 'label' => $case->label()])
        ->values()
        ->all();
    $statusOptions = collect(\Botble\Inventory\Enums\SupplierStatusEnum::cases())
        ->map(fn ($case) => ['value' => $case->value, 'label' => $case->label()])
        ->values()
        ->all();
    $contactCount = collect($oldContacts)->filter(fn ($item) => ! empty($item['name'] ?? null))->count();
    $addressCount = collect($oldAddresses)->filter(fn ($item) => ! empty($item['address'] ?? null))->count();
    $bankCount = collect($oldBanks)->filter(fn ($item) => ! empty($item['bank_name'] ?? null))->count();
    $productCount = collect($oldProducts)->filter(fn ($item) => ! empty($item['product_id'] ?? null))->count();
@endphp

<style>
    .supplier-glassline-page {
        background: #f1f3f5;
        min-height: calc(100vh - 56px);
    }

    .supplier-glassline {
        color: #0f1419;
        font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .supplier-page-wrap {
        padding-top: 24px;
        padding-bottom: 32px;
    }

    .supplier-page-header {
        align-items: flex-start;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .supplier-eyebrow,
    .supplier-label,
    .supplier-stat-label,
    .supplier-section-index {
        color: #4a5568;
        font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
        font-size: 0.75rem;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .supplier-page-title {
        color: #0f1419;
        font-size: clamp(1.75rem, 3vw, 2.25rem);
        font-weight: 600;
        letter-spacing: 0;
        line-height: 1.15;
        margin: 4px 0 8px;
    }

    .supplier-page-meta,
    .supplier-panel-note {
        color: #4a5568;
        font-size: 0.95rem;
    }

    .supplier-page-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .supplier-editor-shell {
        align-items: start;
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
    }

    .supplier-side,
    .supplier-section,
    .supplier-repeat-item {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.18);
        border-radius: 16px;
    }

    .supplier-side {
        padding: 24px;
        position: sticky;
        top: 16px;
    }

    .supplier-side-title {
        font-size: 1.15rem;
        font-weight: 600;
        margin: 6px 0 2px;
    }

    .supplier-side-code {
        color: #4a5568;
        font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
        font-size: 0.85rem;
        overflow-wrap: anywhere;
    }

    .supplier-step-nav {
        display: grid;
        gap: 8px;
        margin: 20px 0;
    }

    .supplier-step-nav .nav-link {
        border: 1px solid rgba(74, 85, 104, 0.2);
        border-radius: 10px;
        color: #0f1419;
        font-weight: 600;
        justify-content: flex-start;
        padding: 11px 12px;
        text-align: left;
        width: 100%;
    }

    .supplier-step-nav .nav-link.active {
        background: #0f1419;
        border-color: #0f1419;
        color: #fff;
    }

    .supplier-stat-grid {
        display: grid;
        gap: 8px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .supplier-stat {
        background: #f1f3f5;
        border-radius: 10px;
        padding: 12px;
    }

    .supplier-stat-value {
        display: block;
        font-size: 1.15rem;
        font-weight: 600;
        line-height: 1;
    }

    .supplier-main {
        min-width: 0;
    }

    .supplier-section {
        margin-bottom: 16px;
        padding: 24px;
    }

    .supplier-section-head {
        align-items: flex-start;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .supplier-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.25;
        margin: 0;
    }

    .supplier-section-tools {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .supplier-repeat-list {
        display: grid;
        gap: 12px;
    }

    .supplier-repeat-item {
        padding: 16px;
    }

    .supplier-contact-fields .form-label,
    .supplier-product-fields .form-label {
        white-space: nowrap;
    }

    .supplier-repeat-head {
        align-items: center;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .supplier-repeat-title {
        font-weight: 600;
    }

    .supplier-glassline .form-label {
        color: #4a5568;
        font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
        font-size: 0.75rem;
        letter-spacing: 0;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .supplier-glassline .form-control,
    .supplier-glassline .form-select,
    .supplier-glassline .select2-container--default .select2-selection--single {
        border-color: rgba(74, 85, 104, 0.24);
        border-radius: 10px;
        min-height: 44px;
    }

    .supplier-glassline .select2-container {
        width: 100% !important;
    }

    .supplier-glassline .form-control:focus,
    .supplier-glassline .form-select:focus {
        border-color: #2c5ef5;
        box-shadow: 0 0 0 3px rgba(44, 94, 245, 0.12);
    }

    .supplier-glassline .btn {
        border-radius: 10px;
        font-weight: 600;
    }

    .supplier-glassline .btn-primary,
    .supplier-save-button {
        background: #2c5ef5;
        border-color: #2c5ef5;
        color: #fff;
    }

    .supplier-btn-secondary,
    .supplier-add-btn,
    .supplier-remove-btn {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.26);
        color: #0f1419;
    }

    .supplier-btn-secondary:hover,
    .supplier-add-btn:hover,
    .supplier-remove-btn:hover {
        background: #f1f3f5;
        border-color: rgba(74, 85, 104, 0.42);
        color: #0f1419;
    }

    .supplier-form-footer {
        align-items: center;
        display: flex;
        gap: 10px;
        justify-content: space-between;
        margin-top: 16px;
    }

    @media (max-width: 991.98px) {
        .supplier-page-header,
        .supplier-form-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .supplier-page-actions {
            justify-content: flex-start;
        }

        .supplier-editor-shell {
            grid-template-columns: 1fr;
        }

        .supplier-side {
            position: static;
        }
    }
</style>

<div class="supplier-editor-shell">
    <aside class="supplier-side">
        <div class="supplier-eyebrow">{{ trans('plugins/inventory::inventory.name') }}</div>
        <div class="supplier-side-title">{{ trans('plugins/inventory::inventory.supplier.name') }}</div>
        <div class="supplier-side-code">{{ old('code', $supplier->code ?? trans('plugins/inventory::inventory.supplier.code_placeholder')) }}</div>

        <ul class="nav supplier-step-nav" id="supplierStepTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#supplier-step-1" type="button" role="tab">
                    {{ trans('plugins/inventory::inventory.supplier.show') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#supplier-step-2" type="button" role="tab">
                    {{ trans('plugins/inventory::inventory.supplier.products') }}
                </button>
            </li>
        </ul>

        <div class="supplier-stat-grid">
            <div class="supplier-stat">
                <span class="supplier-stat-value">{{ $contactCount }}</span>
                <span class="supplier-stat-label">{{ trans('plugins/inventory::inventory.supplier.contacts') }}</span>
            </div>
            <div class="supplier-stat">
                <span class="supplier-stat-value">{{ $addressCount }}</span>
                <span class="supplier-stat-label">{{ trans('plugins/inventory::inventory.supplier.addresses') }}</span>
            </div>
            <div class="supplier-stat">
                <span class="supplier-stat-value">{{ $bankCount }}</span>
                <span class="supplier-stat-label">{{ trans('plugins/inventory::inventory.supplier.banks') }}</span>
            </div>
            <div class="supplier-stat">
                <span class="supplier-stat-value">{{ $productCount }}</span>
                <span class="supplier-stat-label">{{ trans('plugins/inventory::inventory.supplier.products') }}</span>
            </div>
        </div>
    </aside>

    <main class="supplier-main">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="supplier-step-1" role="tabpanel">
                <section class="supplier-section">
                    <div class="supplier-section-head">
                        <div>
                            <div class="supplier-section-index">01</div>
                            <h2 class="supplier-section-title">{{ trans('plugins/inventory::inventory.supplier.show') }}</h2>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.code') }}</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $supplier->code ?? '') }}" placeholder="{{ trans('plugins/inventory::inventory.supplier.code_placeholder') }}">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name ?? '') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.type.label') }}</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                @foreach(\Botble\Inventory\Enums\SupplierTypeEnum::cases() as $case)
                                    <option value="{{ $case->value }}" @selected($selectedSupplierType === $case->value)>{{ $case->label() }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.status.label') }}</label>
                            @if($canSelectSupplierStatus)
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach($statusOptions as $statusOption)
                                        <option value="{{ $statusOption['value'] }}" @selected($selectedSupplierStatus === $statusOption['value'])>{{ $statusOption['label'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="status" value="{{ $selectedSupplierStatus }}">
                                <div class="form-control bg-light">{{ $selectedSupplierStatusLabel }}</div>
                            @endif
                            @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.tax_code') }}</label>
                            <input type="text" name="tax_code" class="form-control @error('tax_code') is-invalid @enderror" value="{{ old('tax_code', $supplier->tax_code ?? '') }}">
                            @error('tax_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.website') }}</label>
                            <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $supplier->website ?? '') }}">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.note') }}</label>
                            <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="4">{{ old('note', $supplier->note ?? '') }}</textarea>
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </section>

                <section class="supplier-section">
                    <div class="supplier-section-head">
                        <div>
                            <div class="supplier-section-index">02</div>
                            <h2 class="supplier-section-title">{{ trans('plugins/inventory::inventory.supplier.contacts') }}</h2>
                        </div>
                        <div class="supplier-section-tools">
                            <button type="button" class="btn supplier-add-btn" data-add-row="contacts">{{ trans('core/base::forms.add') }}</button>
                        </div>
                    </div>

                    <div id="supplier-contacts-wrapper" class="supplier-repeat-list">
                        @foreach($oldContacts as $i => $contact)
                            <div class="supplier-repeat-item" data-row>
                                <div class="supplier-repeat-head">
                                    <span class="supplier-repeat-title">{{ trans('plugins/inventory::inventory.supplier.contacts') }} #{{ $i + 1 }}</span>
                                    <div class="d-flex gap-2 align-items-center">
                                        <label class="form-check m-0">
                                            <input type="checkbox" class="form-check-input" name="contacts[{{ $i }}][is_primary]" value="1" @checked(old("contacts.$i.is_primary", $contact['is_primary'] ?? false))>
                                            <span class="form-check-label ms-1">{{ trans('plugins/inventory::inventory.supplier.primary') }}</span>
                                        </label>
                                        <button type="button" class="btn btn-sm supplier-remove-btn" data-remove-row>&times;</button>
                                    </div>
                                </div>
                                <div class="row g-3 supplier-contact-fields">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.name') }}</label>
                                        <input name="contacts[{{ $i }}][name]" class="form-control" value="{{ old("contacts.$i.name", $contact['name'] ?? '') }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">Position</label>
                                        <input name="contacts[{{ $i }}][position]" class="form-control" value="{{ old("contacts.$i.position", $contact['position'] ?? '') }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.phone') }}</label>
                                        <input name="contacts[{{ $i }}][phone]" class="form-control" value="{{ old("contacts.$i.phone", $contact['phone'] ?? '') }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.email') }}</label>
                                        <input name="contacts[{{ $i }}][email]" class="form-control" value="{{ old("contacts.$i.email", $contact['email'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="supplier-section">
                    <div class="supplier-section-head">
                        <div>
                            <div class="supplier-section-index">03</div>
                            <h2 class="supplier-section-title">{{ trans('plugins/inventory::inventory.supplier.addresses') }}</h2>
                        </div>
                        <div class="supplier-section-tools">
                            <button type="button" class="btn supplier-add-btn" data-add-row="addresses">{{ trans('core/base::forms.add') }}</button>
                        </div>
                    </div>

                    <div id="supplier-addresses-wrapper" class="supplier-repeat-list">
                        @foreach($oldAddresses as $i => $address)
                            <div class="supplier-repeat-item" data-row>
                                <div class="supplier-repeat-head">
                                    <span class="supplier-repeat-title">{{ trans('plugins/inventory::inventory.supplier.addresses') }} #{{ $i + 1 }}</span>
                                    <div class="d-flex gap-2 align-items-center">
                                        <label class="form-check m-0">
                                            <input type="checkbox" class="form-check-input" name="addresses[{{ $i }}][is_default]" value="1" @checked(old("addresses.$i.is_default", $address['is_default'] ?? false))>
                                            <span class="form-check-label ms-1">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>
                                        </label>
                                        <button type="button" class="btn btn-sm supplier-remove-btn" data-remove-row>&times;</button>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.type.label') }}</label>
                                        <select name="addresses[{{ $i }}][type]" class="form-select">
                                            @foreach($addressTypeOptions as $addressType)
                                                <option value="{{ $addressType['value'] }}" @selected(old("addresses.$i.type", $address['type'] ?? \Botble\Inventory\Enums\SupplierAddressTypeEnum::HEADQUARTER->value) === $addressType['value'])>{{ $addressType['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.addresses') }}</label>
                                        <input name="addresses[{{ $i }}][address]" class="form-control" value="{{ old("addresses.$i.address", $address['address'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="supplier-section">
                    <div class="supplier-section-head">
                        <div>
                            <div class="supplier-section-index">04</div>
                            <h2 class="supplier-section-title">{{ trans('plugins/inventory::inventory.supplier.banks') }}</h2>
                        </div>
                        <div class="supplier-section-tools">
                            <button type="button" class="btn supplier-add-btn" data-add-row="banks">{{ trans('core/base::forms.add') }}</button>
                        </div>
                    </div>

                    <div id="supplier-banks-wrapper" class="supplier-repeat-list">
                        @foreach($oldBanks as $i => $bank)
                            <div class="supplier-repeat-item" data-row>
                                <div class="supplier-repeat-head">
                                    <span class="supplier-repeat-title">{{ trans('plugins/inventory::inventory.supplier.banks') }} #{{ $i + 1 }}</span>
                                    <div class="d-flex gap-2 align-items-center">
                                        <label class="form-check m-0">
                                            <input type="checkbox" class="form-check-input" name="banks[{{ $i }}][is_default]" value="1" @checked(old("banks.$i.is_default", $bank['is_default'] ?? false))>
                                            <span class="form-check-label ms-1">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>
                                        </label>
                                        <button type="button" class="btn btn-sm supplier-remove-btn" data-remove-row>&times;</button>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.bank_name') }}</label>
                                        <input name="banks[{{ $i }}][bank_name]" class="form-control" value="{{ old("banks.$i.bank_name", $bank['bank_name'] ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.account_name') }}</label>
                                        <input name="banks[{{ $i }}][account_name]" class="form-control" value="{{ old("banks.$i.account_name", $bank['account_name'] ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ trans('plugins/inventory::inventory.supplier.account_number') }}</label>
                                        <input name="banks[{{ $i }}][account_number]" class="form-control" value="{{ old("banks.$i.account_number", $bank['account_number'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="supplier-form-footer">
                    <a href="{{ route('inventory.suppliers.index') }}" class="btn supplier-btn-secondary">{{ trans('core/base::forms.cancel') }}</a>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn supplier-btn-secondary" id="go-to-products">{{ trans('plugins/inventory::inventory.supplier.products') }}</button>
                        <button type="submit" class="btn btn-primary supplier-save-button">{{ trans('core/base::forms.save') }}</button>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="supplier-step-2" role="tabpanel">
                <section class="supplier-section">
                    <div class="supplier-section-head">
                        <div>
                            <div class="supplier-section-index">05</div>
                            <h2 class="supplier-section-title">{{ trans('plugins/inventory::inventory.supplier.products') }}</h2>
                        </div>
                        <div class="supplier-section-tools">
                            <button type="button" class="btn supplier-add-btn" data-add-row="products">{{ trans('core/base::forms.add') }}</button>
                        </div>
                    </div>

                    <div id="supplier-products-wrapper" class="supplier-repeat-list"></div>
                </section>

                <div class="supplier-form-footer">
                    <button type="button" class="btn supplier-btn-secondary" id="back-to-info">Back</button>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('inventory.suppliers.index') }}" class="btn supplier-btn-secondary">{{ trans('core/base::forms.cancel') }}</a>
                        <button type="submit" class="btn btn-primary supplier-save-button">{{ trans('core/base::forms.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<template id="supplier-row-template">
    <div class="supplier-repeat-item" data-row>
        <div class="supplier-repeat-head">
            <span class="supplier-repeat-title" data-row-title></span>
            <button type="button" class="btn btn-sm supplier-remove-btn" data-remove-row>&times;</button>
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
    const addressTypeOptions = @json($addressTypeOptions);
    const addressTypeDefault = @json(\Botble\Inventory\Enums\SupplierAddressTypeEnum::HEADQUARTER->value);

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
            title: @json(trans('plugins/inventory::inventory.supplier.contacts')),
            fields: [
                { name: 'name', label: @json(trans('plugins/inventory::inventory.supplier.name')) },
                { name: 'position', label: 'Position' },
                { name: 'phone', label: @json(trans('plugins/inventory::inventory.supplier.phone')) },
                { name: 'email', label: @json(trans('plugins/inventory::inventory.supplier.email')) },
            ],
        },
        addresses: {
            wrapper: '#supplier-addresses-wrapper',
            title: @json(trans('plugins/inventory::inventory.supplier.addresses')),
            fields: [
                { name: 'type', label: @json(trans('plugins/inventory::inventory.supplier.type.label')), type: 'select' },
                { name: 'address', label: @json(trans('plugins/inventory::inventory.supplier.addresses')) },
            ],
        },
        banks: {
            wrapper: '#supplier-banks-wrapper',
            title: @json(trans('plugins/inventory::inventory.supplier.banks')),
            fields: [
                { name: 'bank_name', label: @json(trans('plugins/inventory::inventory.supplier.bank_name')) },
                { name: 'account_name', label: @json(trans('plugins/inventory::inventory.supplier.account_name')) },
                { name: 'account_number', label: @json(trans('plugins/inventory::inventory.supplier.account_number')) },
            ],
        },
        products: {
            wrapper: '#supplier-products-wrapper',
            title: @json(trans('plugins/inventory::inventory.supplier.product')),
            fields: [
                { name: 'product_id', label: @json(trans('plugins/inventory::inventory.supplier.product')), type: 'select' },
                { name: 'purchase_price', label: @json(trans('plugins/inventory::inventory.supplier.purchase_price')) },
                { name: 'moq', label: @json(trans('plugins/inventory::inventory.supplier.moq')) },
                { name: 'lead_time_days', label: @json(trans('plugins/inventory::inventory.supplier.lead_time_days')) },
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

    function appendAddressTypeOptions(input) {
        addressTypeOptions.forEach(function (option) {
            input.appendChild(new Option(option.label, option.value, option.value === addressTypeDefault, option.value === addressTypeDefault));
        });
    }

    function createFieldInput(group, idx, field) {
        const col = document.createElement('div');
        col.className = field.name === 'address' || field.name === 'name' || field.name === 'account_name' ? 'col-md-9' : 'col-md-4';

        if (group === 'products') {
            const productColumns = {
                product_id: 'col-lg-5 col-md-6',
                purchase_price: 'col-lg-3 col-md-6',
                moq: 'col-lg-2 col-md-6',
                lead_time_days: 'col-lg-2 col-md-6',
            };

            col.className = productColumns[field.name] || 'col-md-6';
        }

        if (group === 'contacts') {
            col.className = 'col-lg-3 col-md-6';
        }

        if (group === 'banks') {
            col.className = 'col-md-4';
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
        input.className = field.name === 'product_id' ? 'form-select supplier-product-select' : (isSelectField ? 'form-select' : 'form-control');
        input.name = `${inputNamePrefix}[${idx}][${field.name}]`;

        if (! isSelectField) {
            input.type = 'text';
        }

        if (group === 'addresses' && field.name === 'type') {
            appendAddressTypeOptions(input);
        }

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

            if (group === 'contacts') {
                fieldsWrap.classList.add('supplier-contact-fields');
            }

            if (group === 'products') {
                fieldsWrap.classList.add('supplier-product-fields');
            }

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

        if (! wrapper) {
            return;
        }

        const products = @json($oldProducts);

        if (! Array.isArray(products) || products.length === 0) {
            return;
        }

        wrapper.innerHTML = '';
        products.forEach((product, i) => {
            const tpl = document.getElementById('supplier-row-template').content.cloneNode(true);
            const item = tpl.querySelector('[data-row]');
            item.querySelector('[data-row-title]').textContent = `${configs.products.title} #${i + 1}`;
            const fieldsWrap = item.querySelector('[data-row-fields]');
            fieldsWrap.classList.add('supplier-product-fields');
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

                if (! name || name === 'product_id' || name === 'product_image') {
                    return;
                }

                if (product && Object.prototype.hasOwnProperty.call(product, name)) {
                    input.value = product[name] ?? '';
                }
            });
        });
    }

    function getSelectedProductIds(exceptSelect = null) {
        return Array.from(document.querySelectorAll('.supplier-product-select'))
            .filter((select) => select !== exceptSelect)
            .map((select) => String(select.value || '').trim())
            .filter(Boolean);
    }

    document.addEventListener('click', function (e) {
        if (e.target.matches('[data-remove-row]')) {
            const row = e.target.closest('[data-row]');

            if (row) {
                row.remove();
            }
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

        if (hiddenText) {
            hiddenText.value = text;
        }

        if (hiddenImage) {
            hiddenImage.value = image;
        }

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
                placeholder: @json(trans('plugins/inventory::inventory.supplier.product')),
                allowClear: true,
                minimumInputLength: 0,
                templateResult: function (item) {
                    if (! item.id) {
                        return item.text;
                    }

                    const image = item.image ? `<img src="${item.image}" style="width:28px;height:28px;object-fit:cover;border-radius:8px;margin-right:8px;">` : '';
                    const price = item.price ? `<small class="text-muted d-block">Price: ${item.price}</small>` : '';

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
                        const selectedProductIds = getSelectedProductIds(el);
                        const filtered = normalized.filter((item) => ! selectedProductIds.includes(String(item.id)));

                        logProductSelect('ajax results', {
                            raw: data,
                            count: filtered.length,
                            first: filtered[0] || null,
                            excluded: selectedProductIds,
                        });

                        return {
                            results: filtered,
                        };
                    },
                    cache: true,
                },
            });

            $(el).on('select2:select', function (e) {
                const selectedId = String(e.params.data?.id || '');

                if (selectedId && getSelectedProductIds(el).includes(selectedId)) {
                    $(el).val(null).trigger('change');
                    window.alert('Sản phẩm này đã được chọn ở dòng khác.');

                    return;
                }

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
