@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $isActive = (int) $warehouse->status === 1;
        $statusLabel = $isActive ? 'Đang hoạt động' : 'Ngừng hoạt động';
        $statusColor = $isActive ? '#166534' : '#b42318';
    @endphp

    <style>
        .warehouse-edit-glassline-page {
            background: #f1f3f5;
            color: #0f1419;
            font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: -1rem;
            min-height: calc(100vh - 56px);
            padding: 24px;
        }

        .warehouse-edit-glassline {
            display: grid;
            gap: 24px;
        }

        .warehouse-edit-header {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
        }

        .warehouse-edit-eyebrow,
        .warehouse-edit-section-heading span,
        .warehouse-edit-summary-label {
            color: #4a5568;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.75rem;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .warehouse-edit-title {
            color: #0f1419;
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.1;
            margin: 4px 0 10px;
        }

        .warehouse-edit-meta {
            align-items: center;
            color: #4a5568;
            display: flex;
            flex-wrap: wrap;
            font-size: 0.95rem;
            gap: 10px;
        }

        .warehouse-edit-status {
            background: var(--warehouse-status-color);
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-weight: 600;
            padding: 7px 12px;
        }

        .warehouse-edit-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .warehouse-btn-primary,
        .warehouse-btn-secondary {
            align-items: center;
            border-radius: 10px;
            display: inline-flex;
            font-weight: 600;
            justify-content: center;
            min-height: 44px;
            padding: 12px 20px;
        }

        .warehouse-btn-primary {
            background: #2c5ef5;
            border: 1px solid #2c5ef5;
            color: #fff;
        }

        .warehouse-btn-primary:hover {
            background: #244bd2;
            border-color: #244bd2;
            color: #fff;
        }

        .warehouse-btn-secondary {
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.22);
            color: #0f1419;
        }

        .warehouse-btn-secondary:hover {
            border-color: rgba(15, 20, 25, 0.38);
            color: #0f1419;
        }

        .warehouse-edit-layout {
            align-items: start;
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 1fr) 340px;
        }

        .warehouse-edit-panel,
        .warehouse-edit-summary {
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.18);
            border-radius: 16px;
            box-shadow: none;
        }

        .warehouse-edit-panel-header,
        .warehouse-edit-summary-header {
            border-bottom: 1px solid rgba(74, 85, 104, 0.14);
            padding: 24px;
        }

        .warehouse-edit-panel-title,
        .warehouse-edit-summary-title {
            color: #0f1419;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: 0;
            margin: 0;
        }

        .warehouse-edit-panel-body,
        .warehouse-edit-summary-body {
            padding: 24px;
        }

        .warehouse-edit-section-heading {
            align-items: baseline;
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .warehouse-edit-section-heading h2 {
            color: #0f1419;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0;
            margin: 0;
        }

        .warehouse-edit-form-grid + .warehouse-edit-form-grid {
            margin-top: 16px;
        }

        .warehouse-edit-form .form-group,
        .warehouse-edit-form .mb-3 {
            margin-bottom: 16px !important;
        }

        .warehouse-edit-form label,
        .warehouse-edit-form .form-label {
            color: #4a5568;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0;
            line-height: 1.35;
            margin-bottom: 8px;
            text-transform: uppercase;
            white-space: normal;
        }

        .warehouse-edit-form .form-control,
        .warehouse-edit-form .form-select,
        .warehouse-edit-form select,
        .warehouse-edit-form textarea {
            border-color: rgba(74, 85, 104, 0.22);
            border-radius: 10px;
            color: #0f1419;
            font-size: 0.95rem;
            min-height: 48px;
        }

        .warehouse-edit-form textarea {
            min-height: 120px;
        }

        .warehouse-edit-form .form-control:focus,
        .warehouse-edit-form .form-select:focus,
        .warehouse-edit-form select:focus,
        .warehouse-edit-form textarea:focus {
            border-color: #2c5ef5;
            box-shadow: 0 0 0 3px rgba(44, 94, 245, 0.12);
        }

        .warehouse-edit-form-actions {
            align-items: center;
            border-top: 1px solid rgba(74, 85, 104, 0.14);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 24px;
        }

        .warehouse-edit-summary-list {
            display: grid;
            gap: 14px;
            margin: 0;
        }

        .warehouse-edit-summary-item {
            background: #f1f3f5;
            border-radius: 10px;
            display: grid;
            gap: 4px;
            padding: 14px;
        }

        .warehouse-edit-summary-value {
            color: #0f1419;
            font-size: 0.95rem;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        @media (max-width: 991.98px) {
            .warehouse-edit-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .warehouse-edit-glassline-page {
                margin: -0.75rem;
                padding: 16px;
            }

            .warehouse-edit-header {
                display: block;
            }

            .warehouse-edit-title {
                font-size: 1.75rem;
            }

            .warehouse-edit-actions,
            .warehouse-edit-form-actions {
                justify-content: stretch;
                margin-top: 16px;
            }

            .warehouse-btn-primary,
            .warehouse-btn-secondary {
                flex: 1 1 auto;
            }
        }
    </style>

    <div class="page-body warehouse-edit-glassline-page">
        <div class="container-fluid warehouse-edit-glassline">
            <div class="warehouse-edit-header">
                <div>
                    <div class="warehouse-edit-eyebrow">{{ trans('plugins/inventory::inventory.warehouse.name') }}</div>
                    <h1 class="warehouse-edit-title">{{ $warehouse->name }}</h1>
                    <div class="warehouse-edit-meta">
                        <span>{{ $warehouse->code }}</span>
                        @if($warehouse->type)
                            <span>/</span>
                            <span>{{ $warehouse->type }}</span>
                        @endif
                        <span>/</span>
                        <span class="warehouse-edit-status" style="--warehouse-status-color: {{ $statusColor }}">{{ $statusLabel }}</span>
                    </div>
                </div>

                <div class="warehouse-edit-actions">
                    <a class="btn warehouse-btn-secondary" href="{{ route('inventory.warehouse.index') }}">Quay lại</a>
                    <a class="btn warehouse-btn-secondary" href="{{ route('inventory.warehouse.show', $warehouse) }}">Xem kho</a>
                </div>
            </div>

            <div class="warehouse-edit-layout">
                <section class="warehouse-edit-panel">
                    <div class="warehouse-edit-panel-header">
                        <h2 class="warehouse-edit-panel-title">Cấu hình kho</h2>
                    </div>
                    <div class="warehouse-edit-panel-body">
                        {!! $form->renderForm([], true, true, false) !!}

                        <div class="warehouse-edit-form-actions">
                            <a class="btn warehouse-btn-secondary" href="{{ route('inventory.warehouse.index') }}">Hủy bỏ</a>
                            <button class="btn warehouse-btn-primary" type="submit">Lưu</button>
                        </div>

                        {!! Form::close() !!}
                    </div>
                </section>

                <aside class="warehouse-edit-summary">
                    <div class="warehouse-edit-summary-header">
                        <h2 class="warehouse-edit-summary-title">Tổng quan</h2>
                    </div>
                    <div class="warehouse-edit-summary-body">
                        <div class="warehouse-edit-summary-list">
                            <div class="warehouse-edit-summary-item">
                                <div class="warehouse-edit-summary-label">Mã kho</div>
                                <div class="warehouse-edit-summary-value">{{ $warehouse->code }}</div>
                            </div>
                            <div class="warehouse-edit-summary-item">
                                <div class="warehouse-edit-summary-label">Kiểu kho</div>
                                <div class="warehouse-edit-summary-value">{{ $warehouse->type ?: 'Chưa thiết lập' }}</div>
                            </div>
                            <div class="warehouse-edit-summary-item">
                                <div class="warehouse-edit-summary-label">Số điện thoại</div>
                                <div class="warehouse-edit-summary-value">{{ $warehouse->phone }}</div>
                            </div>
                            <div class="warehouse-edit-summary-item">
                                <div class="warehouse-edit-summary-label">Email</div>
                                <div class="warehouse-edit-summary-value">{{ $warehouse->email ?: 'Chưa thiết lập' }}</div>
                            </div>
                            <div class="warehouse-edit-summary-item">
                                <div class="warehouse-edit-summary-label">Địa chỉ</div>
                                <div class="warehouse-edit-summary-value">{{ $warehouse->address }}</div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
