<style>
    .warehouse-workspace {
        background: #f1f3f5 !important;
        color: #0f1419;
        font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        margin: -1rem;
        min-height: calc(100vh - 56px);
        padding: 24px;
    }

    .warehouse-workspace .warehouse-shell {
        gap: 24px;
    }

    .warehouse-workspace .warehouse-card {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.18);
        border-radius: 16px;
        box-shadow: none;
        color: #0f1419;
    }

    .warehouse-workspace .warehouse-card__header {
        border-bottom: 1px solid rgba(74, 85, 104, 0.14);
        padding: 24px;
    }

    .warehouse-workspace .warehouse-card__body {
        padding: 24px;
    }

    .warehouse-workspace .warehouse-card__title,
    .warehouse-workspace .warehouse-title {
        color: #0f1419;
        font-weight: 600;
        letter-spacing: 0;
    }

    .warehouse-workspace .warehouse-title {
        font-size: 2.25rem;
        line-height: 1.1;
        margin: 4px 0 10px;
    }

    .warehouse-workspace .warehouse-card__title {
        font-size: 1.25rem;
        margin: 0;
    }

    .warehouse-workspace .warehouse-kicker,
    .warehouse-workspace .warehouse-metric span,
    .warehouse-workspace .warehouse-overview-card span,
    .warehouse-workspace .warehouse-settings-item span,
    .warehouse-workspace table thead th,
    .warehouse-workspace .form-label,
    .warehouse-workspace label {
        color: #4a5568;
        font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
        font-size: 0.75rem;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .warehouse-workspace .warehouse-subtitle,
    .warehouse-workspace .warehouse-card__hint,
    .warehouse-workspace .text-muted {
        color: #4a5568 !important;
        font-size: 0.95rem;
        line-height: 1.55;
    }

    .warehouse-workspace .warehouse-hero {
        padding: 24px;
    }

    .warehouse-workspace .warehouse-hero__grid {
        gap: 24px;
    }

    .warehouse-workspace .warehouse-header-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .warehouse-workspace .badge {
        border-radius: 999px;
        font-weight: 600;
        letter-spacing: 0;
        padding: 0.45rem 0.75rem;
    }

    .warehouse-workspace .bg-success-lt,
    .warehouse-workspace .text-success {
        background: #166534 !important;
        color: #fff !important;
    }

    .warehouse-workspace .bg-primary-lt,
    .warehouse-workspace .text-primary {
        background: #2c5ef5 !important;
        color: #fff !important;
    }

    .warehouse-workspace .bg-warning-lt,
    .warehouse-workspace .text-warning {
        background: #b45309 !important;
        color: #fff !important;
    }

    .warehouse-workspace .bg-secondary-lt,
    .warehouse-workspace .text-secondary {
        background: #4a5568 !important;
        color: #fff !important;
    }

    .warehouse-workspace .bg-light,
    .warehouse-workspace .text-dark {
        background: #f1f3f5 !important;
        color: #0f1419 !important;
    }

    .warehouse-workspace .btn {
        border-radius: 10px;
        font-weight: 600;
        min-height: 44px;
    }

    .warehouse-workspace .btn-primary {
        background: #2c5ef5;
        border-color: #2c5ef5;
        color: #fff;
    }

    .warehouse-workspace .btn-primary:hover {
        background: #244bd2;
        border-color: #244bd2;
        color: #fff;
    }

    .warehouse-workspace .btn-outline-primary,
    .warehouse-workspace .btn-outline-secondary,
    .warehouse-workspace .btn-light {
        background: #fff;
        border-color: rgba(74, 85, 104, 0.22);
        color: #0f1419;
    }

    .warehouse-workspace .btn-outline-primary:hover,
    .warehouse-workspace .btn-outline-secondary:hover,
    .warehouse-workspace .btn-light:hover {
        border-color: rgba(15, 20, 25, 0.38);
        color: #0f1419;
    }

    .warehouse-workspace .warehouse-tabs.d-none {
        display: flex !important;
    }

    .warehouse-workspace .warehouse-tabs {
        gap: 8px;
        margin-bottom: 18px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .warehouse-workspace .warehouse-tab {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.22);
        border-radius: 10px;
        color: #0f1419;
        font-weight: 600;
        padding: 10px 14px;
        text-decoration: none;
        white-space: nowrap;
    }

    .warehouse-workspace .warehouse-tab.is-active,
    .warehouse-workspace .warehouse-tab:hover {
        background: #0f1419;
        border-color: #0f1419;
        color: #fff;
    }

    .warehouse-workspace .warehouse-metric,
    .warehouse-workspace .warehouse-overview-card,
    .warehouse-workspace .warehouse-settings-item,
    .warehouse-workspace .warehouse-map-stat,
    .warehouse-workspace .warehouse-system-note,
    .warehouse-workspace .warehouse-map-view-detail,
    .warehouse-workspace .warehouse-empty-state {
        background: #f1f3f5;
        border: 0;
        border-radius: 10px;
        box-shadow: none;
        color: #0f1419;
        padding: 14px;
    }

    .warehouse-workspace .warehouse-metric strong,
    .warehouse-workspace .warehouse-overview-card strong,
    .warehouse-workspace .warehouse-settings-item strong,
    .warehouse-workspace .warehouse-map-stat strong {
        color: #0f1419;
        font-size: 1.35rem;
        font-weight: 600;
        letter-spacing: 0;
    }

    .warehouse-workspace .warehouse-mode-card,
    .warehouse-workspace .warehouse-preset-btn,
    .warehouse-workspace .warehouse-template-card,
    .warehouse-workspace .warehouse-tree-node,
    .warehouse-workspace .warehouse-map-toolbar-card,
    .warehouse-workspace .warehouse-storage-mode-card {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.18);
        border-radius: 10px;
        box-shadow: none;
    }

    .warehouse-workspace .warehouse-mode-card.is-active,
    .warehouse-workspace .warehouse-tree-node.is-active,
    .warehouse-workspace .warehouse-storage-mode-card:has(input:checked) {
        border-color: #2c5ef5;
        box-shadow: 0 0 0 3px rgba(44, 94, 245, 0.10);
    }

    .warehouse-workspace .form-control,
    .warehouse-workspace .form-select,
    .warehouse-workspace textarea,
    .warehouse-workspace select {
        border-color: rgba(74, 85, 104, 0.22);
        border-radius: 10px;
        color: #0f1419;
        min-height: 44px;
    }

    .warehouse-workspace .form-control:focus,
    .warehouse-workspace .form-select:focus,
    .warehouse-workspace textarea:focus,
    .warehouse-workspace select:focus {
        border-color: #2c5ef5;
        box-shadow: 0 0 0 3px rgba(44, 94, 245, 0.12);
    }

    .warehouse-workspace .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card {
        border-radius: 16px;
        margin: 0;
    }

    .warehouse-workspace .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card > .warehouse-card__header {
        display: block;
    }

    .warehouse-workspace .warehouse-panel[data-panel-key="maps"].is-active > .warehouse-card > .warehouse-card__body {
        padding: 16px;
    }

    .warehouse-workspace .warehouse-map-canvas-panel,
    .warehouse-workspace .warehouse-map-sidebar,
    .warehouse-workspace .warehouse-map-tools,
    .warehouse-workspace .warehouse-map-inspector {
        background: #fff;
        border: 1px solid rgba(74, 85, 104, 0.18);
        border-radius: 16px;
        box-shadow: none;
    }

    .warehouse-workspace .warehouse-map-stage {
        background: #f1f3f5;
        border: 1px solid rgba(74, 85, 104, 0.16);
        border-radius: 16px;
    }

    .warehouse-workspace .warehouse-map-viewport {
        background: #fff;
        border-radius: 14px;
    }

    .warehouse-workspace table {
        color: #0f1419;
    }

    .warehouse-workspace table thead th {
        background: #fff;
        border-bottom: 1px solid rgba(74, 85, 104, 0.16);
        font-weight: 500;
    }

    .warehouse-workspace table tbody td {
        border-bottom: 1px solid rgba(74, 85, 104, 0.12);
        vertical-align: middle;
    }

    @media (max-width: 767.98px) {
        .warehouse-workspace {
            margin: -0.75rem;
            padding: 16px;
        }

        .warehouse-workspace .warehouse-title {
            font-size: 1.75rem;
        }
    }
</style>
