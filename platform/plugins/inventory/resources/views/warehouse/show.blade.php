@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $canManageWarehouseProducts = auth()->user()?->hasPermission('warehouse.products.manage') === true;
    @endphp

    <style>
        .warehouse-product-page {
            background: #f8fafc;
            margin: -1.25rem;
            padding: 28px;
            min-height: calc(100vh - 56px);
        }
        .warehouse-hero,
        .warehouse-soft-card,
        .warehouse-product-card {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 24px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .055);
        }
        .warehouse-hero {
            padding: 28px;
        }
        .warehouse-kicker {
            color: #6366f1;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .warehouse-title {
            color: #111827;
            font-size: 30px;
            font-weight: 750;
            margin: 6px 0;
        }
        .warehouse-muted {
            color: #64748b;
        }
        .warehouse-metric {
            background: #f9fafb;
            border: 1px solid #edf0f6;
            border-radius: 20px;
            padding: 18px;
        }
        .warehouse-metric-value {
            color: #111827;
            font-size: 24px;
            font-weight: 760;
        }
        .warehouse-soft-card {
            padding: 24px;
        }
        .warehouse-section-title {
            color: #111827;
            font-size: 18px;
            font-weight: 720;
            margin: 0;
        }
        .warehouse-form-control,
        .warehouse-form-select,
        .warehouse-soft-card .form-control,
        .warehouse-soft-card .form-select,
        .warehouse-product-card .form-control,
        .warehouse-product-card .form-select {
            border-color: #e4e8f0;
            border-radius: 14px;
            min-height: 42px;
        }
        .warehouse-primary-btn {
            background: #4f46e5;
            border-color: #4f46e5;
            border-radius: 999px;
            box-shadow: 0 10px 22px rgba(79, 70, 229, .18);
            padding-inline: 18px;
        }
        .warehouse-secondary-btn {
            border-color: #dce2ee;
            border-radius: 999px;
            padding-inline: 18px;
        }
        .warehouse-product-card {
            padding: 18px;
        }
        .warehouse-product-name {
            color: #111827;
            font-size: 16px;
            font-weight: 700;
        }
        .warehouse-pill {
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
        }
        .warehouse-pill-active {
            background: #ecfdf5;
            color: #047857;
        }
        .warehouse-pill-inactive {
            background: #fef2f2;
            color: #b91c1c;
        }
        .warehouse-product-meta {
            color: #64748b;
            font-size: 13px;
        }
        .warehouse-product-hint {
            background: #eef2ff;
            border: 1px solid #e0e7ff;
            border-radius: 16px;
            color: #4338ca;
            padding: 10px 12px;
        }
        @media (max-width: 768px) {
            .warehouse-product-page {
                margin: -1rem;
                padding: 16px;
            }
            .warehouse-hero,
            .warehouse-soft-card {
                padding: 18px;
            }
        }
    </style>

    <div class="page-body warehouse-product-page">
        <div class="container-fluid">
            <div class="warehouse-hero mb-4">
                <div class="d-flex justify-content-between gap-3 flex-wrap">
                    <div>
                        <div class="warehouse-kicker">{{ trans('plugins/inventory::inventory.warehouse.name') }}</div>
                        <h2 class="warehouse-title">{{ $warehouse->name }}</h2>
                        <div class="warehouse-muted">
                            {{ $warehouse->code }} @if($warehouse->address) - {{ $warehouse->address }} @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-start">
                        <a href="{{ route('inventory.warehouse.index') }}" class="btn btn-light warehouse-secondary-btn">
                            {{ trans('core/base::forms.cancel') }}
                        </a>
                        @if(auth()->user()?->hasPermission('warehouse.edit'))
                            <a href="{{ route('inventory.warehouse.edit', $warehouse) }}" class="btn btn-primary warehouse-primary-btn">
                                {{ trans('core/base::forms.edit') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="warehouse-metric">
                            <div class="warehouse-muted">{{ trans('plugins/inventory::inventory.warehouse_product.managed_products') }}</div>
                            <div class="warehouse-metric-value">{{ $warehouse->warehouse_products_count }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="warehouse-metric">
                            <div class="warehouse-muted">{{ trans('plugins/inventory::inventory.warehouse_product.active_products') }}</div>
                            <div class="warehouse-metric-value">{{ $warehouse->active_warehouse_products_count }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="warehouse-metric">
                            <div class="warehouse-muted">{{ trans('plugins/inventory::inventory.warehouse_product.locations') }}</div>
                            <div class="warehouse-metric-value">{{ $warehouse->locations_count }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($canManageWarehouseProducts)
                <div class="warehouse-soft-card mb-4">
                    <div class="d-flex justify-content-between gap-3 flex-wrap mb-4">
                        <div>
                            <h3 class="warehouse-section-title">{{ trans('plugins/inventory::inventory.warehouse_product.add_title') }}</h3>
                            <div class="warehouse-muted">{{ trans('plugins/inventory::inventory.warehouse_product.add_subtitle') }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('inventory.warehouse.products.store', $warehouse) }}" id="warehouse-product-create-form">
                        @csrf
                        <input type="hidden" name="product_id" data-create-product-id value="{{ old('product_id') }}">
                        <input type="hidden" name="product_variation_id" data-create-product-variation-id value="{{ old('product_variation_id') }}">
                        <input type="hidden" name="supplier_product_id" data-create-supplier-product-id value="{{ old('supplier_product_id') }}">
                        <input type="hidden" name="is_active" value="0">

                        <div class="row g-3">
                            <div class="col-lg-5">
                                <label class="form-label">{{ trans('plugins/inventory::inventory.warehouse_product.product') }} <span class="text-danger">*</span></label>
                                <select class="form-select warehouse-form-select" id="warehouse-product-select"></select>
                                @error('product_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @error('product_variation_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="warehouse-product-meta mt-2" data-create-product-meta></div>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">{{ trans('plugins/inventory::inventory.warehouse_product.default_location') }}</label>
                                <select name="default_location_id" class="form-select warehouse-form-select">
                                    <option value="">{{ trans('plugins/inventory::inventory.warehouse_product.select_location') }}</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->getKey() }}" @selected(old('default_location_id') == $location->getKey())>
                                            {{ trim(($location->path ? $location->path . ' / ' : '') . $location->code . ' - ' . $location->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_location_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">{{ trans('plugins/inventory::inventory.warehouse_product.supplier') }}</label>
                                <select name="supplier_id" class="form-select warehouse-form-select warehouse-product-supplier-select" data-product-id-target="[data-create-product-id]" data-supplier-product-target="[data-create-supplier-product-id]" data-hint-target="[data-create-supplier-hint]">
                                    <option value="">{{ trans('plugins/inventory::inventory.warehouse_product.select_supplier') }}</option>
                                    @foreach($suppliers as $id => $label)
                                        <option value="{{ $id }}" @selected(old('supplier_id') == $id)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @error('supplier_product_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-lg-8">
                                <label class="form-label">{{ trans('plugins/inventory::inventory.warehouse_product.note') }}</label>
                                <input type="text" name="note" class="form-control warehouse-form-control" value="{{ old('note') }}">
                            </div>
                            <div class="col-lg-4 d-flex align-items-end">
                                <label class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                    <span class="form-check-label">{{ trans('plugins/inventory::inventory.warehouse_product.is_active') }}</span>
                                </label>
                            </div>
                            <div class="col-12">
                                <div class="warehouse-product-hint d-none" data-create-supplier-hint></div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary warehouse-primary-btn">
                                    {{ trans('plugins/inventory::inventory.warehouse_product.add_product') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <div class="warehouse-soft-card">
                <div class="d-flex justify-content-between gap-3 flex-wrap mb-4">
                    <div>
                        <h3 class="warehouse-section-title">{{ trans('plugins/inventory::inventory.warehouse_product.title') }}</h3>
                        <div class="warehouse-muted">{{ trans('plugins/inventory::inventory.warehouse_product.subtitle') }}</div>
                    </div>
                </div>

                <div class="row g-3">
                    @forelse($warehouse->warehouseProducts as $warehouseProduct)
                        <div class="col-xl-6">
                            <div class="warehouse-product-card h-100">
                                <div class="d-flex justify-content-between gap-3 mb-3">
                                    <div>
                                        <div class="warehouse-product-name">{{ $warehouseProduct->product?->name ?: $warehouseProduct->product_id }}</div>
                                        <div class="warehouse-product-meta">
                                            SKU: {{ $warehouseProduct->product?->sku ?: '-' }}
                                            @if($warehouseProduct->product?->barcode)
                                                - Barcode: {{ $warehouseProduct->product?->barcode }}
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="warehouse-pill {{ $warehouseProduct->is_active ? 'warehouse-pill-active' : 'warehouse-pill-inactive' }}">
                                            {{ $warehouseProduct->is_active ? trans('plugins/inventory::inventory.warehouse_product.status_active') : trans('plugins/inventory::inventory.warehouse_product.status_inactive') }}
                                        </span>
                                    </div>
                                </div>

                                @if($canManageWarehouseProducts)
                                    <form method="POST" action="{{ route('inventory.warehouse.products.update', [$warehouse, $warehouseProduct]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="hidden" name="supplier_product_id" value="{{ $warehouseProduct->supplier_product_id }}" data-row-supplier-product-id="{{ $warehouseProduct->getKey() }}">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ trans('plugins/inventory::inventory.warehouse_product.default_location') }}</label>
                                                <select name="default_location_id" class="form-select">
                                                    <option value="">{{ trans('plugins/inventory::inventory.warehouse_product.select_location') }}</option>
                                                    @foreach($locations as $location)
                                                        <option value="{{ $location->getKey() }}" @selected($warehouseProduct->default_location_id == $location->getKey())>
                                                            {{ trim(($location->path ? $location->path . ' / ' : '') . $location->code . ' - ' . $location->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ trans('plugins/inventory::inventory.warehouse_product.supplier') }}</label>
                                                <select name="supplier_id" class="form-select warehouse-product-supplier-select" data-static-product-id="{{ $warehouseProduct->product_id }}" data-supplier-product-target="[data-row-supplier-product-id='{{ $warehouseProduct->getKey() }}']" data-hint-target="[data-row-supplier-hint='{{ $warehouseProduct->getKey() }}']">
                                                    <option value="">{{ trans('plugins/inventory::inventory.warehouse_product.select_supplier') }}</option>
                                                    @foreach($suppliers as $id => $label)
                                                        <option value="{{ $id }}" @selected($warehouseProduct->supplier_id === $id)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">{{ trans('plugins/inventory::inventory.warehouse_product.note') }}</label>
                                                <input type="text" name="note" class="form-control" value="{{ $warehouseProduct->note }}">
                                            </div>
                                            <div class="col-md-6 d-flex align-items-center">
                                                <label class="form-check mb-0">
                                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" @checked($warehouseProduct->is_active)>
                                                    <span class="form-check-label">{{ trans('plugins/inventory::inventory.warehouse_product.is_active') }}</span>
                                                </label>
                                            </div>
                                            <div class="col-md-6 d-flex justify-content-md-end gap-2">
                                                <button type="submit" class="btn btn-primary warehouse-primary-btn">
                                                    {{ trans('plugins/inventory::inventory.warehouse_product.save') }}
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <div class="warehouse-product-hint d-none" data-row-supplier-hint="{{ $warehouseProduct->getKey() }}"></div>
                                            </div>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('inventory.warehouse.products.destroy', [$warehouse, $warehouseProduct]) }}" class="mt-3" onsubmit="return confirm('{{ trans('plugins/inventory::inventory.warehouse_product.remove_help') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger warehouse-secondary-btn">
                                            {{ trans('plugins/inventory::inventory.warehouse_product.remove') }}
                                        </button>
                                        <div class="warehouse-product-meta mt-2">{{ trans('plugins/inventory::inventory.warehouse_product.remove_help') }}</div>
                                    </form>
                                @else
                                    <div class="row g-2 warehouse-product-meta">
                                        <div class="col-md-6">{{ trans('plugins/inventory::inventory.warehouse_product.default_location') }}: {{ $warehouseProduct->defaultLocation?->name ?: '-' }}</div>
                                        <div class="col-md-6">{{ trans('plugins/inventory::inventory.warehouse_product.supplier') }}: {{ $warehouseProduct->supplier?->name ?: '-' }}</div>
                                        @if($warehouseProduct->note)
                                            <div class="col-12">{{ trans('plugins/inventory::inventory.warehouse_product.note') }}: {{ $warehouseProduct->note }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center warehouse-muted py-5">{{ trans('plugins/inventory::inventory.warehouse_product.empty') }}</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const productSearchUrl = @json(route('inventory.warehouse.products.search', $warehouse));
        const supplierProductUrl = @json(route('inventory.warehouse.products.supplier-product', $warehouse));
        const supplierTermsEmpty = @json(trans('plugins/inventory::inventory.warehouse_product.supplier_terms_empty'));
        const supplierTermsLabel = @json(trans('plugins/inventory::inventory.warehouse_product.supplier_terms'));

        function resolveElement(selector) {
            return selector ? document.querySelector(selector) : null;
        }

        function renderSupplierHint(target, data) {
            if (! target) {
                return;
            }

            if (! data) {
                target.textContent = supplierTermsEmpty;
                target.classList.remove('d-none');
                return;
            }

            const parts = [];

            if (data.purchase_price) parts.push(`Gia: ${data.purchase_price}`);
            if (data.moq) parts.push(`MOQ: ${data.moq}`);
            if (data.lead_time_days) parts.push(`Lead time: ${data.lead_time_days} ngay`);

            target.textContent = `${supplierTermsLabel}: ${parts.length ? parts.join(' - ') : '-'}`;
            target.classList.remove('d-none');
        }

        function resolveSupplierProduct(select) {
            const supplierId = select.value || '';
            const productTarget = resolveElement(select.dataset.productIdTarget);
            const productId = select.dataset.staticProductId || productTarget?.value || '';
            const supplierProductTarget = resolveElement(select.dataset.supplierProductTarget);
            const hintTarget = resolveElement(select.dataset.hintTarget);

            if (supplierProductTarget) {
                supplierProductTarget.value = '';
            }

            if (! supplierId || ! productId) {
                if (hintTarget) {
                    hintTarget.classList.add('d-none');
                    hintTarget.textContent = '';
                }
                return;
            }

            fetch(`${supplierProductUrl}?supplier_id=${encodeURIComponent(supplierId)}&product_id=${encodeURIComponent(productId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((response) => response.json())
                .then((payload) => {
                    const data = payload.data || null;

                    if (supplierProductTarget && data?.supplier_product_id) {
                        supplierProductTarget.value = data.supplier_product_id;
                    }

                    renderSupplierHint(hintTarget, data);
                });
        }

        document.querySelectorAll('.warehouse-product-supplier-select').forEach(function (select) {
            select.addEventListener('change', function () {
                resolveSupplierProduct(select);
            });
        });

        const productSelect = document.getElementById('warehouse-product-select');

        if (productSelect && typeof $ !== 'undefined' && $.fn && $.fn.select2) {
            $(productSelect).select2({
                width: '100%',
                placeholder: @json(trans('plugins/inventory::inventory.warehouse_product.product_placeholder')),
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: productSearchUrl,
                    dataType: 'json',
                    delay: 150,
                    data: function (params) {
                        return { q: params.term || '' };
                    },
                    processResults: function (data) {
                        return { results: data.results || [] };
                    },
                    cache: true,
                },
            });

            $(productSelect).on('select2:select', function (event) {
                const product = event.params.data || {};
                const productId = document.querySelector('[data-create-product-id]');
                const variationId = document.querySelector('[data-create-product-variation-id]');
                const meta = document.querySelector('[data-create-product-meta]');

                if (productId) productId.value = product.product_id || product.id || '';
                if (variationId) variationId.value = product.product_variation_id || '';
                if (meta) {
                    const parts = [];
                    if (product.sku) parts.push(`SKU: ${product.sku}`);
                    if (product.barcode) parts.push(`Barcode: ${product.barcode}`);
                    if (product.quantity !== null && product.quantity !== undefined) parts.push(`Ton TMDT: ${product.quantity}`);
                    meta.textContent = parts.join(' - ');
                }

                document.querySelectorAll('#warehouse-product-create-form .warehouse-product-supplier-select').forEach(resolveSupplierProduct);
            }).on('select2:clear', function () {
                const productId = document.querySelector('[data-create-product-id]');
                const variationId = document.querySelector('[data-create-product-variation-id]');
                const supplierProductId = document.querySelector('[data-create-supplier-product-id]');
                const meta = document.querySelector('[data-create-product-meta]');

                if (productId) productId.value = '';
                if (variationId) variationId.value = '';
                if (supplierProductId) supplierProductId.value = '';
                if (meta) meta.textContent = '';
            });
        }
    })();
    </script>
@endsection
