@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <style>
        .warehouse-template-glassline-page {
            background: #f1f3f5;
            color: #0f1419;
            font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: -1rem;
            min-height: calc(100vh - 56px);
            padding: 24px;
        }

        .warehouse-template-glassline {
            display: grid;
            gap: 24px;
        }

        .warehouse-template-header {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
        }

        .warehouse-template-eyebrow,
        .warehouse-template-card-code,
        .warehouse-template-node-code,
        .warehouse-template-stat-label {
            color: #4a5568;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.75rem;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .warehouse-template-title {
            color: #0f1419;
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.1;
            margin: 4px 0 0;
        }

        .warehouse-template-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .warehouse-template-btn {
            align-items: center;
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.22);
            border-radius: 10px;
            color: #0f1419;
            display: inline-flex;
            font-weight: 600;
            justify-content: center;
            min-height: 44px;
            padding: 12px 20px;
            text-decoration: none;
        }

        .warehouse-template-btn:hover {
            border-color: rgba(15, 20, 25, 0.38);
            color: #0f1419;
        }

        .warehouse-template-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .warehouse-template-card {
            background: #fff;
            border: 1px solid rgba(74, 85, 104, 0.18);
            border-radius: 16px;
            display: grid;
            gap: 20px;
            padding: 24px;
        }

        .warehouse-template-card-header {
            display: grid;
            gap: 8px;
        }

        .warehouse-template-card-title {
            color: #0f1419;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.2;
            margin: 0;
        }

        .warehouse-template-card-description {
            color: #4a5568;
            font-size: 0.95rem;
            line-height: 1.55;
            margin: 0;
        }

        .warehouse-template-stats {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .warehouse-template-stat {
            background: #f1f3f5;
            border-radius: 10px;
            padding: 12px;
        }

        .warehouse-template-stat-value {
            color: #0f1419;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .warehouse-template-preview {
            display: grid;
            gap: 10px;
        }

        .warehouse-template-node {
            border: 1px solid rgba(74, 85, 104, 0.16);
            border-radius: 10px;
            display: grid;
            gap: 4px;
            padding: 12px;
        }

        .warehouse-template-node-name {
            color: #0f1419;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .warehouse-template-node-children {
            color: #4a5568;
            font-size: 0.85rem;
        }

        @media (max-width: 1199.98px) {
            .warehouse-template-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .warehouse-template-glassline-page {
                margin: -0.75rem;
                padding: 16px;
            }

            .warehouse-template-header {
                display: block;
            }

            .warehouse-template-title {
                font-size: 1.75rem;
            }

            .warehouse-template-actions {
                justify-content: stretch;
                margin-top: 16px;
            }

            .warehouse-template-btn {
                flex: 1 1 auto;
            }

            .warehouse-template-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-body warehouse-template-glassline-page">
        <div class="container-fluid warehouse-template-glassline">
            <div class="warehouse-template-header">
                <div>
                    <div class="warehouse-template-eyebrow">{{ trans('plugins/inventory::inventory.warehouse.name') }}</div>
                    <h1 class="warehouse-template-title">Mẫu kho</h1>
                </div>

                <div class="warehouse-template-actions">
                    <a class="warehouse-template-btn" href="{{ route('inventory.warehouse.create') }}">Tạo kho</a>
                    <a class="warehouse-template-btn" href="{{ route('inventory.warehouse.index') }}">Danh sách kho</a>
                </div>
            </div>

            <div class="warehouse-template-grid">
                @foreach($templates as $code => $template)
                    @php
                        $preview = $template['preview'] ?? [];
                        $nestedCount = collect($preview)->sum(fn (array $item) => count($item['children'] ?? []));
                    @endphp

                    <article class="warehouse-template-card">
                        <div class="warehouse-template-card-header">
                            <div class="warehouse-template-card-code">{{ $code }}</div>
                            <h2 class="warehouse-template-card-title">{{ $template['name'] }}</h2>
                            <p class="warehouse-template-card-description">{{ $template['description'] }}</p>
                        </div>

                        <div class="warehouse-template-stats">
                            <div class="warehouse-template-stat">
                                <div class="warehouse-template-stat-label">Số khu</div>
                                <div class="warehouse-template-stat-value">{{ count($preview) }}</div>
                            </div>
                            <div class="warehouse-template-stat">
                                <div class="warehouse-template-stat-label">Sơ đồ</div>
                                <div class="warehouse-template-stat-value">{{ $template['default_map_blueprint'] ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="warehouse-template-preview">
                            @foreach(array_slice($preview, 0, 6) as $item)
                                <div class="warehouse-template-node">
                                    <div class="warehouse-template-node-code">{{ $item['code'] ?? '' }}</div>
                                    <div class="warehouse-template-node-name">{{ $item['name'] ?? '' }}</div>
                                    @if(! empty($item['children']))
                                        <div class="warehouse-template-node-children">
                                            {{ count($item['children']) }} vị trí con
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if($nestedCount > 0)
                                <div class="warehouse-template-node">
                                    <div class="warehouse-template-node-code">Nested</div>
                                    <div class="warehouse-template-node-name">{{ $nestedCount }} vị trí con trong cấu trúc</div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection
