@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $status = $filters['status'] ?? 'all';
        $warehouseId = $filters['warehouse_id'] ?? null;
        $catalogQuery = static fn (array $params): array => array_filter($params, static fn ($value): bool => $value !== null && $value !== '');
        $canQuickManageWarehouseProducts = auth()->user()?->hasPermission('warehouse.products.manage') && ($isSuperAdmin || $selectedWarehouse);

        $statusMeta = [
            'all' => ['label' => trans('plugins/inventory::inventory.warehouse_product.all_products'), 'icon' => 'ti ti-layout-grid', 'tone' => 'primary'],
            'in_warehouse' => ['label' => trans('plugins/inventory::inventory.warehouse_product.in_warehouse'), 'icon' => 'ti ti-package', 'tone' => 'success'],
            'without_warehouse' => ['label' => trans('plugins/inventory::inventory.warehouse_product.without_warehouse'), 'icon' => 'ti ti-package-off', 'tone' => 'warning'],
        ];
        $activeStatusMeta = $statusMeta[$status] ?? $statusMeta['all'];
    @endphp

    <style>
        .warehouse-products-page {
            position: relative;
            margin: -1.25rem;
            min-height: calc(100vh - 56px);
            padding: 28px;
            background:
                radial-gradient(circle at top left, rgba(99, 102, 241, .10), transparent 30%),
                radial-gradient(circle at top right, rgba(168, 85, 247, .08), transparent 26%),
                linear-gradient(180deg, #f8fafc 0%, #f5f7fb 100%);
        }

        .warehouse-products-shell {
            display: grid;
            gap: 18px;
        }

        .warehouse-products-hero,
        .warehouse-products-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(148, 163, 184, .16);
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .06);
            backdrop-filter: blur(14px);
        }

        .warehouse-products-hero {
            padding: 24px;
            overflow: hidden;
            position: relative;
        }

        .warehouse-products-hero::after {
            content: '';
            position: absolute;
            right: -40px;
            top: -40px;
            width: 160px;
            height: 160px;
            background: radial-gradient(circle, rgba(99, 102, 241, .18), transparent 68%);
            pointer-events: none;
        }

        .warehouse-products-eyebrow {
            color: #6366f1;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .warehouse-products-title {
            color: #0f172a;
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -.03em;
            margin: 6px 0 8px;
        }

        .warehouse-products-subtitle {
            color: #64748b;
            max-width: 54rem;
        }

        .warehouse-products-status-pill {
            align-items: center;
            background: rgba(99, 102, 241, .08);
            border-radius: 999px;
            color: #4338ca;
            display: inline-flex;
            gap: 8px;
            padding: 8px 14px;
            font-weight: 700;
        }

        .warehouse-products-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .warehouse-products-metric {
            border: 1px solid rgba(148, 163, 184, .16);
            border-radius: 22px;
            padding: 16px 18px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98) 0%, rgba(248, 250, 252, .96) 100%);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .warehouse-products-metric:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(15, 23, 42, .08);
        }

        .warehouse-products-metric span {
            color: #64748b;
            display: block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .warehouse-products-metric strong {
            color: #0f172a;
            display: block;
            font-size: 1.5rem;
            margin-top: 6px;
        }

        .warehouse-products-card {
            padding: 18px;
        }

        .warehouse-products-modal {
            z-index: 1065;
        }

        .modal-backdrop.show {
            z-index: 1060;
        }

        .warehouse-products-toolbar {
            display: flex;
            gap: 18px;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .warehouse-products-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .warehouse-products-tabs .btn {
            border-radius: 999px;
            padding: .68rem 1rem;
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .warehouse-products-tabs .btn:hover,
        .warehouse-products-filter .btn:hover,
        .warehouse-products-hero-actions .btn:hover {
            transform: translateY(-1px);
        }

        .warehouse-products-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .warehouse-products-filter .form-control,
        .warehouse-products-filter .form-select {
            border-radius: 16px;
            min-height: 44px;
            border-color: rgba(148, 163, 184, .28);
            box-shadow: none;
        }

        .warehouse-products-filter .form-control:focus,
        .warehouse-products-filter .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .12);
        }

        .warehouse-products-filter .btn {
            min-height: 44px;
            border-radius: 16px;
            padding-inline: 1rem;
        }

        .warehouse-products-table-wrap {
            overflow: hidden;
            border-radius: 20px;
            border: 1px solid rgba(148, 163, 184, .16);
        }

        .warehouse-products-table {
            margin-bottom: 0;
        }

        .warehouse-products-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid rgba(148, 163, 184, .18) !important;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding-block: 14px;
            white-space: nowrap;
        }

        .warehouse-products-table tbody tr {
            transition: background-color .18s ease, transform .18s ease;
        }

        .warehouse-products-table tbody tr:hover {
            background: rgba(99, 102, 241, .025);
        }

        .warehouse-products-table td {
            vertical-align: top;
            padding-block: 18px;
        }

        .warehouse-product-name {
            color: #0f172a;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: -.01em;
        }

        .warehouse-product-meta {
            color: #64748b;
            font-size: 12px;
            line-height: 1.65;
            margin-top: 4px;
        }

        .warehouse-product-stack {
            display: grid;
            gap: 10px;
        }

        .warehouse-assignment {
            border: 1px solid rgba(148, 163, 184, .16);
            border-radius: 18px;
            padding: 12px 14px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .94));
            box-shadow: 0 8px 20px rgba(15, 23, 42, .03);
        }

        .warehouse-assignment + .warehouse-assignment {
            margin-top: 0;
        }

        .warehouse-assignment-title {
            font-weight: 800;
            color: #0f172a;
        }

        .warehouse-assignment-meta {
            color: #64748b;
            font-size: 12px;
            margin-top: 5px;
            line-height: 1.6;
        }

        .warehouse-assignment-badge {
            border-radius: 999px;
            font-weight: 700;
            padding: .42rem .7rem;
        }

        .warehouse-products-modal-list {
            max-height: 420px;
            overflow: auto;
            display: grid;
            gap: 10px;
        }

        .warehouse-products-modal-item {
            align-items: center;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 18px;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 12px 14px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            width: 100%;
        }

        .warehouse-products-modal-action {
            color: inherit;
            text-align: left;
        }

        .warehouse-products-modal-action:not(:disabled):hover {
            background: #f8fafc;
            border-color: rgba(99, 102, 241, .35);
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, .07);
        }

        .warehouse-products-modal-action.is-moving {
            opacity: .35;
            transform: translateX(16px);
        }

        .warehouse-products-modal-action.is-changed {
            border-color: rgba(37, 99, 235, .32);
            background: rgba(37, 99, 235, .04);
        }

        .warehouse-products-modal-action:disabled {
            cursor: not-allowed;
            opacity: .75;
        }

        .warehouse-products-empty {
            border: 1px dashed rgba(148, 163, 184, .28);
            border-radius: 20px;
            padding: 42px 20px;
            text-align: center;
            color: #64748b;
            background: rgba(248, 250, 252, .65);
        }

        .warehouse-products-empty i {
            color: #6366f1;
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .warehouse-products-quantity {
            color: #0f172a;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .warehouse-products-warehouse-count {
            color: #0f172a;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .warehouse-products-horizontal-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: stretch;
        }

        .warehouse-products-horizontal-stat {
            min-width: 120px;
        }

        .warehouse-products-horizontal-stat .warehouse-product-meta {
            margin-top: 0;
            margin-bottom: 6px;
        }

        .warehouse-products-warehouse-button {
            align-items: flex-start;
            background: rgba(255, 255, 255, .88);
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 18px;
            color: inherit;
            display: block;
            padding: 12px 14px;
            text-align: left;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            width: 100%;
        }

        .warehouse-products-warehouse-button:hover {
            border-color: rgba(99, 102, 241, .34);
            box-shadow: 0 14px 28px rgba(15, 23, 42, .06);
            transform: translateY(-1px);
        }

        .warehouse-products-warehouse-button .warehouse-products-warehouse-count {
            display: block;
            margin: 6px 0 8px;
            line-height: 1;
        }

        .warehouse-products-warehouse-content {
            display: block;
        }

        .warehouse-products-fade-in {
            animation: warehouseProductsFadeIn .36s ease both;
        }

        .warehouse-products-fade-in.delay-1 { animation-delay: .05s; }
        .warehouse-products-fade-in.delay-2 { animation-delay: .10s; }
        .warehouse-products-fade-in.delay-3 { animation-delay: .15s; }
        .warehouse-products-fade-in.delay-4 { animation-delay: .20s; }

        @keyframes warehouseProductsFadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991.98px) {
            .warehouse-products-page {
                padding: 16px;
            }

            .warehouse-products-hero,
            .warehouse-products-card {
                border-radius: 20px;
            }

            .warehouse-products-hero-actions {
                justify-content: flex-start;
            }
        }
    </style>

    <div class="warehouse-products-page">
        <div class="container-fluid warehouse-products-shell">
            <div class="warehouse-products-hero warehouse-products-fade-in">
                <div class="d-flex justify-content-between align-items-start gap-4 flex-wrap">
                    <div>
                        <div class="warehouse-products-eyebrow">{{ trans('plugins/inventory::inventory.name') }}</div>
                        <h1 class="warehouse-products-title mb-0">
                            @if($selectedWarehouse)
                                {{ trans('plugins/inventory::inventory.warehouse_product.selected_warehouse_title', ['warehouse' => $selectedWarehouse->name]) }}
                            @else
                                {{ trans('plugins/inventory::inventory.warehouse_product.name') }}
                            @endif
                        </h1>
                        <p class="warehouse-products-subtitle mb-0 mt-2">
                            {{ trans('plugins/inventory::inventory.warehouse_product.catalog_subtitle') }}
                        </p>
                    </div>

                    <div class="d-grid gap-2">
                        <div class="warehouse-products-status-pill">
                            <i class="ti ti-sparkles"></i>
                            <span>{{ $activeStatusMeta['label'] }}</span>
                        </div>

                        <div class="warehouse-products-hero-actions">
                            @if($isSuperAdmin || $selectedWarehouse)
                                <a href="{{ route('inventory.warehouse-products.index', $catalogQuery(['status' => 'all'])) }}" class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    <i class="ti ti-layout-grid me-1"></i>
                                    {{ trans('plugins/inventory::inventory.warehouse_product.all_products') }}
                                </a>
                            @endif
                            @if($isSuperAdmin)
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#warehouse-products-help-modal">
                                    <i class="ti ti-info-circle me-1"></i>
                                    {{ trans('plugins/inventory::inventory.warehouse_product.subtitle') }}
                                </button>
                            @endif
                            @if($canQuickManageWarehouseProducts && $selectedWarehouse)
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#warehouse-products-toggle-modal">
                                    <i class="ti ti-switch-horizontal me-1"></i>
                                    {{ trans('plugins/inventory::inventory.warehouse_product.quick_add_to_warehouse') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3 warehouse-products-fade-in delay-1">
                    <div class="warehouse-products-metric">
                        <span>{{ trans('plugins/inventory::inventory.warehouse_product.total_products') }}</span>
                        <strong>{{ number_format($summary['total_products']) }}</strong>
                    </div>
                </div>
                <div class="col-md-3 warehouse-products-fade-in delay-2">
                    <div class="warehouse-products-metric">
                        <span>{{ trans('plugins/inventory::inventory.warehouse_product.in_warehouse') }}</span>
                        <strong>{{ number_format($summary['in_warehouse']) }}</strong>
                    </div>
                </div>
                @if($isSuperAdmin)
                    <div class="col-md-3 warehouse-products-fade-in delay-3">
                        <div class="warehouse-products-metric">
                            <span>{{ trans('plugins/inventory::inventory.warehouse_product.without_warehouse') }}</span>
                            <strong>{{ number_format($summary['without_warehouse']) }}</strong>
                        </div>
                    </div>
                @endif
                <div class="col-md-3 warehouse-products-fade-in delay-4">
                    <div class="warehouse-products-metric">
                        <span>{{ trans('plugins/inventory::inventory.warehouse_product.configured_rows') }}</span>
                        <strong>{{ number_format($summary['configured_rows']) }}</strong>
                    </div>
                </div>
            </div>

            <div class="warehouse-products-card warehouse-products-fade-in delay-2">
                <div class="warehouse-products-toolbar mb-3">
                    <div class="warehouse-products-tabs">
                        @if($isSuperAdmin || $selectedWarehouse)
                            <a class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('inventory.warehouse-products.index', $catalogQuery(['status' => 'all', 'q' => $filters['q'] ?? null, 'warehouse_id' => $isSuperAdmin ? $warehouseId : null])) }}">
                                {{ trans('plugins/inventory::inventory.warehouse_product.all_products') }}
                            </a>
                        @endif
                        @if($isSuperAdmin)
                            <a class="btn {{ $status === 'in_warehouse' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('inventory.warehouse-products.index', $catalogQuery(['status' => 'in_warehouse', 'q' => $filters['q'] ?? null, 'warehouse_id' => $isSuperAdmin ? $warehouseId : null])) }}">
                                {{ trans('plugins/inventory::inventory.warehouse_product.in_warehouse') }}
                            </a>
                            <a class="btn {{ $status === 'without_warehouse' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('inventory.warehouse-products.index', $catalogQuery(['status' => 'without_warehouse', 'q' => $filters['q'] ?? null])) }}">
                                {{ trans('plugins/inventory::inventory.warehouse_product.without_warehouse') }}
                            </a>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('inventory.warehouse-products.index') }}" class="warehouse-products-filter">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" style="width: 280px" placeholder="{{ trans('plugins/inventory::inventory.warehouse_product.search_placeholder') }}">
                        @if($isSuperAdmin && $status !== 'without_warehouse')
                            <select name="warehouse_id" class="form-select" style="width: 250px">
                                <option value="">{{ trans('plugins/inventory::inventory.warehouse_product.all_warehouses') }}</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->getKey() }}" @selected((int) $warehouseId === (int) $warehouse->getKey())>
                                        {{ trim(($warehouse->code ? $warehouse->code . ' - ' : '') . $warehouse->name) }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i>
                            {{ trans('plugins/inventory::inventory.warehouse_product.filter') }}
                        </button>
                    </form>
                </div>

                <div class="warehouse-products-table-wrap">
                    <div class="table-responsive">
                        <table class="table table-vcenter warehouse-products-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/inventory::inventory.warehouse_product.product') }}</th>
                                    <th>{{ trans('plugins/inventory::inventory.warehouse_product.warehouse_assignment') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php
                                        $warehouseProducts = $product->getRelation('inventoryWarehouseProducts');
                                        $allWarehouseProducts = $product->getRelation('allInventoryWarehouseProducts');
                                        $assignedWarehouseIds = $allWarehouseProducts->pluck('warehouse_id')->map(fn ($id) => (int) $id)->all();
                                        $warehouseProductsPayload = $allWarehouseProducts->map(function ($warehouseProduct): array {
                                            return [
                                                'name' => $warehouseProduct->warehouse?->name ?: $warehouseProduct->warehouse_id,
                                                'code' => $warehouseProduct->warehouse?->code,
                                                'status' => $warehouseProduct->is_active ? trans('plugins/inventory::inventory.warehouse_product.status_active') : trans('plugins/inventory::inventory.warehouse_product.status_inactive'),
                                                'default_location' => $warehouseProduct->defaultLocation?->name,
                                                'supplier' => $warehouseProduct->supplier?->name,
                                                'note' => $warehouseProduct->note,
                                            ];
                                        })->values()->all();
                                        $stockStatus = $product->stock_status;

                                        if (is_object($stockStatus) && method_exists($stockStatus, 'label')) {
                                            $stockStatus = $stockStatus->label();
                                        } elseif (is_object($stockStatus) && method_exists($stockStatus, 'getValue')) {
                                            $stockStatus = $stockStatus->getValue();
                                        }
                                    @endphp
                                    <tr>
                                        <td style="min-width: 320px">
                                            <div class="warehouse-product-name">{{ $product->name ?: $product->getKey() }}</div>
                                            <div class="warehouse-product-meta">
                                                ID: {{ $product->getKey() }}
                                                @if($product->sku)
                                                    <span class="mx-1">•</span>SKU: {{ $product->sku }}
                                                @endif
                                                @if($product->barcode)
                                                    <span class="mx-1">•</span>Barcode: {{ $product->barcode }}
                                                @endif
                                            </div>
                                            <div class="warehouse-products-horizontal-stats mt-3">
                                                <div class="warehouse-products-horizontal-stat">
                                                    <div class="warehouse-product-meta">{{ trans('plugins/inventory::inventory.warehouse_product.ecommerce_stock') }}</div>
                                                    <div class="warehouse-products-quantity">{{ number_format((float) $product->quantity) }}</div>
                                                </div>
                                                <div class="warehouse-products-horizontal-stat">
                                                    <div class="warehouse-product-meta">{{ trans('plugins/inventory::inventory.warehouse_product.stock_status') }}</div>
                                                    <span class="badge bg-primary-lt text-primary warehouse-assignment-badge">{{ $stockStatus ?: '-' }}</span>
                                                </div>
                                                <div class="warehouse-products-horizontal-stat">
                                                    <div class="warehouse-product-meta">{{ trans('plugins/inventory::inventory.warehouse_product.cost') }}</div>
                                                    <div class="fw-semibold text-dark">{{ $product->cost_per_item !== null ? number_format((float) $product->cost_per_item) : '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="min-width: 260px">
                                            <div class="warehouse-product-stack">
                                            <button
                                                type="button"
                                                class="warehouse-products-warehouse-button"
                                                data-warehouse-products-show-warehouses
                                                data-product-name="{{ $product->name ?: $product->getKey() }}"
                                                data-warehouse-products='@json($warehouseProductsPayload)'
                                                data-warehouse-count="{{ $allWarehouseProducts->count() }}"
                                            >
                                                <span class="warehouse-products-warehouse-content">
                                                    <span class="warehouse-product-meta d-block">{{ trans('plugins/inventory::inventory.warehouse_product.warehouse_assignment') }}</span>
                                                    <span class="warehouse-products-warehouse-count">{{ number_format($allWarehouseProducts->count()) }}</span>
                                                    <span class="badge bg-primary-lt text-primary rounded-pill">
                                                        {{ trans('plugins/inventory::inventory.warehouse_product.view_linked_warehouses') }}
                                                    </span>
                                                </span>
                                            </button>
                                                {{--
                                                    <div class="warehouse-assignment">
                                                        <div class="d-flex justify-content-between gap-2 align-items-start">
                                                            <div>
                                                                <div class="warehouse-assignment-title">
                                                                    {{ $warehouseProduct->warehouse?->name ?: $warehouseProduct->warehouse_id }}
                                                                </div>
                                                                <div class="warehouse-assignment-meta">
                                                                    @if($warehouseProduct->warehouse?->code)
                                                                        {{ trans('plugins/inventory::inventory.warehouse_product.warehouse_code') }}: {{ $warehouseProduct->warehouse->code }}
                                                                    @endif
                                                                    @if($warehouseProduct->defaultLocation)
                                                                        <span class="mx-1">•</span>{{ trans('plugins/inventory::inventory.warehouse_product.default_location') }}: {{ $warehouseProduct->defaultLocation->name }}
                                                                    @endif
                                                                    @if($warehouseProduct->supplier)
                                                                        <span class="mx-1">•</span>{{ trans('plugins/inventory::inventory.warehouse_product.supplier') }}: {{ $warehouseProduct->supplier->name }}
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="badge {{ $warehouseProduct->is_active ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }} warehouse-assignment-badge">
                                                                {{ $warehouseProduct->is_active ? trans('plugins/inventory::inventory.warehouse_product.status_active') : trans('plugins/inventory::inventory.warehouse_product.status_inactive') }}
                                                            </span>
                                                        </div>
                                                        @if($warehouseProduct->note)
                                                            <div class="warehouse-assignment-meta mt-2">{{ trans('plugins/inventory::inventory.warehouse_product.note') }}: {{ $warehouseProduct->note }}</div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="warehouse-products-empty py-4">
                                                        <i class="ti ti-box-off"></i>
                                                        <div class="fw-semibold text-dark">{{ trans('plugins/inventory::inventory.warehouse_product.without_warehouse') }}</div>
                                                        <div class="mt-1">{{ trans('plugins/inventory::inventory.warehouse_product.empty') }}</div>
                                                    </div>
                                                --}}

                                                @if($canQuickManageWarehouseProducts)
                                                    <div>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary rounded-pill"
                                                            data-warehouse-products-assign-button
                                                            data-product-id="{{ $product->getKey() }}"
                                                            data-product-name="{{ $product->name ?: $product->getKey() }}"
                                                            data-assigned-warehouse-ids="{{ implode(',', $assignedWarehouseIds) }}"
                                                        >
                                                            <i class="ti ti-plus me-1"></i>
                                                            {{ trans('plugins/inventory::inventory.warehouse_product.add_to_warehouse') }}
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">
                                            <div class="warehouse-products-empty my-4">
                                                <i class="ti ti-package-off"></i>
                                                <div class="fw-semibold text-dark">{{ trans('plugins/inventory::inventory.warehouse_product.empty') }}</div>
                                                <div class="mt-1">{{ trans('plugins/inventory::inventory.warehouse_product.catalog_subtitle') }}</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($products->hasPages())
                        <div class="p-3 border-top">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal fade warehouse-products-modal" id="warehouse-products-warehouses-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow-lg">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title mb-1" data-warehouse-products-warehouses-title></h5>
                                <div class="text-muted small">
                                    {{ trans('plugins/inventory::inventory.warehouse_product.warehouse_assignment') }}:
                                    <span data-warehouse-products-warehouses-count>0</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <div class="warehouse-products-modal-list" data-warehouse-products-warehouses-list></div>
                        </div>
                    </div>
                </div>
            </div>

            @if($canQuickManageWarehouseProducts)
                <div
                    class="modal fade warehouse-products-modal"
                    id="warehouse-products-assign-modal"
                    tabindex="-1"
                    aria-hidden="true"
                    data-add-label="{{ trans('plugins/inventory::inventory.warehouse_product.add_to_this_warehouse') }}"
                    data-already-label="{{ trans('plugins/inventory::inventory.warehouse_product.already_in_warehouse') }}"
                >
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <form method="POST" action="{{ route('inventory.warehouse-products.assign') }}" class="modal-content rounded-4 border-0 shadow-lg">
                            @csrf
                            <input type="hidden" name="product_id" value="" data-warehouse-products-assign-product-id>
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h5 class="modal-title mb-1">{{ trans('plugins/inventory::inventory.warehouse_product.select_warehouse_to_add') }}</h5>
                                    <div class="text-muted small" data-warehouse-products-assign-product-name></div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <div class="warehouse-products-modal-list">
                                    @foreach($warehouses as $warehouse)
                                        <label class="warehouse-products-modal-item warehouse-products-modal-action" data-warehouse-products-assign-option data-warehouse-id="{{ $warehouse->getKey() }}">
                                            <span class="d-flex align-items-center gap-3">
                                                <input type="checkbox" class="form-check-input m-0" name="warehouse_ids[]" value="{{ $warehouse->getKey() }}" data-warehouse-products-assign-checkbox>
                                                <span>
                                                    <span class="fw-semibold d-block">{{ $warehouse->name }}</span>
                                                    <span class="warehouse-product-meta">{{ $warehouse->code ?: $warehouse->getKey() }}</span>
                                                </span>
                                            </span>
                                            <span class="badge rounded-pill" data-warehouse-products-assign-state></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" data-dismiss="modal">
                                    {{ trans('plugins/inventory::inventory.warehouse_product.cancel') }}
                                </button>
                                <button type="submit" class="btn btn-primary" data-warehouse-products-assign-submit disabled>
                                    {{ trans('plugins/inventory::inventory.warehouse_product.confirm_add_to_warehouses') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($canQuickManageWarehouseProducts && $selectedWarehouse)
                <div class="modal fade warehouse-products-modal" id="warehouse-products-toggle-modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <form method="POST" action="{{ route('inventory.warehouse-products.toggle') }}" class="modal-content rounded-4 border-0 shadow-lg" data-warehouse-products-toggle-form>
                            @csrf
                            <input type="hidden" name="warehouse_id" value="{{ $selectedWarehouse->getKey() }}">
                            <div data-warehouse-products-add-inputs></div>
                            <div data-warehouse-products-remove-inputs></div>
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h5 class="modal-title mb-1">
                                        {{ trans('plugins/inventory::inventory.warehouse_product.quick_add_modal_title', ['warehouse' => $selectedWarehouse->name]) }}
                                    </h5>
                                    <div class="text-muted small">{{ trans('plugins/inventory::inventory.warehouse_product.remove_help') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6 class="mb-3 fw-bold">{{ trans('plugins/inventory::inventory.warehouse_product.products_in_selected_warehouse') }}</h6>
                                        <div class="warehouse-products-modal-list" data-warehouse-products-toggle-list="in">
                                            @forelse($toggleProducts['in'] as $toggleProduct)
                                                @php($canRemoveFromWarehouse = (float) $toggleProduct->quantity === 0.0)
                                                @if($canRemoveFromWarehouse)
                                                        <button
                                                            type="button"
                                                            class="warehouse-products-modal-item warehouse-products-modal-action"
                                                            data-warehouse-products-toggle-item
                                                            data-product-id="{{ $toggleProduct->getKey() }}"
                                                            data-original-side="in"
                                                            data-current-side="in"
                                                            data-can-remove="1"
                                                        >
                                                            <span>
                                                                <span class="fw-semibold d-block">{{ $toggleProduct->name ?: $toggleProduct->getKey() }}</span>
                                                                <span class="warehouse-product-meta">
                                                                    ID: {{ $toggleProduct->getKey() }}
                                                                    @if($toggleProduct->sku)
                                                                        <span class="mx-1">•</span>SKU: {{ $toggleProduct->sku }}
                                                                    @endif
                                                                    <span class="mx-1">•</span>{{ trans('plugins/inventory::inventory.warehouse_product.quantity') }}: {{ number_format((float) $toggleProduct->quantity) }}
                                                                </span>
                                                            </span>
                                                            <span class="badge bg-danger-lt text-danger rounded-pill" data-warehouse-products-toggle-state>
                                                                {{ trans('plugins/inventory::inventory.warehouse_product.remove_from_warehouse') }}
                                                            </span>
                                                        </button>
                                                @else
                                                    <button
                                                        type="button"
                                                        class="warehouse-products-modal-item warehouse-products-modal-action"
                                                        data-warehouse-products-toggle-item
                                                        data-product-id="{{ $toggleProduct->getKey() }}"
                                                        data-original-side="in"
                                                        data-current-side="in"
                                                        data-can-remove="0"
                                                        disabled
                                                    >
                                                        <span>
                                                            <span class="fw-semibold d-block">{{ $toggleProduct->name ?: $toggleProduct->getKey() }}</span>
                                                            <span class="warehouse-product-meta">
                                                                ID: {{ $toggleProduct->getKey() }}
                                                                @if($toggleProduct->sku)
                                                                    <span class="mx-1">•</span>SKU: {{ $toggleProduct->sku }}
                                                                @endif
                                                                <span class="mx-1">•</span>{{ trans('plugins/inventory::inventory.warehouse_product.quantity') }}: {{ number_format((float) $toggleProduct->quantity) }}
                                                            </span>
                                                        </span>
                                                        <span class="badge bg-secondary-lt text-secondary rounded-pill" data-warehouse-products-toggle-state>
                                                            {{ trans('plugins/inventory::inventory.warehouse_product.cannot_remove_has_quantity') }}
                                                        </span>
                                                    </button>
                                                @endif
                                            @empty
                                            @endforelse
                                            <div class="warehouse-products-empty d-none" data-warehouse-products-toggle-empty="in">
                                                <i class="ti ti-package"></i>
                                                <div class="fw-semibold text-dark">{{ trans('plugins/inventory::inventory.warehouse_product.no_products_in_selected_warehouse') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="mb-3 fw-bold">{{ trans('plugins/inventory::inventory.warehouse_product.products_not_in_selected_warehouse') }}</h6>
                                        <div class="warehouse-products-modal-list" data-warehouse-products-toggle-list="out">
                                            @forelse($toggleProducts['out'] as $toggleProduct)
                                                    <button
                                                        type="button"
                                                        class="warehouse-products-modal-item warehouse-products-modal-action"
                                                        data-warehouse-products-toggle-item
                                                        data-product-id="{{ $toggleProduct->getKey() }}"
                                                        data-original-side="out"
                                                        data-current-side="out"
                                                        data-can-remove="1"
                                                    >
                                                        <span>
                                                            <span class="fw-semibold d-block">{{ $toggleProduct->name ?: $toggleProduct->getKey() }}</span>
                                                            <span class="warehouse-product-meta">
                                                                ID: {{ $toggleProduct->getKey() }}
                                                                @if($toggleProduct->sku)
                                                                    <span class="mx-1">•</span>SKU: {{ $toggleProduct->sku }}
                                                                @endif
                                                                <span class="mx-1">•</span>{{ trans('plugins/inventory::inventory.warehouse_product.quantity') }}: {{ number_format((float) $toggleProduct->quantity) }}
                                                            </span>
                                                        </span>
                                                        <span class="badge bg-primary-lt text-primary rounded-pill" data-warehouse-products-toggle-state>
                                                            {{ trans('plugins/inventory::inventory.warehouse_product.add_to_this_warehouse') }}
                                                        </span>
                                                    </button>
                                            @empty
                                            @endforelse
                                            <div class="warehouse-products-empty d-none" data-warehouse-products-toggle-empty="out">
                                                <i class="ti ti-package-off"></i>
                                                <div class="fw-semibold text-dark">{{ trans('plugins/inventory::inventory.warehouse_product.no_products_not_in_selected_warehouse') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" data-dismiss="modal">
                                    {{ trans('plugins/inventory::inventory.warehouse_product.cancel') }}
                                </button>
                                <button type="submit" class="btn btn-primary" data-warehouse-products-toggle-submit disabled>
                                    {{ trans('plugins/inventory::inventory.warehouse_product.confirm_update_warehouse_products') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($isSuperAdmin)
                <div class="modal fade warehouse-products-modal" id="warehouse-products-help-modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow-lg">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title">{{ trans('plugins/inventory::inventory.warehouse_product.subtitle') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-2 text-muted">
                                <div class="mb-2">{{ trans('plugins/inventory::inventory.warehouse_product.catalog_subtitle') }}</div>
                                <div>{{ trans('plugins/inventory::inventory.warehouse_product.remove_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const assignModal = document.getElementById('warehouse-products-assign-modal')

            const getBootstrapModal = function (modal) {
                if (! window.bootstrap || ! window.bootstrap.Modal) {
                    return null
                }

                if (typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
                    return window.bootstrap.Modal.getOrCreateInstance(modal)
                }

                return window.bootstrap.Modal.getInstance(modal) || new window.bootstrap.Modal(modal)
            }

            const showModal = function (modal) {
                if (! modal) {
                    return
                }

                const bootstrapModal = getBootstrapModal(modal)

                if (bootstrapModal) {
                    bootstrapModal.show()

                    return
                }

                if (window.jQuery && typeof window.jQuery(modal).modal === 'function') {
                    window.jQuery(modal).modal('show')

                    return
                }

                modal.classList.add('show')
                modal.style.display = 'block'
                modal.removeAttribute('aria-hidden')
                document.body.classList.add('modal-open')
            }

            const hideModal = function (modal) {
                if (! modal) {
                    return
                }

                const bootstrapModal = getBootstrapModal(modal)

                if (bootstrapModal) {
                    bootstrapModal.hide()

                    return
                }

                if (window.jQuery && typeof window.jQuery(modal).modal === 'function') {
                    window.jQuery(modal).modal('hide')

                    return
                }

                modal.classList.remove('show')
                modal.style.display = 'none'
                modal.setAttribute('aria-hidden', 'true')
                document.body.classList.remove('modal-open')
            }

            const toggleModal = document.getElementById('warehouse-products-toggle-modal')
            const toggleLabels = {
                add: @json(trans('plugins/inventory::inventory.warehouse_product.add_to_this_warehouse')),
                remove: @json(trans('plugins/inventory::inventory.warehouse_product.remove_from_warehouse')),
                pendingAdd: @json(trans('plugins/inventory::inventory.warehouse_product.pending_add_to_warehouse')),
                pendingRemove: @json(trans('plugins/inventory::inventory.warehouse_product.pending_remove_from_warehouse')),
            }

            const updateToggleEmptyStates = function () {
                if (! toggleModal) {
                    return
                }

                toggleModal.querySelectorAll('[data-warehouse-products-toggle-list]').forEach(function (list) {
                    const side = list.dataset.warehouseProductsToggleList
                    const hasItems = !! list.querySelector('[data-warehouse-products-toggle-item]')

                    list.querySelectorAll('[data-warehouse-products-toggle-empty="' + side + '"]').forEach(function (empty) {
                        empty.classList.toggle('d-none', hasItems)
                    })
                })
            }

            const updateToggleItemState = function (item) {
                const originalSide = item.dataset.originalSide
                const currentSide = item.dataset.currentSide
                const state = item.querySelector('[data-warehouse-products-toggle-state]')

                item.classList.toggle('is-changed', originalSide !== currentSide)

                if (! state) {
                    return
                }

                if (currentSide === 'in') {
                    state.textContent = originalSide === 'out' ? toggleLabels.pendingAdd : toggleLabels.remove
                    state.className = originalSide === 'out'
                        ? 'badge bg-primary-lt text-primary rounded-pill'
                        : 'badge bg-danger-lt text-danger rounded-pill'

                    return
                }

                state.textContent = originalSide === 'in' ? toggleLabels.pendingRemove : toggleLabels.add
                state.className = originalSide === 'in'
                    ? 'badge bg-warning-lt text-warning rounded-pill'
                    : 'badge bg-primary-lt text-primary rounded-pill'
            }

            const updateToggleFormInputs = function () {
                if (! toggleModal) {
                    return
                }

                const addInputs = toggleModal.querySelector('[data-warehouse-products-add-inputs]')
                const removeInputs = toggleModal.querySelector('[data-warehouse-products-remove-inputs]')
                const submitButton = toggleModal.querySelector('[data-warehouse-products-toggle-submit]')
                let changedCount = 0

                if (addInputs) {
                    addInputs.innerHTML = ''
                }

                if (removeInputs) {
                    removeInputs.innerHTML = ''
                }

                toggleModal.querySelectorAll('[data-warehouse-products-toggle-item]').forEach(function (item) {
                    const originalSide = item.dataset.originalSide
                    const currentSide = item.dataset.currentSide

                    if (originalSide === currentSide) {
                        return
                    }

                    changedCount++

                    const input = document.createElement('input')
                    input.type = 'hidden'
                    input.value = item.dataset.productId || ''

                    if (originalSide === 'out' && currentSide === 'in') {
                        input.name = 'add_product_ids[]'
                        addInputs && addInputs.appendChild(input)

                        return
                    }

                    input.name = 'remove_product_ids[]'
                    removeInputs && removeInputs.appendChild(input)
                })

                if (submitButton) {
                    submitButton.disabled = changedCount === 0
                }
            }

            const resetToggleModal = function () {
                if (! toggleModal) {
                    return
                }

                toggleModal.querySelectorAll('[data-warehouse-products-toggle-item]').forEach(function (item) {
                    const originalSide = item.dataset.originalSide
                    const originalList = toggleModal.querySelector('[data-warehouse-products-toggle-list="' + originalSide + '"]')

                    item.dataset.currentSide = originalSide
                    item.classList.remove('is-moving', 'is-changed')
                    updateToggleItemState(item)

                    if (originalList) {
                        originalList.appendChild(item)
                    }
                })

                updateToggleEmptyStates()
                updateToggleFormInputs()
            }

            if (toggleModal) {
                toggleModal.addEventListener('show.bs.modal', resetToggleModal)
            }

            document.addEventListener('click', function (event) {
                const warehousesButton = event.target.closest('[data-warehouse-products-show-warehouses]')
                const assignButton = event.target.closest('[data-warehouse-products-assign-button]')

                if (warehousesButton) {
                    const data = JSON.parse(warehousesButton.dataset.warehouseProducts || '[]')
                    const count = warehousesButton.dataset.warehouseCount || data.length
                    const productName = warehousesButton.dataset.productName || ''
                    const modal = document.getElementById('warehouse-products-warehouses-modal')

                    if (! modal) {
                        return
                    }

                    event.preventDefault()

                    modal.querySelector('[data-warehouse-products-warehouses-title]').textContent = productName
                    modal.querySelector('[data-warehouse-products-warehouses-count]').textContent = count

                    const list = modal.querySelector('[data-warehouse-products-warehouses-list]')
                    if (list) {
                        list.innerHTML = ''

                        if (! data.length) {
                            list.innerHTML = '<div class="warehouse-products-empty"><i class="ti ti-package-off"></i><div class="fw-semibold text-dark">' + @json(trans('plugins/inventory::inventory.warehouse_product.no_products_in_selected_warehouse')) + '</div></div>'
                        } else {
                            data.forEach(function (item) {
                                const row = document.createElement('div')
                                row.className = 'warehouse-assignment'
                                row.innerHTML = `
                                    <div class="d-flex justify-content-between gap-2 align-items-start">
                                        <div>
                                            <div class="warehouse-assignment-title">${item.name || '-'}</div>
                                            <div class="warehouse-assignment-meta">
                                                ${item.code ? 'Mã kho: ' + item.code : ''}
                                                ${item.default_location ? '<span class="mx-1">•</span>Vị trí mặc định: ' + item.default_location : ''}
                                                ${item.supplier ? '<span class="mx-1">•</span>NCC: ' + item.supplier : ''}
                                            </div>
                                        </div>
                                        <span class="badge bg-success-lt text-success warehouse-assignment-badge">${item.status || ''}</span>
                                    </div>
                                    ${item.note ? '<div class="warehouse-assignment-meta mt-2">Ghi chú: ' + item.note + '</div>' : ''}
                                `
                                list.appendChild(row)
                            })
                        }
                    }

                    showModal(modal)
                    return
                }

                if (assignButton && assignModal) {
                    event.preventDefault()

                    const productId = assignButton.dataset.productId || ''
                    const productName = assignButton.dataset.productName || productId
                    const assignedWarehouseIds = new Set((assignButton.dataset.assignedWarehouseIds || '').split(',').filter(Boolean))
                    const addLabel = assignModal.dataset.addLabel || ''
                    const alreadyLabel = assignModal.dataset.alreadyLabel || ''
                    const productNameElement = assignModal.querySelector('[data-warehouse-products-assign-product-name]')

                    if (productNameElement) {
                        productNameElement.textContent = productName
                    }

                    const productIdInput = assignModal.querySelector('[data-warehouse-products-assign-product-id]')
                    const submitButton = assignModal.querySelector('[data-warehouse-products-assign-submit]')

                    if (productIdInput) {
                        productIdInput.value = productId
                    }

                    assignModal.querySelectorAll('[data-warehouse-products-assign-option]').forEach(function (option) {
                        const warehouseId = option.dataset.warehouseId || ''
                        const isAssigned = assignedWarehouseIds.has(warehouseId)
                        const checkbox = option.querySelector('[data-warehouse-products-assign-checkbox]')
                        const state = option.querySelector('[data-warehouse-products-assign-state]')

                        if (checkbox) {
                            checkbox.checked = false
                            checkbox.disabled = isAssigned
                        }

                        if (state) {
                            state.textContent = isAssigned ? alreadyLabel : addLabel
                            state.className = isAssigned
                                ? 'badge bg-secondary-lt text-secondary rounded-pill'
                                : 'badge bg-primary-lt text-primary rounded-pill'
                        }
                    })

                    if (submitButton) {
                        submitButton.disabled = true
                    }

                    showModal(assignModal)

                    return
                }

                if (event.target.closest('[data-warehouse-products-assign-checkbox]') && assignModal) {
                    const submitButton = assignModal.querySelector('[data-warehouse-products-assign-submit]')

                    if (submitButton) {
                        submitButton.disabled = ! assignModal.querySelector('[data-warehouse-products-assign-checkbox]:checked')
                    }

                    return
                }

                const toggleItem = event.target.closest('[data-warehouse-products-toggle-item]')

                if (toggleItem && toggleModal) {
                    event.preventDefault()

                    if (toggleItem.disabled || (toggleItem.dataset.currentSide === 'in' && toggleItem.dataset.canRemove !== '1')) {
                        return
                    }

                    const nextSide = toggleItem.dataset.currentSide === 'in' ? 'out' : 'in'
                    const targetList = toggleModal.querySelector('[data-warehouse-products-toggle-list="' + nextSide + '"]')

                    if (! targetList) {
                        return
                    }

                    toggleItem.classList.add('is-moving')

                    window.setTimeout(function () {
                        toggleItem.dataset.currentSide = nextSide
                        targetList.appendChild(toggleItem)
                        toggleItem.classList.remove('is-moving')

                        updateToggleItemState(toggleItem)
                        updateToggleEmptyStates()
                        updateToggleFormInputs()
                    }, 150)

                    return
                }

                const legacyTrigger = event.target.closest('[data-bs-toggle="modal"][data-bs-target]')

                if (legacyTrigger && (! window.bootstrap || ! window.bootstrap.Modal)) {
                    const target = document.querySelector(legacyTrigger.getAttribute('data-bs-target'))

                    if (target) {
                        event.preventDefault()
                        if (target.id === 'warehouse-products-toggle-modal') {
                            resetToggleModal()
                        }
                        showModal(target)
                    }

                    return
                }

                const dismissTrigger = event.target.closest('[data-bs-dismiss="modal"]')

                if (dismissTrigger && (! window.bootstrap || ! window.bootstrap.Modal)) {
                    const modal = dismissTrigger.closest('.modal')

                    if (modal) {
                        event.preventDefault()
                        hideModal(modal)
                    }
                }
            })
        })
    </script>
@endpush
