@php
    $setupByKey = collect($setupCheckpoints)->keyBy('key');
    $setupSteps = [
        'settings' => [
            'number' => '01',
            'title' => 'Cài đặt kho',
            'description' => 'Xác nhận kiểu vận hành, pallet, QC, batch và serial trước khi dựng cấu trúc.',
        ],
        'locations' => [
            'number' => '02',
            'title' => 'Cây vị trí',
            'description' => 'Tạo khu nhận hàng, khu lưu trữ, rack, tầng và vị trí chứa hàng.',
        ],
        'maps' => [
            'number' => '03',
            'title' => 'Sơ đồ kho',
            'description' => 'Sinh bản đồ trực quan và gắn từng vùng với location vận hành.',
        ],
        'pallets' => [
            'number' => '04',
            'title' => 'Pallet',
            'description' => 'Bật khi kho vận hành theo pallet, xe nâng hoặc slot pallet.',
            'optional' => true,
        ],
    ];
    $requiredStepKeys = ['settings', 'locations', 'maps'];
    $doneRequiredSteps = collect($requiredStepKeys)->filter(fn (string $key) => (bool) data_get($setupByKey, "$key.done"))->count();
    $recommendedTemplateCode = array_key_first($templates);
    $recommendedTemplate = $recommendedTemplateCode ? $templates[$recommendedTemplateCode] : null;
@endphp

