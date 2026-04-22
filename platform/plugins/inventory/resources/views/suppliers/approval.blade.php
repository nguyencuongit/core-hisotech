@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $isPendingApproval = $supplier->status?->value === \Botble\Inventory\Enums\SupplierStatusEnum::PENDING_APPROVAL->value;
        $primaryContact = $supplier->contacts->firstWhere('is_primary', true) ?: $supplier->contacts->first();
        $defaultAddress = $supplier->addresses->firstWhere('is_default', true) ?: $supplier->addresses->first();
        $defaultBank = $supplier->banks->firstWhere('is_default', true) ?: $supplier->banks->first();
        $productsCount = $supplier->supplierProducts->count();
    @endphp

    <style>
        .supplier-approval-page {
            --supplier-approval-ink: #0f172a;
            --supplier-approval-muted: #64748b;
            --supplier-approval-line: #e2e8f0;
            --supplier-approval-soft: #f8fafc;
            --supplier-approval-primary: #2563eb;
            --supplier-approval-primary-2: #7c3aed;
            --supplier-approval-success: #16a34a;
            --supplier-approval-danger: #dc2626;
        }

        .supplier-approval-page .approval-header,
        .supplier-approval-page .approval-panel,
        .supplier-approval-page .product-row,
        .supplier-approval-page .info-strip {
            animation: supplierApprovalIn .28s ease both;
        }

        .supplier-approval-page .approval-header {
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.16), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #1d4ed8 48%, #7c3aed 100%);
            border-radius: 24px;
            padding: 28px;
            margin-bottom: 18px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
        }

        .supplier-approval-page .approval-header::before {
            content: "";
            position: absolute;
            inset: 18px auto auto -32px;
            width: 130px;
            height: 130px;
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            filter: blur(8px);
        }

        .supplier-approval-page .approval-header::after {
            content: "";
            position: absolute;
            inset: auto -12% -45% 52%;
            height: 220px;
            background: rgba(255,255,255,.12);
            transform: rotate(-10deg);
        }

        .supplier-approval-page .approval-header > * {
            position: relative;
            z-index: 1;
        }

        .supplier-approval-page .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(10px);
            font-size: 12px;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .supplier-approval-page .approval-kicker {
            color: rgba(255,255,255,.76);
            font-size: 13px;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .supplier-approval-page .approval-title {
            font-size: 28px;
            line-height: 1.2;
            margin: 6px 0 8px;
        }

        .supplier-approval-page .approval-subtitle {
            color: rgba(255,255,255,.8);
            max-width: 780px;
            margin: 0;
        }

        .supplier-approval-page .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .supplier-approval-page .header-actions .btn {
            border-color: rgba(255,255,255,.4);
            color: #fff;
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(8px);
        }

        .supplier-approval-page .approval-grid {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(420px, 1.05fr);
            gap: 18px;
            align-items: start;
        }

        .supplier-approval-page .approval-panel {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226,232,240,.9);
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .supplier-approval-page .approval-panel + .approval-panel {
            margin-top: 18px;
        }

        .supplier-approval-page .panel-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--supplier-approval-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: linear-gradient(180deg, rgba(248,250,252,.95), rgba(255,255,255,.9));
        }

        .supplier-approval-page .panel-title {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: var(--supplier-approval-ink);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .supplier-approval-page .panel-body {
            padding: 20px;
        }

        .supplier-approval-page .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .supplier-approval-page .metric {
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 14px;
            padding: 12px;
        }

        .supplier-approval-page .metric-label {
            color: rgba(255,255,255,.72);
            font-size: 12px;
            margin-bottom: 5px;
        }

        .supplier-approval-page .metric-value {
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            word-break: break-word;
        }

        .supplier-approval-page .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .supplier-approval-page .detail-item {
            border: 1px solid #edf2f7;
            background: var(--supplier-approval-soft);
            border-radius: 14px;
            padding: 12px;
            min-height: 72px;
        }

        .supplier-approval-page .detail-label {
            color: var(--supplier-approval-muted);
            font-size: 12px;
            margin-bottom: 5px;
        }

        .supplier-approval-page .detail-value {
            color: var(--supplier-approval-ink);
            font-weight: 650;
            word-break: break-word;
        }

        .supplier-approval-page .info-strip {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            border: 1px solid #edf2f7;
            background: #fff;
            border-radius: 14px;
            padding: 12px;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .supplier-approval-page .info-strip + .info-strip {
            margin-top: 10px;
        }

        .supplier-approval-page .info-strip:hover,
        .supplier-approval-page .product-row:hover {
            transform: translateY(-2px) scale(1.01);
            border-color: #bfdbfe;
            box-shadow: 0 16px 28px rgba(37, 99, 235, .10);
        }

        .supplier-approval-page .info-icon,
        .supplier-approval-page .product-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: var(--supplier-approval-primary);
        }

        .supplier-approval-page .info-main {
            min-width: 0;
        }

        .supplier-approval-page .info-title {
            font-weight: 700;
            color: var(--supplier-approval-ink);
            overflow-wrap: anywhere;
        }

        .supplier-approval-page .info-sub {
            color: var(--supplier-approval-muted);
            font-size: 13px;
            margin-top: 2px;
            overflow-wrap: anywhere;
        }

        .supplier-approval-page .timeline {
            display: grid;
            gap: 12px;
        }

        .supplier-approval-page .timeline-item {
            display: grid;
            grid-template-columns: 14px minmax(0, 1fr);
            gap: 12px;
        }

        .supplier-approval-page .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--supplier-approval-primary);
            box-shadow: 0 0 0 5px #dbeafe;
            margin-top: 5px;
        }

        .supplier-approval-page .timeline-content {
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--supplier-approval-line);
        }

        .supplier-approval-page .timeline-label {
            color: var(--supplier-approval-muted);
            font-size: 12px;
        }

        .supplier-approval-page .timeline-value {
            color: var(--supplier-approval-ink);
            font-weight: 650;
        }

        .supplier-approval-page .product-row {
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 14px;
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr);
            gap: 14px;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .supplier-approval-page .product-row + .product-row {
            margin-top: 12px;
        }

        .supplier-approval-page .product-name {
            font-weight: 750;
            color: var(--supplier-approval-ink);
            font-size: 15px;
            overflow-wrap: anywhere;
        }

        .supplier-approval-page .product-meta {
            color: var(--supplier-approval-muted);
            font-size: 13px;
            margin-top: 3px;
        }

        .supplier-approval-page .term-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .supplier-approval-page .term {
            background: var(--supplier-approval-soft);
            border-radius: 12px;
            padding: 10px;
        }

        .supplier-approval-page .term-label {
            color: var(--supplier-approval-muted);
            font-size: 12px;
        }

        .supplier-approval-page .term-value {
            color: var(--supplier-approval-ink);
            font-weight: 750;
            margin-top: 4px;
            overflow-wrap: anywhere;
        }

        .supplier-approval-page .decision-panel {
            position: sticky;
            top: 82px;
        }

        .supplier-approval-page .decision-body {
            background: #fbfdff;
        }

        .supplier-approval-page .approval-note {
            resize: vertical;
            min-height: 104px;
        }

        .supplier-approval-page .decision-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 12px;
        }

        .supplier-approval-page .decision-actions .btn {
            min-height: 46px;
            font-weight: 700;
            border-radius: 14px;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .08);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .supplier-approval-page .decision-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(15, 23, 42, .12);
        }

        .supplier-approval-page .pending-pulse {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .supplier-approval-page .pending-pulse::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #f59e0b;
            box-shadow: 0 0 0 rgba(245, 158, 11, .55);
            animation: supplierApprovalPulse 1.4s infinite;
        }

        .supplier-approval-page .empty-state {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 16px;
            padding: 28px;
            text-align: center;
            color: var(--supplier-approval-muted);
        }

        @keyframes supplierApprovalIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes supplierApprovalPulse {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, .5); }
            70% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        @media (max-width: 1199.98px) {
            .supplier-approval-page .approval-grid {
                grid-template-columns: 1fr;
            }

            .supplier-approval-page .decision-panel {
                position: static;
            }
        }

        @media (max-width: 767.98px) {
            .supplier-approval-page .approval-header {
                padding: 18px;
            }

            .supplier-approval-page .header-actions {
                justify-content: flex-start;
                margin-top: 14px;
            }

            .supplier-approval-page .metric-grid,
            .supplier-approval-page .detail-grid,
            .supplier-approval-page .term-grid,
            .supplier-approval-page .decision-actions {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .supplier-approval-page *,
            .supplier-approval-page *::before,
            .supplier-approval-page *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>

    <div class="page-body">
        <div class="container-fluid supplier-approval-page">
            <div class="approval-header">
                <div class="row g-3 align-items-start">
                    <div class="col-lg-8">
                        <div class="hero-badge mb-3">
                            <x-core::icon name="ti ti-sparkles" />
                            {{ trans('plugins/inventory::inventory.supplier.approval_page.title') }}
                        </div>
                        <div class="approval-kicker">{{ trans('plugins/inventory::inventory.supplier.approval_page.subtitle') }}</div>
                        <h1 class="approval-title">{{ $supplier->name }}</h1>
                        <p class="approval-subtitle">{{ trans('plugins/inventory::inventory.supplier.approval_page.subtitle') }}</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="header-actions">
                            <a href="{{ route('inventory.suppliers.index') }}" class="btn">
                                <x-core::icon name="ti ti-list" />
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.back_to_list') }}
                            </a>
                            <a href="{{ route('inventory.suppliers.edit', $supplier) }}" class="btn">
                                <x-core::icon name="ti ti-edit" />
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.go_to_edit') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="metric-grid">
                    <div class="metric">
                        <div class="metric-label">{{ trans('plugins/inventory::inventory.supplier.code') }}</div>
                        <div class="metric-value">{{ $supplier->code ?: '-' }}</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">{{ trans('plugins/inventory::inventory.supplier.status.label') }}</div>
                        <div class="metric-value">{!! $supplier->status?->toHtml() !!}</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">{{ trans('plugins/inventory::inventory.supplier.products') }}</div>
                        <div class="metric-value">{{ number_format($productsCount) }}</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">{{ trans('plugins/inventory::inventory.supplier.submitted_at') }}</div>
                        <div class="metric-value">{{ $supplier->submitted_at ? BaseHelper::formatDateTime($supplier->submitted_at) : '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="approval-grid">
                <div>
                    <div class="approval-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-building-store" />
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.supplier_profile') }}
                            </h2>
                            @if($isPendingApproval)
                                <span class="badge bg-warning text-warning-fg pending-pulse">{{ trans('plugins/inventory::inventory.supplier.status.pending_approval') }}</span>
                            @endif
                        </div>
                        <div class="panel-body">
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <div class="detail-label">{{ trans('plugins/inventory::inventory.supplier.type.label') }}</div>
                                    <div class="detail-value">{{ $supplier->type?->label() ?: '-' }}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">{{ trans('plugins/inventory::inventory.supplier.tax_code') }}</div>
                                    <div class="detail-value">{{ $supplier->tax_code ?: '-' }}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">{{ trans('plugins/inventory::inventory.supplier.website') }}</div>
                                    <div class="detail-value">
                                        @if($supplier->website)
                                            <a href="{{ $supplier->website }}" target="_blank" rel="noopener">{{ $supplier->website }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">{{ trans('plugins/inventory::inventory.supplier.approval.requires_reapproval') }}</div>
                                    <div class="detail-value">{{ $supplier->requires_reapproval ? trans('core/base::base.yes') : trans('core/base::base.no') }}</div>
                                </div>
                            </div>

                            @if($supplier->note)
                                <div class="detail-item mt-3">
                                    <div class="detail-label">{{ trans('plugins/inventory::inventory.supplier.note') }}</div>
                                    <div class="detail-value">{{ $supplier->note }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="approval-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-user-check" />
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.overview') }}
                            </h2>
                        </div>
                        <div class="panel-body">
                            <div class="info-strip">
                                <div class="info-icon"><x-core::icon name="ti ti-phone" /></div>
                                <div class="info-main">
                                    <div class="info-title">{{ $primaryContact?->name ?: trans('plugins/inventory::inventory.supplier.empty') }}</div>
                                    <div class="info-sub">
                                        {{ $primaryContact?->position ?: '-' }}
                                        @if($primaryContact?->phone) · {{ $primaryContact->phone }} @endif
                                        @if($primaryContact?->email) · {{ $primaryContact->email }} @endif
                                    </div>
                                </div>
                                @if($primaryContact?->is_primary)
                                    <span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.primary') }}</span>
                                @endif
                            </div>

                            <div class="info-strip">
                                <div class="info-icon"><x-core::icon name="ti ti-map-pin" /></div>
                                <div class="info-main">
                                    <div class="info-title">{{ $defaultAddress?->type?->label() ?: trans('plugins/inventory::inventory.supplier.addresses') }}</div>
                                    <div class="info-sub">{{ $defaultAddress?->address ?: trans('plugins/inventory::inventory.supplier.empty') }}</div>
                                </div>
                                @if($defaultAddress?->is_default)
                                    <span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>
                                @endif
                            </div>

                            <div class="info-strip">
                                <div class="info-icon"><x-core::icon name="ti ti-building-bank" /></div>
                                <div class="info-main">
                                    <div class="info-title">{{ $defaultBank?->bank_name ?: trans('plugins/inventory::inventory.supplier.banks') }}</div>
                                    <div class="info-sub">
                                        {{ $defaultBank?->account_name ?: '-' }}
                                        @if($defaultBank?->account_number) · {{ $defaultBank->account_number }} @endif
                                        @if($defaultBank?->branch) · {{ $defaultBank->branch }} @endif
                                    </div>
                                </div>
                                @if($defaultBank?->is_default)
                                    <span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="approval-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-address-book" />
                                {{ trans('plugins/inventory::inventory.supplier.contacts') }}
                            </h2>
                            <span class="badge bg-blue text-blue-fg">{{ number_format($supplier->contacts->count()) }}</span>
                        </div>
                        <div class="panel-body">
                            @forelse($supplier->contacts as $contact)
                                <div class="info-strip">
                                    <div class="info-icon"><x-core::icon name="ti ti-user" /></div>
                                    <div class="info-main">
                                        <div class="info-title">{{ $contact->name }}</div>
                                        <div class="info-sub">
                                            {{ $contact->position ?: '-' }}
                                            @if($contact->phone) · {{ $contact->phone }} @endif
                                            @if($contact->email) · {{ $contact->email }} @endif
                                        </div>
                                    </div>
                                    @if($contact->is_primary)
                                        <span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.primary') }}</span>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">{{ trans('plugins/inventory::inventory.supplier.empty') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="approval-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-history" />
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.timeline') }}
                            </h2>
                        </div>
                        <div class="panel-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-label">{{ trans('plugins/inventory::inventory.supplier.created_by') }}</div>
                                        <div class="timeline-value">{{ $supplier->creator?->name ?: '-' }}</div>
                                        <div class="text-muted small">{{ $supplier->created_at ? BaseHelper::formatDateTime($supplier->created_at) : '-' }}</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-label">{{ trans('plugins/inventory::inventory.supplier.submitted_by') }}</div>
                                        <div class="timeline-value">{{ $supplier->submitter?->name ?: '-' }}</div>
                                        <div class="text-muted small">{{ $supplier->submitted_at ? BaseHelper::formatDateTime($supplier->submitted_at) : '-' }}</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-label">{{ trans('plugins/inventory::inventory.supplier.approved_by') }}</div>
                                        <div class="timeline-value">{{ $supplier->approver?->name ?: '-' }}</div>
                                        <div class="text-muted small">{{ $supplier->approved_at ? BaseHelper::formatDateTime($supplier->approved_at) : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="approval-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-packages" />
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.supplier_products') }}
                            </h2>
                            <span class="badge bg-blue text-blue-fg">{{ number_format($productsCount) }}</span>
                        </div>
                        <div class="panel-body">
                            @forelse($supplier->supplierProducts as $item)
                                @php
                                    $product = $item->product;
                                    $purchasePrice = $item->purchase_price !== null ? number_format((float) $item->purchase_price, 4) : '-';
                                    $productSku = $product?->sku ?: $item->supplier_sku;
                                    $productImage = null;

                                    if ($product?->image) {
                                        try {
                                            $productImage = rv_media()->getImageUrl($product->image, 'thumb');
                                        } catch (\Throwable) {
                                            $productImage = null;
                                        }
                                    }
                                @endphp

                                <div class="product-row">
                                    @if($productImage)
                                        <img src="{{ $productImage }}" alt="{{ $product?->name }}" class="product-icon object-fit-cover">
                                    @else
                                        <div class="product-icon"><x-core::icon name="ti ti-package" /></div>
                                    @endif
                                    <div>
                                        <div class="product-name">{{ $product?->name ?: '#' . $item->product_id }}</div>
                                        <div class="product-meta">
                                            SKU: {{ $productSku ?: '-' }}
                                            @if($item->supplier_sku && $item->supplier_sku !== $productSku)
                                                · {{ trans('plugins/inventory::inventory.supplier.supplier_sku') }}: {{ $item->supplier_sku }}
                                            @endif
                                        </div>
                                        <div class="term-grid">
                                            <div class="term">
                                                <div class="term-label">{{ trans('plugins/inventory::inventory.supplier.purchase_price') }}</div>
                                                <div class="term-value">{{ $purchasePrice }}</div>
                                            </div>
                                            <div class="term">
                                                <div class="term-label">{{ trans('plugins/inventory::inventory.supplier.moq') }}</div>
                                                <div class="term-value">{{ $item->moq ?? '-' }}</div>
                                            </div>
                                            <div class="term">
                                                <div class="term-label">{{ trans('plugins/inventory::inventory.supplier.lead_time_days') }}</div>
                                                <div class="term-value">{{ $item->lead_time_days ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">{{ trans('plugins/inventory::inventory.supplier.empty') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="approval-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-map-2" />
                                {{ trans('plugins/inventory::inventory.supplier.addresses') }}
                            </h2>
                            <span class="badge bg-blue text-blue-fg">{{ number_format($supplier->addresses->count()) }}</span>
                        </div>
                        <div class="panel-body">
                            @forelse($supplier->addresses as $address)
                                <div class="info-strip">
                                    <div class="info-icon"><x-core::icon name="ti ti-map-pin" /></div>
                                    <div class="info-main">
                                        <div class="info-title">{{ $address->type?->label() ?: '-' }}</div>
                                        <div class="info-sub">{{ $address->address }}</div>
                                    </div>
                                    @if($address->is_default)
                                        <span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">{{ trans('plugins/inventory::inventory.supplier.empty') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="approval-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-building-bank" />
                                {{ trans('plugins/inventory::inventory.supplier.banks') }}
                            </h2>
                            <span class="badge bg-blue text-blue-fg">{{ number_format($supplier->banks->count()) }}</span>
                        </div>
                        <div class="panel-body">
                            @forelse($supplier->banks as $bank)
                                <div class="info-strip">
                                    <div class="info-icon"><x-core::icon name="ti ti-credit-card" /></div>
                                    <div class="info-main">
                                        <div class="info-title">{{ $bank->bank_name }}</div>
                                        <div class="info-sub">
                                            {{ $bank->account_name ?: '-' }}
                                            @if($bank->account_number) · {{ $bank->account_number }} @endif
                                            @if($bank->branch) · {{ $bank->branch }} @endif
                                        </div>
                                    </div>
                                    @if($bank->is_default)
                                        <span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">{{ trans('plugins/inventory::inventory.supplier.empty') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="approval-panel decision-panel">
                        <div class="panel-header">
                            <h2 class="panel-title">
                                <x-core::icon name="ti ti-shield-check" />
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.review_decision') }}
                            </h2>
                        </div>
                        <div class="panel-body decision-body">
                            @if($isPendingApproval)
                                <p class="text-muted mb-3">{{ trans('plugins/inventory::inventory.supplier.approval_page.decision_help') }}</p>
                                <label for="supplier-approval-note" class="form-label">{{ trans('plugins/inventory::inventory.supplier.approval.note') }}</label>
                                <textarea id="supplier-approval-note" class="form-control approval-note" placeholder="{{ trans('plugins/inventory::inventory.supplier.approval_page.note_placeholder') }}"></textarea>

                                <form id="supplier-approve-form" method="POST" action="{{ route('inventory.suppliers.approve', $supplier) }}" data-approval-form>
                                    @csrf
                                    <input type="hidden" name="note">
                                </form>

                                <form id="supplier-reject-form" method="POST" action="{{ route('inventory.suppliers.reject', $supplier) }}" data-approval-form data-reject-form>
                                    @csrf
                                    <input type="hidden" name="note">
                                </form>

                                <div class="decision-actions">
                                    <button type="submit" form="supplier-approve-form" class="btn btn-success">
                                        <x-core::icon name="ti ti-check" />
                                        {{ trans('plugins/inventory::inventory.supplier.approval.approve') }}
                                    </button>
                                    <button type="submit" form="supplier-reject-form" class="btn btn-danger">
                                        <x-core::icon name="ti ti-x" />
                                        {{ trans('plugins/inventory::inventory.supplier.approval.reject') }}
                                    </button>
                                </div>
                            @else
                                <div class="empty-state">
                                    <div class="mb-2">{!! $supplier->status?->toHtml() !!}</div>
                                    {{ trans('plugins/inventory::inventory.supplier.approval_page.not_pending') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const note = document.getElementById('supplier-approval-note');
            const rejectConfirmMessage = @js(trans('plugins/inventory::inventory.supplier.approval.reject') . '?');

            document.querySelectorAll('[data-approval-form]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const hiddenNote = form.querySelector('input[name="note"]');

                    if (hiddenNote && note) {
                        hiddenNote.value = note.value;
                    }

                    if (form.hasAttribute('data-reject-form') && ! window.confirm(rejectConfirmMessage)) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
@endsection
