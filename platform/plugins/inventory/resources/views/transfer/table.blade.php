@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <style>
        .transfer-index-glassline {
            background: #f1f3f5;
            color: #0f1419;
            font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: -1rem;
            min-height: calc(100vh - 56px);
            padding: 24px;
        }

        .transfer-index-glassline .transfer-index-heading {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin: 8px 0 18px;
        }

        .transfer-index-glassline .transfer-index-kicker,
        .transfer-index-glassline table.dataTable thead th,
        .transfer-index-glassline .transfer-metric-grid span,
        .transfer-index-glassline .transfer-documents span,
        .transfer-index-glassline .transfer-route span,
        .transfer-index-glassline .transfer-in-transit span,
        .transfer-index-glassline .transfer-workflow-step span {
            color: #4a5568;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .transfer-index-glassline .transfer-index-title {
            color: #0f1419;
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.1;
            margin: 4px 0 0;
        }

        .transfer-index-glassline .transfer-index-meta {
            color: #4a5568;
            font-size: 0.95rem;
            line-height: 1.55;
            margin-top: 6px;
            max-width: 760px;
        }

        .transfer-index-glassline .transfer-workflow-strip {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(4, minmax(140px, 1fr));
            margin-bottom: 18px;
        }

        .transfer-index-glassline .transfer-workflow-step {
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.18);
            border-radius: 16px;
            padding: 14px 16px;
        }

        .transfer-index-glassline .transfer-workflow-step strong {
            color: #0f1419;
            display: block;
            font-size: 0.98rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .transfer-index-glassline .card {
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.18);
            border-radius: 16px;
            box-shadow: none;
            overflow: hidden;
        }

        .transfer-index-glassline .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(74, 85, 104, 0.16);
            padding: 24px 30px;
        }

        .transfer-index-glassline .table-search-input input,
        .transfer-index-glassline .form-control,
        .transfer-index-glassline .form-select {
            border-color: rgba(74, 85, 104, 0.2);
            border-radius: 10px;
            min-height: 44px;
        }

        .transfer-index-glassline .btn {
            border-radius: 10px;
            font-weight: 600;
            min-height: 44px;
        }

        .transfer-index-glassline .btn-primary,
        .transfer-index-glassline .buttons-create {
            background: #2c5ef5;
            border-color: #2c5ef5;
            color: #fff;
        }

        .transfer-index-glassline .btn:not(.btn-primary):not(.btn-danger) {
            background: #fff;
            border-color: rgba(74, 85, 104, 0.22);
            color: #0f1419;
        }

        .transfer-index-glassline table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
            min-width: 1320px;
        }

        .transfer-index-glassline table.dataTable thead th {
            background: #fff;
            border-bottom: 1px solid rgba(74, 85, 104, 0.16) !important;
            padding-bottom: 16px;
            padding-top: 16px;
        }

        .transfer-index-glassline table.dataTable tbody td {
            border-bottom: 1px solid rgba(74, 85, 104, 0.12);
            color: #0f1419;
            font-size: 0.95rem;
            padding-bottom: 18px;
            padding-top: 18px;
            vertical-align: middle;
        }

        .transfer-index-glassline .transfer-cell-main {
            display: grid;
            gap: 5px;
            min-width: 210px;
        }

        .transfer-index-glassline .transfer-code-link,
        .transfer-index-glassline .transfer-route strong,
        .transfer-index-glassline .transfer-documents strong {
            color: #0f1419;
            font-weight: 600;
            letter-spacing: 0;
            text-decoration: none;
        }

        .transfer-index-glassline .transfer-code-link:hover {
            color: #2c5ef5;
        }

        .transfer-index-glassline .transfer-cell-meta,
        .transfer-index-glassline .transfer-cell-note,
        .transfer-index-glassline .transfer-route em {
            color: #4a5568;
            font-size: 0.85rem;
            font-style: normal;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .transfer-index-glassline .transfer-cell-note {
            background: #f1f3f5;
            border-radius: 10px;
            padding: 6px 8px;
        }

        .transfer-index-glassline .transfer-route {
            align-items: center;
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(150px, 1fr) 24px minmax(150px, 1fr);
            min-width: 360px;
        }

        .transfer-index-glassline .transfer-route i {
            align-items: center;
            background: #f1f3f5;
            border-radius: 999px;
            color: #4a5568;
            display: inline-flex;
            height: 24px;
            justify-content: center;
            width: 24px;
        }

        .transfer-index-glassline .transfer-route strong,
        .transfer-index-glassline .transfer-route em {
            display: block;
        }

        .transfer-index-glassline .transfer-metric-grid {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(3, minmax(72px, 1fr));
            min-width: 260px;
        }

        .transfer-index-glassline .transfer-metric-grid div,
        .transfer-index-glassline .transfer-documents div {
            background: #f1f3f5;
            border: 1px solid rgba(74, 85, 104, 0.14);
            border-radius: 10px;
            padding: 8px 10px;
        }

        .transfer-index-glassline .transfer-metric-grid strong,
        .transfer-index-glassline .transfer-documents strong,
        .transfer-index-glassline .transfer-in-transit strong {
            color: #0f1419;
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 2px;
            overflow-wrap: anywhere;
        }

        .transfer-index-glassline .transfer-in-transit {
            background: rgba(44, 94, 245, 0.1);
            border-radius: 12px;
            color: #2c5ef5;
            display: inline-grid;
            justify-items: center;
            min-width: 104px;
            padding: 10px 12px;
        }

        .transfer-index-glassline .transfer-in-transit strong {
            color: #2c5ef5;
            font-size: 1rem;
        }

        .transfer-index-glassline .transfer-documents {
            display: grid;
            gap: 8px;
            min-width: 210px;
        }

        .transfer-index-glassline .transfer-status-badge {
            border-radius: 999px;
            display: inline-flex;
            font-weight: 600;
            justify-content: center;
            min-width: 118px;
            padding: 8px 12px;
            white-space: nowrap;
        }

        .transfer-index-glassline .transfer-status-badge.is-draft {
            background: #f1f3f5;
            color: #4a5568;
        }

        .transfer-index-glassline .transfer-status-badge.is-confirmed,
        .transfer-index-glassline .transfer-status-badge.is-moving {
            background: rgba(44, 94, 245, 0.12);
            color: #2c5ef5;
        }

        .transfer-index-glassline .transfer-status-badge.is-completed {
            background: rgba(15, 20, 25, 0.08);
            color: #0f1419;
        }

        .transfer-index-glassline .transfer-status-badge.is-cancelled {
            background: rgba(180, 35, 24, 0.1);
            color: #b42318;
        }

        .transfer-index-glassline .table-actions {
            display: inline-flex;
            gap: 8px;
            justify-content: center;
            white-space: nowrap;
        }

        .transfer-index-glassline .table-actions .btn {
            align-items: center;
            display: inline-flex;
            min-height: 36px;
        }

        .transfer-index-glassline .table-actions .btn-primary {
            background: #0f1419;
            border-color: #0f1419;
        }

        .transfer-index-glassline .table-actions .btn-danger {
            background: #fff;
            border-color: rgba(180, 35, 24, 0.28);
            color: #b42318;
        }

        .transfer-index-glassline th.column-key-row_actions,
        .transfer-index-glassline td.column-key-row_actions {
            background: #fff;
            min-width: 132px;
            position: sticky;
            right: 0;
            z-index: 2;
        }

        .transfer-index-glassline th.column-key-row_actions {
            z-index: 3;
        }

        @media (max-width: 991.98px) {
            .transfer-index-glassline .transfer-workflow-strip {
                grid-template-columns: repeat(2, minmax(140px, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .transfer-index-glassline {
                margin: -0.75rem;
                padding: 16px;
            }

            .transfer-index-glassline .transfer-index-heading {
                display: block;
            }

            .transfer-index-glassline .transfer-index-title {
                font-size: 1.75rem;
            }

            .transfer-index-glassline .transfer-workflow-strip {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="transfer-index-glassline">
        <div class="transfer-index-heading">
            <div>
                <div class="transfer-index-kicker">Inventory</div>
                <h1 class="transfer-index-title">{{ trans('plugins/inventory::inventory.transfer.name') }}</h1>
                <div class="transfer-index-meta">Theo dõi tuyến chuyển kho, chứng từ xuất nhập nội bộ, số lượng đang chuyển và trạng thái nhận hàng.</div>
            </div>
        </div>

        <div class="transfer-workflow-strip">
            <div class="transfer-workflow-step">
                <span>Bước 1</span>
                <strong>Tạo phiếu</strong>
            </div>
            <div class="transfer-workflow-step">
                <span>Bước 2</span>
                <strong>Xác nhận</strong>
            </div>
            <div class="transfer-workflow-step">
                <span>Bước 3</span>
                <strong>Xuất chuyển</strong>
            </div>
            <div class="transfer-workflow-step">
                <span>Bước 4</span>
                <strong>Nhập kho đích</strong>
            </div>
        </div>

        @include('core/table::base-table')
    </div>
@endsection