<style>
    .warehouse-setup-glassline {
        background: #f1f3f5;
        color: #0f1419;
        font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        margin: -1rem;
        min-height: calc(100vh - 56px);
        padding: 24px;
    }

    .warehouse-setup-shell {
        display: grid;
        gap: 24px;
    }

    .warehouse-setup-header {
        align-items: flex-start;
        display: flex;
        gap: 16px;
        justify-content: space-between;
    }

    .warehouse-setup-kicker,
    .warehouse-setup-label,
    .warehouse-setup-step-number,
    .warehouse-template-code {
        color: #4a5568;
        font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
        font-size: 0.75rem;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .warehouse-setup-title {
        color: #0f1419;
        font-size: 2.25rem;
        font-weight: 600;
        letter-spacing: 0;
        line-height: 1.1;
        margin: 4px 0 10px;
    }

    .warehouse-setup-meta {
        align-items: center;
        color: #4a5568;
        display: flex;
        flex-wrap: wrap;
        font-size: 0.95rem;
        gap: 10px;
    }

    .warehouse-setup-status {
        background: #b45309;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-weight: 600;
        padding: 7px 12px;
    }

    .warehouse-setup-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .warehouse-setup-btn-primary,
    .warehouse-setup-btn-secondary {
        align-items: center;
        border-radius: 10px;
        display: inline-flex;
        font-weight: 600;
        justify-content: center;
        min-height: 44px;
        padding: 12px 20px;
        text-decoration: none;
    }

    .warehouse-setup-btn-primary {
        background: #2c5ef5;
        border: 1px solid #2c5ef5;
        color: #fff;
    }

    .warehouse-setup-btn-primary:hover {
        background: #244bd2;
        border-color: #244bd2;
        color: #fff;
    }

    .warehouse-setup-btn-secondary {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.22);
        color: #0f1419;
    }

    .warehouse-setup-btn-secondary:hover {
        border-color: rgba(15, 20, 25, 0.38);
        color: #0f1419;
    }

    .warehouse-setup-layout {
        align-items: start;
        display: grid;
        gap: 24px;
        grid-template-columns: minmax(0, 1fr) 360px;
    }

    .warehouse-setup-card {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.18);
        border-radius: 16px;
        box-shadow: none;
        overflow: hidden;
    }

    .warehouse-setup-card-header {
        border-bottom: 1px solid rgba(74, 85, 104, 0.14);
        padding: 24px;
    }

    .warehouse-setup-card-body {
        padding: 24px;
    }

    .warehouse-setup-card-title {
        color: #0f1419;
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: 0;
        margin: 0;
    }

    .warehouse-setup-card-hint {
        color: #4a5568;
        font-size: 0.95rem;
        line-height: 1.55;
        margin: 6px 0 0;
    }

    .warehouse-setup-steps {
        display: grid;
        gap: 12px;
    }

    .warehouse-setup-step {
        align-items: flex-start;
        border: 1px solid rgba(74, 85, 104, 0.16);
        border-radius: 10px;
        display: grid;
        gap: 14px;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        padding: 16px;
    }

    .warehouse-setup-step.is-done {
        background: #f7fbf8;
        border-color: rgba(22, 101, 52, 0.24);
    }

    .warehouse-setup-step-number {
        align-items: center;
        background: #f1f3f5;
        border-radius: 10px;
        display: inline-flex;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .warehouse-setup-step-title {
        color: #0f1419;
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .warehouse-setup-step-description {
        color: #4a5568;
        font-size: 0.95rem;
        line-height: 1.55;
        margin: 4px 0 0;
    }

    .warehouse-setup-chip {
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 7px 10px;
        white-space: nowrap;
    }

    .warehouse-setup-chip.is-done {
        background: #166534;
        color: #fff;
    }

    .warehouse-setup-chip.is-open {
        background: #f1f3f5;
        color: #4a5568;
    }

    .warehouse-template-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .warehouse-template-card {
        border: 1px solid rgba(74, 85, 104, 0.16);
        border-radius: 10px;
        display: grid;
        gap: 14px;
        padding: 16px;
    }

    .warehouse-template-title {
        color: #0f1419;
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .warehouse-template-description {
        color: #4a5568;
        font-size: 0.95rem;
        line-height: 1.55;
        margin: 0;
    }

    .warehouse-template-preview {
        display: grid;
        gap: 8px;
    }

    .warehouse-template-node {
        align-items: center;
        background: #f1f3f5;
        border-radius: 8px;
        color: #0f1419;
        display: flex;
        font-size: 0.9rem;
        justify-content: space-between;
        padding: 8px 10px;
    }

    .warehouse-setup-side-list {
        display: grid;
        gap: 12px;
    }

    .warehouse-setup-side-item {
        background: #f1f3f5;
        border-radius: 10px;
        display: grid;
        gap: 4px;
        padding: 14px;
    }

    .warehouse-setup-side-value {
        color: #0f1419;
        font-size: 1rem;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    @media (max-width: 1199.98px) {
        .warehouse-template-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .warehouse-setup-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .warehouse-setup-glassline {
            margin: -0.75rem;
            padding: 16px;
        }

        .warehouse-setup-header {
            display: block;
        }

        .warehouse-setup-title {
            font-size: 1.75rem;
        }

        .warehouse-setup-actions {
            justify-content: stretch;
            margin-top: 16px;
        }

        .warehouse-setup-btn-primary,
        .warehouse-setup-btn-secondary {
            flex: 1 1 auto;
        }

        .warehouse-setup-step {
            grid-template-columns: 42px minmax(0, 1fr);
        }

        .warehouse-setup-step > .warehouse-setup-chip {
            grid-column: 1 / -1;
            justify-content: center;
        }
    }
</style>

<div class="warehouse-setup-glassline">
    <div class="container-fluid warehouse-setup-shell">
        <div class="warehouse-setup-header">
            <div>
                <div class="warehouse-setup-kicker">Quản lý kho</div>
                <h1 class="warehouse-setup-title">{{ $warehouse->name }}</h1>
                <div class="warehouse-setup-meta">
                    <span>{{ $warehouse->code }}</span>
                    @if($warehouse->address)
                        <span>/</span>
                        <span>{{ $warehouse->address }}</span>
                    @endif
                    <span>/</span>
                    <span class="warehouse-setup-status">Cần setup thêm</span>
                </div>
            </div>

            <div class="warehouse-setup-actions">
                <a class="warehouse-setup-btn-secondary" href="{{ route('inventory.warehouse.index') }}">Quay lại</a>
                @if(auth()->user()?->hasPermission('warehouse.edit'))
                    <a class="warehouse-setup-btn-secondary" href="{{ route('inventory.warehouse.edit', $warehouse) }}">Chỉnh sửa kho</a>
                @endif
            </div>
        </div>

        <div class="warehouse-setup-layout">
            <main class="warehouse-setup-card">
                <div class="warehouse-setup-card-header">
                    <h2 class="warehouse-setup-card-title">Thiết lập ban đầu</h2>
                    <p class="warehouse-setup-card-hint">Kho này chưa đủ cây vị trí và sơ đồ để vận hành. Hoàn tất các bước dưới đây hoặc áp dụng nhanh một mẫu kho có sẵn.</p>
                </div>
                <div class="warehouse-setup-card-body">
                    <div class="warehouse-setup-steps">
                        @foreach($setupSteps as $key => $step)
                            @php
                                $isDone = (bool) data_get($setupByKey, "$key.done");
                            @endphp
                            <div class="warehouse-setup-step {{ $isDone ? 'is-done' : '' }}">
                                <div class="warehouse-setup-step-number">{{ $step['number'] }}</div>
                                <div>
                                    <h3 class="warehouse-setup-step-title">{{ $step['title'] }}</h3>
                                    <p class="warehouse-setup-step-description">{{ $step['description'] }}</p>
                                </div>
                                <span class="warehouse-setup-chip {{ $isDone ? 'is-done' : 'is-open' }}">
                                    {{ $isDone ? 'Xong' : (($step['optional'] ?? false) ? 'Tùy chọn' : 'Chưa xong') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </main>

            <aside class="warehouse-setup-card">
                <div class="warehouse-setup-card-header">
                    <h2 class="warehouse-setup-card-title">Trạng thái</h2>
                </div>
                <div class="warehouse-setup-card-body">
                    <div class="warehouse-setup-side-list">
                        <div class="warehouse-setup-side-item">
                            <div class="warehouse-setup-label">Tiến độ</div>
                            <div class="warehouse-setup-side-value">{{ $doneRequiredSteps }}/{{ count($requiredStepKeys) }} bước chính</div>
                        </div>
                        <div class="warehouse-setup-side-item">
                            <div class="warehouse-setup-label">Location</div>
                            <div class="warehouse-setup-side-value">{{ number_format((int) $warehouse->locations_count) }}</div>
                        </div>
                        <div class="warehouse-setup-side-item">
                            <div class="warehouse-setup-label">Sơ đồ</div>
                            <div class="warehouse-setup-side-value">{{ number_format($warehouse->maps->count()) }}</div>
                        </div>
                        <div class="warehouse-setup-side-item">
                            <div class="warehouse-setup-label">Pallet</div>
                            <div class="warehouse-setup-side-value">{{ number_format($pallets->count()) }}</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <section class="warehouse-setup-card">
            <div class="warehouse-setup-card-header">
                <h2 class="warehouse-setup-card-title">Mẫu kho có sẵn</h2>
                <p class="warehouse-setup-card-hint">Áp dụng mẫu sẽ sinh nhanh cây vị trí và sơ đồ mặc định cho kho này.</p>
            </div>
            <div class="warehouse-setup-card-body">
                @if($recommendedTemplateCode && auth()->user()?->hasPermission('warehouse.locations.manage'))
                    <form method="POST" action="{{ route('inventory.warehouse.templates.apply', $warehouse) }}" class="mb-3">
                        @csrf
                        <input type="hidden" name="template_code" value="{{ $recommendedTemplateCode }}">
                        <input type="hidden" name="mode" value="append">
                        <button class="warehouse-setup-btn-primary" type="submit">
                            Thiết lập nhanh bằng {{ $recommendedTemplate['name'] ?? 'mẫu kho' }}
                        </button>
                    </form>
                @endif

                <div class="warehouse-template-grid">
                    @foreach($templates as $templateCode => $template)
                        <article class="warehouse-template-card">
                            <div>
                                <div class="warehouse-template-code">{{ $templateCode }}</div>
                                <h3 class="warehouse-template-title">{{ $template['name'] }}</h3>
                                <p class="warehouse-template-description">{{ $template['description'] }}</p>
                            </div>

                            <div class="warehouse-template-preview">
                                @foreach(array_slice(data_get($template, 'preview', []), 0, 4) as $previewNode)
                                    <div class="warehouse-template-node">
                                        <span>{{ $previewNode['name'] ?? $previewNode['code'] }}</span>
                                        <span class="warehouse-template-code">{{ $previewNode['code'] ?? '' }}</span>
                                    </div>
                                @endforeach
                            </div>

                            @if(auth()->user()?->hasPermission('warehouse.locations.manage'))
                                <form method="POST" action="{{ route('inventory.warehouse.templates.apply', $warehouse) }}">
                                    @csrf
                                    <input type="hidden" name="template_code" value="{{ $templateCode }}">
                                    <input type="hidden" name="mode" value="append">
                                    <button class="warehouse-setup-btn-secondary w-100" type="submit">Áp dụng mẫu này</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>
