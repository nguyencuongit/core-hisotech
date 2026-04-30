@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <style>
        .warehouse-index-glassline {
            background: #f1f3f5;
            color: #0f1419;
            font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: -1rem;
            min-height: calc(100vh - 56px);
            padding: 24px;
        }

        .warehouse-index-glassline .warehouse-index-heading {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin: 8px 0 18px;
        }

        .warehouse-index-glassline .warehouse-index-kicker {
            color: #4a5568;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.75rem;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .warehouse-index-glassline .warehouse-index-title {
            color: #0f1419;
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.1;
            margin: 4px 0 0;
        }

        .warehouse-index-glassline .card {
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.18);
            border-radius: 16px;
            box-shadow: none;
            overflow: hidden;
        }

        .warehouse-index-glassline .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(74, 85, 104, 0.16);
            padding: 24px 30px;
        }

        .warehouse-index-glassline .table-search-input input {
            border-color: rgba(74, 85, 104, 0.2);
            border-radius: 10px;
            min-height: 44px;
        }

        .warehouse-index-glassline .btn {
            border-radius: 10px;
            font-weight: 600;
            min-height: 44px;
        }

        .warehouse-index-glassline .btn-primary,
        .warehouse-index-glassline .buttons-create {
            background: #2c5ef5;
            border-color: #2c5ef5;
            color: #fff;
        }

        .warehouse-index-glassline .btn:not(.btn-primary):not(.btn-danger) {
            background: #fff;
            border-color: rgba(74, 85, 104, 0.22);
            color: #0f1419;
        }

        .warehouse-index-glassline table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
        }

        .warehouse-index-glassline table.dataTable thead th {
            background: #fff;
            border-bottom: 1px solid rgba(74, 85, 104, 0.16) !important;
            color: #4a5568;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0;
            padding-bottom: 16px;
            padding-top: 16px;
            text-transform: uppercase;
        }

        .warehouse-index-glassline table.dataTable tbody td {
            border-bottom: 1px solid rgba(74, 85, 104, 0.12);
            color: #0f1419;
            font-size: 0.95rem;
            padding-bottom: 20px;
            padding-top: 20px;
            vertical-align: middle;
        }

        .warehouse-index-glassline table.dataTable tbody td a {
            color: #0b5ed7;
            font-weight: 500;
            text-decoration: none;
        }

        .warehouse-index-glassline .badge {
            letter-spacing: 0;
        }

        .warehouse-index-glassline .table-actions {
            display: inline-flex;
            gap: 8px;
            justify-content: center;
            white-space: nowrap;
        }

        .warehouse-index-glassline .table-actions .btn {
            align-items: center;
            display: inline-flex;
            min-height: 36px;
        }

        .warehouse-index-glassline .table-actions .btn-primary {
            background: #0f1419;
            border-color: #0f1419;
        }

        .warehouse-index-glassline .table-actions .btn-danger {
            background: #fff;
            border-color: rgba(180, 35, 24, 0.28);
            color: #b42318;
        }

        .warehouse-index-glassline th.column-key-row_actions,
        .warehouse-index-glassline td.column-key-row_actions {
            background: #fff;
            min-width: 160px;
            position: sticky;
            right: 0;
            z-index: 2;
        }

        .warehouse-index-glassline th.column-key-row_actions {
            z-index: 3;
        }

        @media (max-width: 767.98px) {
            .warehouse-index-glassline {
                margin: -0.75rem;
                padding: 16px;
            }

            .warehouse-index-glassline .warehouse-index-heading {
                display: block;
            }

            .warehouse-index-glassline .warehouse-index-title {
                font-size: 1.75rem;
            }
        }
    </style>

    <div class="warehouse-index-glassline">
        <div class="warehouse-index-heading">
            <div>
                <div class="warehouse-index-kicker">{{ trans('plugins/inventory::inventory.name') }}</div>
                <h1 class="warehouse-index-title">{{ trans('plugins/inventory::inventory.warehouse.name') }}</h1>
            </div>
        </div>

        @include('core/table::base-table')
    </div>
@endsection
