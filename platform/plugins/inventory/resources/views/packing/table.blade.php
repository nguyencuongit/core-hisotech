@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <style>
        .packing-index-glassline {
            background: #f1f3f5;
            color: #0f1419;
            font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: -1rem;
            min-height: calc(100vh - 56px);
            padding: 24px;
        }

        .packing-index-glassline .packing-index-heading {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin: 8px 0 18px;
        }

        .packing-index-glassline .packing-index-kicker,
        .packing-index-glassline table.dataTable thead th,
        .packing-index-glassline .packing-total-grid span,
        .packing-index-glassline .packing-timeline span {
            color: #4a5568;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .packing-index-glassline .packing-index-title {
            color: #0f1419;
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.1;
            margin: 4px 0 0;
        }

        .packing-index-glassline .packing-index-meta {
            color: #4a5568;
            font-size: 0.95rem;
            margin-top: 6px;
        }

        .packing-index-glassline .card {
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.18);
            border-radius: 16px;
            box-shadow: none;
            overflow: hidden;
        }

        .packing-index-glassline .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(74, 85, 104, 0.16);
            padding: 24px 30px;
        }

        .packing-index-glassline .table-search-input input,
        .packing-index-glassline .form-control,
        .packing-index-glassline .form-select {
            border-color: rgba(74, 85, 104, 0.2);
            border-radius: 10px;
            min-height: 44px;
        }

        .packing-index-glassline .btn {
            border-radius: 10px;
            font-weight: 600;
            min-height: 44px;
        }

        .packing-index-glassline .btn-primary,
        .packing-index-glassline .buttons-create {
            background: #2c5ef5;
            border-color: #2c5ef5;
            color: #fff;
        }

        .packing-index-glassline .btn:not(.btn-primary):not(.btn-danger) {
            background: #fff;
            border-color: rgba(74, 85, 104, 0.22);
            color: #0f1419;
        }

        .packing-index-glassline table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
            min-width: 1120px;
        }

        .packing-index-glassline table.dataTable thead th {
            background: #fff;
            border-bottom: 1px solid rgba(74, 85, 104, 0.16) !important;
            padding-bottom: 16px;
            padding-top: 16px;
        }

        .packing-index-glassline table.dataTable tbody td {
            border-bottom: 1px solid rgba(74, 85, 104, 0.12);
            color: #0f1419;
            font-size: 0.95rem;
            padding-bottom: 18px;
            padding-top: 18px;
            vertical-align: middle;
        }

        .packing-index-glassline .packing-cell-main {
            display: grid;
            gap: 5px;
            min-width: 0;
        }

        .packing-index-glassline .packing-code-link,
        .packing-index-glassline .packing-export-code,
        .packing-index-glassline .packing-warehouse-name {
            color: #0f1419;
            font-weight: 600;
            letter-spacing: 0;
            text-decoration: none;
        }

        .packing-index-glassline .packing-code-link:hover {
            color: #2c5ef5;
        }

        .packing-index-glassline .packing-cell-meta,
        .packing-index-glassline .packing-cell-note,
        .packing-index-glassline .packing-muted {
            color: #4a5568;
            font-size: 0.85rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .packing-index-glassline .packing-cell-note {
            background: #f1f3f5;
            border-radius: 10px;
            padding: 6px 8px;
        }

        .packing-index-glassline .packing-total-grid {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(2, minmax(74px, 1fr));
            max-width: 260px;
        }

        .packing-index-glassline .packing-total-grid div {
            background: #f1f3f5;
            border: 1px solid rgba(74, 85, 104, 0.14);
            border-radius: 10px;
            padding: 8px 10px;
        }

        .packing-index-glassline .packing-total-grid strong,
        .packing-index-glassline .packing-timeline strong {
            color: #0f1419;
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 2px;
        }

        .packing-index-glassline .packing-timeline {
            display: grid;
            gap: 8px;
            min-width: 180px;
        }

        .packing-index-glassline .packing-status-badge {
            border-radius: 999px;
            display: inline-flex;
            font-weight: 600;
            justify-content: center;
            min-width: 112px;
            padding: 8px 12px;
            white-space: nowrap;
        }

        .packing-index-glassline .packing-status-badge.is-draft {
            background: #f1f3f5;
            color: #4a5568;
        }

        .packing-index-glassline .packing-status-badge.is-packing {
            background: rgba(44, 94, 245, 0.12);
            color: #2c5ef5;
        }

        .packing-index-glassline .packing-status-badge.is-packed {
            background: rgba(15, 20, 25, 0.08);
            color: #0f1419;
        }

        .packing-index-glassline .packing-status-badge.is-cancelled {
            background: rgba(180, 35, 24, 0.1);
            color: #b42318;
        }

        .packing-index-glassline .table-actions {
            display: inline-flex;
            gap: 8px;
            justify-content: center;
            white-space: nowrap;
        }

        .packing-index-glassline .table-actions .btn {
            align-items: center;
            display: inline-flex;
            min-height: 36px;
        }

        .packing-index-glassline .table-actions .btn-primary {
            background: #0f1419;
            border-color: #0f1419;
        }

        .packing-index-glassline .table-actions .btn-danger {
            background: #fff;
            border-color: rgba(180, 35, 24, 0.28);
            color: #b42318;
        }

        .packing-index-glassline th.column-key-row_actions,
        .packing-index-glassline td.column-key-row_actions {
            background: #fff;
            min-width: 132px;
            position: sticky;
            right: 0;
            z-index: 2;
        }

        .packing-index-glassline th.column-key-row_actions {
            z-index: 3;
        }

        @media (max-width: 767.98px) {
            .packing-index-glassline {
                margin: -0.75rem;
                padding: 16px;
            }

            .packing-index-glassline .packing-index-heading {
                display: block;
            }

            .packing-index-glassline .packing-index-title {
                font-size: 1.75rem;
            }
        }
    </style>

    <div class="packing-index-glassline">
        <div class="packing-index-heading">
            <div>
                <div class="packing-index-kicker">Inventory</div>
                <h1 class="packing-index-title">{{ trans('plugins/inventory::inventory.packing.name') }}</h1>
                <div class="packing-index-meta">Theo dõi phiếu xuất, kiện hàng, số lượng và trạng thái đóng gói.</div>
            </div>
        </div>

        @include('core/table::base-table')
    </div>
@endsection
