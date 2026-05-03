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
            --gl-primary: #0F1419;
            --gl-secondary: #4A5568;
            --gl-tertiary: #2C5EF5;
            --gl-neutral: #F1F3F5;
            --gl-surface: #FFFFFF;
            --gl-border: rgba(74, 85, 104, .18);
            color: var(--gl-primary);
            font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: -1rem;
            min-height: calc(100vh - 56px);
            padding: 24px;
            background: var(--gl-neutral);
        }

        .warehouse-products-shell {
            display: grid;
            gap: 24px;
        }

        .warehouse-products-hero,
        .warehouse-products-card {
            background: var(--gl-surface);
            border: 1px solid var(--gl-border);
            border-radius: 16px;
            box-shadow: none;
        }

        .warehouse-products-hero {
            padding: 24px;
        }

        .warehouse-products-eyebrow {
            color: var(--gl-secondary);
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .warehouse-products-title {
            color: var(--gl-primary);
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.1;
            margin: 4px 0 10px;
        }

        .warehouse-products-subtitle {
            color: var(--gl-secondary);
            font-size: .95rem;
            line-height: 1.55;
            max-width: 54rem;
        }

        .warehouse-products-status-pill {
            align-items: center;
            background: var(--gl-neutral);
            border: 1px solid var(--gl-border);
            border-radius: 10px;
            color: var(--gl-primary);
            display: inline-flex;
            gap: 8px;
            padding: 10px 14px;
            font-weight: 600;
        }

        .warehouse-products-status-pill i {
            color: var(--gl-tertiary);
        }

        .warehouse-products-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .warehouse-products-metric {
            background: var(--gl-surface);
            border: 1px solid var(--gl-border);
            border-radius: 16px;
            padding: 18px;
            box-shadow: none;
        }

        .warehouse-products-metric span {
            color: var(--gl-secondary);
            display: block;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: .75rem;
            font-weight: 500;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .warehouse-products-metric strong {
            color: var(--gl-primary);
            display: block;
            font-size: 1.65rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .warehouse-products-card {
            padding: 24px;
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
            gap: 8px;
        }

        .warehouse-products-page .btn {
            border-radius: 10px;
            font-weight: 600;
            min-height: 44px;
            padding: 10px 16px;
        }

        .warehouse-products-page .btn-primary {
            background: var(--gl-tertiary);
            border-color: var(--gl-tertiary);
            color: #fff;
        }

        .warehouse-products-page .btn-primary:hover,
        .warehouse-products-page .btn-primary:focus {
            background: #244bd2;
            border-color: #244bd2;
            color: #fff;
        }

        .warehouse-products-tabs .btn-primary {
            background: var(--gl-primary);
            border-color: var(--gl-primary);
            color: #fff;
        }

        .warehouse-products-page .btn-outline-secondary,
        .warehouse-products-page .btn-outline-primary,
        .warehouse-products-page .btn-light {
            background: var(--gl-surface);
            border-color: var(--gl-border);
            color: var(--gl-primary);
        }

        .warehouse-products-page .btn-outline-secondary:hover,
        .warehouse-products-page .btn-outline-primary:hover,
        .warehouse-products-page .btn-light:hover {
            border-color: rgba(15, 20, 25, .38);
            color: var(--gl-primary);
        }

        .warehouse-products-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .warehouse-products-filter .form-control,
        .warehouse-products-filter .form-select {
            border-color: var(--gl-border);
            border-radius: 10px;
            color: var(--gl-primary);
            min-height: 44px;
            box-shadow: none;
        }

        .warehouse-products-filter .form-control:focus,
        .warehouse-products-filter .form-select:focus {
            border-color: var(--gl-tertiary);
            box-shadow: 0 0 0 3px rgba(44, 94, 245, .12);
        }

        .warehouse-products-filter .btn {
            min-height: 44px;
            padding-inline: 1rem;
        }

        .warehouse-products-table-wrap {
            overflow: hidden;
            border: 1px solid var(--gl-border);
            border-radius: 16px;
            background: var(--gl-surface);
        }

        .warehouse-products-table {
            margin-bottom: 0;
        }

        .warehouse-products-table thead th {
            background: var(--gl-neutral);
            border-bottom: 1px solid var(--gl-border) !important;
            color: var(--gl-secondary);
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: .75rem;
            font-weight: 500;
            letter-spacing: 0;
            text-transform: uppercase;
            padding-block: 14px;
            white-space: nowrap;
        }

        .warehouse-products-table tbody tr {
            transition: background-color .16s ease;
        }

        .warehouse-products-table tbody tr:hover {
            background: rgba(241, 243, 245, .72);
        }

        .warehouse-products-table td {
            border-color: rgba(74, 85, 104, .12);
            vertical-align: top;
            padding-block: 18px;
        }

        .warehouse-product-name {
            color: var(--gl-primary);
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0;
        }

        .warehouse-product-meta {
            color: var(--gl-secondary);
            font-size: 12px;
            line-height: 1.65;
            margin-top: 4px;
        }

        .warehouse-product-stack {
            display: grid;
            gap: 10px;
        }

        .warehouse-assignment {
            background: var(--gl-neutral);
            border: 0;
            border-radius: 10px;
            padding: 12px 14px;
        }

        .warehouse-assignment + .warehouse-assignment {
            margin-top: 0;
        }

        .warehouse-assignment-title {
            color: var(--gl-primary);
            font-weight: 600;
        }

        .warehouse-assignment-meta {
            color: var(--gl-secondary);
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
            display: grid;
            gap: 10px;
            max-height: 420px;
            overflow: auto;
        }

        .warehouse-products-modal-item {
            align-items: center;
            background: var(--gl-surface);
            border: 1px solid var(--gl-border);
            border-radius: 10px;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 12px 14px;
            box-shadow: none;
            transition: background-color .16s ease, border-color .16s ease;
            width: 100%;
        }

        .warehouse-products-modal-action {
            color: inherit;
            text-align: left;
        }

        .warehouse-products-modal-action:not(:disabled):hover {
            background: var(--gl-neutral);
            border-color: rgba(44, 94, 245, .32);
        }

        .warehouse-products-modal-action.is-moving {
            opacity: .35;
        }

        .warehouse-products-modal-action.is-changed {
            background: rgba(44, 94, 245, .06);
            border-color: rgba(44, 94, 245, .32);
        }

        .warehouse-products-modal-action:disabled {
            cursor: not-allowed;
            opacity: .75;
        }

        .warehouse-products-empty {
            background: var(--gl-neutral);
            border: 1px dashed rgba(74, 85, 104, .26);
            border-radius: 16px;
            padding: 42px 20px;
            text-align: center;
            color: var(--gl-secondary);
        }

        .warehouse-products-empty i {
            color: var(--gl-tertiary);
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .warehouse-products-quantity {
            color: var(--gl-primary);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .warehouse-products-warehouse-count {
            color: var(--gl-primary);
            font-size: 1.35rem;
            font-weight: 600;
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
            background: var(--gl-neutral);
            border: 1px solid transparent;
            border-radius: 10px;
            color: inherit;
            display: block;
            padding: 12px 14px;
            text-align: left;
            transition: background-color .16s ease, border-color .16s ease;
            width: 100%;
        }

        .warehouse-products-warehouse-button:hover {
            background: var(--gl-surface);
            border-color: rgba(44, 94, 245, .32);
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
            animation: none;
        }

        .warehouse-products-fade-in.delay-1 { animation-delay: .05s; }
        .warehouse-products-fade-in.delay-2 { animation-delay: .10s; }
        .warehouse-products-fade-in.delay-3 { animation-delay: .15s; }
        .warehouse-products-fade-in.delay-4 { animation-delay: .20s; }

        .warehouse-products-page .badge {
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0;
            padding: .45rem .75rem;
        }

        .warehouse-products-page .badge.bg-primary-lt,
        .warehouse-products-page .badge.text-primary {
            background: rgba(44, 94, 245, .10) !important;
            color: var(--gl-tertiary) !important;
        }

        .warehouse-products-page .badge.bg-success-lt,
        .warehouse-products-page .badge.bg-warning-lt,
        .warehouse-products-page .badge.bg-danger-lt,
        .warehouse-products-page .badge.bg-secondary-lt,
        .warehouse-products-page .badge.text-success,
        .warehouse-products-page .badge.text-warning,
        .warehouse-products-page .badge.text-danger,
        .warehouse-products-page .badge.text-secondary {
            background: var(--gl-neutral) !important;
            color: var(--gl-secondary) !important;
        }

        .warehouse-products-page .modal-content {
            border: 1px solid var(--gl-border) !important;
            border-radius: 16px !important;
            box-shadow: 0 24px 80px rgba(15, 20, 25, .18) !important;
            color: var(--gl-primary);
        }

        .warehouse-products-page .modal-title {
            color: var(--gl-primary);
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: 0;
        }

        .warehouse-products-page .modal-header,
        .warehouse-products-page .modal-body,
        .warehouse-products-page .modal-footer {
            padding-left: 24px;
            padding-right: 24px;
        }

        .warehouse-products-page .modal-xl {
            max-width: min(980px, calc(100vw - 32px));
        }

        @media (max-width: 991.98px) {
            .warehouse-products-page {
                padding: 16px;
            }

            .warehouse-products-hero,
            .warehouse-products-card {
                border-radius: 16px;
            }

            .warehouse-products-hero-actions {
                justify-content: flex-start;
            }

            .warehouse-products-filter,
            .warehouse-products-filter .form-control,
            .warehouse-products-filter .form-select,
            .warehouse-products-filter .btn {
                width: 100% !important;
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
                                        $stockSummary = $product->getRelation('inventoryStockSummary');
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

                                        $catalogQuantity = (float) ($product->quantity ?? 0);
                                        $inventoryQuantity = (float) ($stockSummary->inventory_quantity ?? 0);
                                        $inventoryAvailableQuantity = (float) ($stockSummary->inventory_available_qty ?? 0);
                                        $inventoryReservedQuantity = (float) ($stockSummary->inventory_reserved_qty ?? 0);
                                        $inventoryAverageCost = (float) ($stockSummary->inventory_average_cost ?? 0);
                                        $inventoryLastUnitCost = (float) ($stockSummary->inventory_last_unit_cost ?? 0);
                                        $displayCost = $product->cost_per_item !== null
                                            ? (float) $product->cost_per_item
                                            : ($inventoryAverageCost > 0 ? $inventoryAverageCost : ($inventoryLastUnitCost > 0 ? $inventoryLastUnitCost : null));
                                        $displayCostSource = $product->cost_per_item !== null ? 'TMĐT' : ($displayCost !== null ? 'Từ tồn kho' : null);
                                        $inventoryStockLabel = $warehouseId ? 'Tồn kho này' : 'Tồn kho';
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
                                                    <div class="warehouse-product-meta">{{ $inventoryStockLabel }}</div>
                                                    <div class="warehouse-products-quantity">{{ number_format($inventoryAvailableQuantity) }}</div>
                                                    <div class="warehouse-product-meta">
                                                        Tổng {{ number_format($inventoryQuantity) }}
                                                        @if($inventoryReservedQuantity > 0)
                                                            <span class="mx-1">•</span>Giữ {{ number_format($inventoryReservedQuantity) }}
                                                        @endif
                                                        <span class="mx-1">•</span>TMĐT {{ number_format($catalogQuantity) }}
                                                    </div>
                                                </div>
                                                <div class="warehouse-products-horizontal-stat">
                                                    <div class="warehouse-product-meta">Trạng thái TMĐT</div>
                                                    <span class="badge bg-primary-lt text-primary warehouse-assignment-badge">{{ $stockStatus ?: '-' }}</span>
                                                </div>
                                                <div class="warehouse-products-horizontal-stat">
                                                    <div class="warehouse-product-meta">{{ trans('plugins/inventory::inventory.warehouse_product.cost') }}</div>
                                                    <div class="fw-semibold text-dark">{{ $displayCost !== null ? number_format($displayCost) : '-' }}</div>
                                                    @if($displayCostSource)
                                                        <div class="warehouse-product-meta">{{ $displayCostSource }}</div>
                                                    @endif
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
