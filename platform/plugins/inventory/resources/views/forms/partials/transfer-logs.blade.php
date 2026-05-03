@if($logs->isNotEmpty())
    <div class="transfer-log-card">
        <div class="transfer-log-header">
            <span>LOG</span>
            <h2>Lịch sử thao tác</h2>
        </div>

        <div class="transfer-log-list">
            @foreach($logs as $log)
                <div class="transfer-log-item">
                    <div>
                        <strong>{{ strtoupper($log->action) }}</strong>
                        <span>{{ $log->old_status ?: '-' }} -> {{ $log->new_status ?: '-' }}</span>
                    </div>
                    <div>
                        <span>{{ $log->user?->name ?: ('User #' . ($log->created_by ?: '-')) }}</span>
                        <time>{{ optional($log->created_at)->format('d/m/Y H:i') }}</time>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .transfer-log-card {
            background: #FFFFFF;
            border: 1px solid #D9DEE6;
            border-radius: 16px;
            margin-top: 16px;
            overflow: hidden;
        }

        .transfer-log-header {
            border-bottom: 1px solid #D9DEE6;
            padding: 20px 24px;
        }

        .transfer-log-header span,
        .transfer-log-item span,
        .transfer-log-item time {
            color: #4A5568;
            font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .transfer-log-header h2 {
            color: #0F1419;
            font-size: 1.25rem;
            font-weight: 650;
            letter-spacing: 0;
            margin: 4px 0 0;
        }

        .transfer-log-list {
            display: grid;
        }

        .transfer-log-item {
            align-items: center;
            border-bottom: 1px solid #EEF1F4;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 14px 24px;
        }

        .transfer-log-item:last-child {
            border-bottom: 0;
        }

        .transfer-log-item strong {
            color: #0F1419;
            display: block;
            font-weight: 750;
        }

        .transfer-log-item > div:last-child {
            text-align: right;
        }

        .transfer-log-item time {
            display: block;
            margin-top: 3px;
        }

        @media (max-width: 575.98px) {
            .transfer-log-item {
                align-items: flex-start;
                display: grid;
            }

            .transfer-log-item > div:last-child {
                text-align: left;
            }
        }
    </style>
@endif
