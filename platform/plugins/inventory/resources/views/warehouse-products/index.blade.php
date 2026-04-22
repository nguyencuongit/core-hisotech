@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $status = $filters['status'] ?? 'all';
        $warehouseId = $filters['warehouse_id'] ?? null;
        $catalogQuery = static fn (array $params): array => array_filter($params, static fn ($value): bool => $value !== null && $value !== '');
    @endphp

    <style>
        .warehouse-products-page {
            background: #f8fafc;
            margin: -1.25rem;
            min-height: calc(100vh - 56px);
            padding: 24px;
        }
        .warehouse-products-panel {
            background: #fff;
            border: 1px solid #e6ebf2;
            border-radius: 8px;
        }
        .warehouse-products-metric {
            border: 1px solid #e6ebf2;
            border-radius: 8px;
            padding: 14px 16px;
            background: #fff;
        }
        .warehouse-products-metric span {
            color: #64748b;
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .warehouse-products-metric strong {
            color: #111827;
            display: block;
            font-size: 22px;
            margin-top: 4px;
        }
        .warehouse-products-tabs .btn {
            border-radius: 6px;
        }
        .warehouse-products-table th {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
        }
        .warehouse-products-table td {
            vertical-align: top;
        }
        .warehouse-product-name {
            color: #111827;
            font-weight: 700;
        }
        .warehouse-product-meta {
            color: #64748b;
            font-size: 12px;
        }
        .warehouse-assignment {
            border: 1px solid #e6ebf2;
            border-radius: 8px;
            padding: 10px;
        }
        .warehouse-assignment + .warehouse-assignment {
            margin-top: 8px;
        }
        .warehouse-assignment-title {
            font-weight: 700;
        }
        .warehouse-assignment-meta {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>

    <div class="warehouse-products-page">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <h1 class="h3 mb-1">{{ trans('plugins/inventory::inventory.warehouse_product.name') }}</h1>
                    <div class="text-muted">{{ trans('plugins/inventory::inventory.warehouse_product.catalog_subtitle') }}</div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="warehouse-products-metric">
                        <span>{{ trans('plugins/inventory::inventory.warehouse_product.total_products') }}</span>
                        <strong>{{ number_format($summary['total_products']) }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="warehouse-products-metric">
                        <span>{{ trans('plugins/inventory::inventory.warehouse_product.in_warehouse') }}</span>
                        <strong>{{ number_format($summary['in_warehouse']) }}</strong>
                    </div>
                </div>
                @if($isSuperAdmin)
                    <div class="col-md-3">
                        <div class="warehouse-products-metric">
                            <span>{{ trans('plugins/inventory::inventory.warehouse_product.without_warehouse') }}</span>
                            <strong>{{ number_format($summary['without_warehouse']) }}</strong>
                        </div>
                    </div>
                @endif
                <div class="col-md-3">
                    <div class="warehouse-products-metric">
                        <span>{{ trans('plugins/inventory::inventory.warehouse_product.configured_rows') }}</span>
                        <strong>{{ number_format($summary['configured_rows']) }}</strong>
                    </div>
                </div>
            </div>

            <div class="warehouse-products-panel p-3 mb-3">
                <div class="d-flex justify-content-between gap-3 flex-wrap">
                    <div class="warehouse-products-tabs d-flex gap-2 flex-wrap">
                        @if($isSuperAdmin)
                            <a class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('inventory.warehouse-products.index', $catalogQuery(['status' => 'all', 'q' => $filters['q'] ?? null, 'warehouse_id' => $warehouseId])) }}">
                                {{ trans('plugins/inventory::inventory.warehouse_product.all_products') }}
                            </a>
                        @endif
                        <a class="btn {{ $status === 'in_warehouse' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('inventory.warehouse-products.index', $catalogQuery(['status' => 'in_warehouse', 'q' => $filters['q'] ?? null, 'warehouse_id' => $warehouseId])) }}">
                            {{ trans('plugins/inventory::inventory.warehouse_product.in_warehouse') }}
                        </a>
                        @if($isSuperAdmin)
                            <a class="btn {{ $status === 'without_warehouse' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('inventory.warehouse-products.index', $catalogQuery(['status' => 'without_warehouse', 'q' => $filters['q'] ?? null])) }}">
                                {{ trans('plugins/inventory::inventory.warehouse_product.without_warehouse') }}
                            </a>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('inventory.warehouse-products.index') }}" class="d-flex gap-2 flex-wrap">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" style="width: 260px" placeholder="{{ trans('plugins/inventory::inventory.warehouse_product.search_placeholder') }}">
                        @if($status !== 'without_warehouse')
                            <select name="warehouse_id" class="form-select" style="width: 240px">
                                <option value="">{{ trans('plugins/inventory::inventory.warehouse_product.all_warehouses') }}</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->getKey() }}" @selected((int) $warehouseId === (int) $warehouse->getKey())>
                                        {{ trim(($warehouse->code ? $warehouse->code . ' - ' : '') . $warehouse->name) }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit" class="btn btn-primary">{{ trans('plugins/inventory::inventory.warehouse_product.filter') }}</button>
                    </form>
                </div>
            </div>

            <div class="warehouse-products-panel">
                <div class="table-responsive">
                    <table class="table table-vcenter warehouse-products-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ trans('plugins/inventory::inventory.warehouse_product.product') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.warehouse_product.ecommerce_stock') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.warehouse_product.warehouse_assignment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php
                                    $warehouseProducts = $product->getRelation('inventoryWarehouseProducts');
                                    $stockStatus = $product->stock_status;

                                    if (is_object($stockStatus) && method_exists($stockStatus, 'label')) {
                                        $stockStatus = $stockStatus->label();
                                    } elseif (is_object($stockStatus) && method_exists($stockStatus, 'getValue')) {
                                        $stockStatus = $stockStatus->getValue();
                                    }
                                @endphp
                                <tr>
                                    <td style="min-width: 280px">
                                        <div class="warehouse-product-name">{{ $product->name ?: $product->getKey() }}</div>
                                        <div class="warehouse-product-meta">
                                            ID: {{ $product->getKey() }}
                                            @if($product->sku)
                                                - SKU: {{ $product->sku }}
                                            @endif
                                            @if($product->barcode)
                                                - Barcode: {{ $product->barcode }}
                                            @endif
                                        </div>
                                    </td>
                                    <td style="min-width: 180px">
                                        <div>{{ trans('plugins/inventory::inventory.warehouse_product.quantity') }}: {{ number_format((float) $product->quantity) }}</div>
                                        <div class="warehouse-product-meta">
                                            {{ trans('plugins/inventory::inventory.warehouse_product.stock_status') }}: {{ $stockStatus ?: '-' }}
                                        </div>
                                        <div class="warehouse-product-meta">
                                            {{ trans('plugins/inventory::inventory.warehouse_product.cost') }}: {{ $product->cost_per_item !== null ? number_format((float) $product->cost_per_item) : '-' }}
                                        </div>
                                    </td>
                                    <td style="min-width: 360px">
                                        @forelse($warehouseProducts as $warehouseProduct)
                                            <div class="warehouse-assignment">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <div class="warehouse-assignment-title">
                                                        {{ $warehouseProduct->warehouse?->name ?: $warehouseProduct->warehouse_id }}
                                                    </div>
                                                    <span class="badge {{ $warehouseProduct->is_active ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                                        {{ $warehouseProduct->is_active ? trans('plugins/inventory::inventory.warehouse_product.status_active') : trans('plugins/inventory::inventory.warehouse_product.status_inactive') }}
                                                    </span>
                                                </div>
                                                <div class="warehouse-assignment-meta">
                                                    @if($warehouseProduct->warehouse?->code)
                                                        {{ trans('plugins/inventory::inventory.warehouse_product.warehouse_code') }}: {{ $warehouseProduct->warehouse->code }}
                                                    @endif
                                                    @if($warehouseProduct->defaultLocation)
                                                        - {{ trans('plugins/inventory::inventory.warehouse_product.default_location') }}: {{ $warehouseProduct->defaultLocation->name }}
                                                    @endif
                                                    @if($warehouseProduct->supplier)
                                                        - {{ trans('plugins/inventory::inventory.warehouse_product.supplier') }}: {{ $warehouseProduct->supplier->name }}
                                                    @endif
                                                </div>
                                                @if($warehouseProduct->note)
                                                    <div class="warehouse-assignment-meta">{{ trans('plugins/inventory::inventory.warehouse_product.note') }}: {{ $warehouseProduct->note }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="badge bg-warning-lt text-warning">{{ trans('plugins/inventory::inventory.warehouse_product.without_warehouse') }}</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">{{ trans('plugins/inventory::inventory.warehouse_product.empty') }}</td>
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
    </div>
@endsection
