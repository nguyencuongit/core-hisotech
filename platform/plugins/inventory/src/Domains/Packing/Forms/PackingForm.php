<?php

namespace Botble\Inventory\Domains\Packing\Forms;

use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\Packing\Http\Requests\PackingRequest;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

class PackingForm extends FormAbstract
{
    public function setup(): void
    {
        $isEdit = $this->getModel() instanceof PackingList && $this->getModel()->exists;
        $formTitle = $isEdit ? 'Chỉnh phiếu đóng gói' : 'Tạo phiếu đóng gói';
        $formSubtitle = 'Chọn phiếu xuất, kho xử lý và chia hàng vào từng kiện trước khi chuyển sang packed.';
        $warehouseQuery = Warehouse::query();
        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $warehouseQuery->whereIn('id', $warehouseIds);
        }

        $warehouseChoices = $warehouseQuery
            ->pluck('name', 'id')
            ->all();

        $exportChoicesQuery = Export::query();

        if (! $isEdit) {
            $exportChoicesQuery->whereNotIn('id', PackingList::query()
                ->select('export_id')
                ->whereNotNull('export_id')
                ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'cancelled'))
            );
        }

        $exportChoices = $exportChoicesQuery
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Export $export) => [
                $export->getKey() => sprintf(
                    '%s - %s',
                    $export->code ?: 'EXP-' . $export->getKey(),
                    $export->partner_name ?: 'Không có đối tác'
                ),
            ])
            ->all();

        $selectedExportId = $this->getRequest()?->input('export_id')
            ?: ($this->getModel() instanceof PackingList ? $this->getModel()->export_id : null);

        $exportItemQuery = ExportItem::query()
            ->with('export')
            ->select([
                'id',
                'export_id',
                'product_id',
                'product_variation_id',
                'product_name',
                'product_code',
                'document_qty',
                'packed_qty',
                'unit_id',
                'unit_name',
                'warehouse_location_id',
                'pallet_id',
                'batch_id',
                'goods_receipt_batch_id',
                'stock_balance_id',
                'lot_no',
                'expiry_date',
            ])
            ->orderByDesc('id')
            ->limit(500);

        if ($selectedExportId) {
            $exportItemQuery->where('export_id', $selectedExportId);
        }

        $exportItemCollection = $exportItemQuery->get();

        $exportItemChoices = $exportItemCollection
            ->mapWithKeys(fn (ExportItem $item) => [
                $item->getKey() => sprintf(
                    '%s | %s - %s | còn %.4f %s',
                    $item->export?->code ?: 'EXP-' . $item->export_id,
                    $item->product_code ?: $item->product_id,
                    $item->product_name ?: 'Sản phẩm',
                    max((float) $item->document_qty - (float) $item->packed_qty, 0),
                    $item->unit_name ?: ''
                ),
            ])
            ->all();

        $exportItemsData = $exportItemCollection
            ->map(fn (ExportItem $item): array => $this->formatExportItemForClient($item))
            ->values()
            ->all();

        $packageValues = $this->getPackageValues();

        $this
            ->model(PackingList::class)
            ->template('plugins/inventory::forms.full-width-form')
            ->setValidatorClass(PackingRequest::class)

            ->add('form_start', 'html', [
                'html' => $this->glasslineStyles()
                    . '<div class="packing-glassline-page">'
                    . '<section class="packing-glassline-hero">'
                    . '<div><div class="packing-glassline-eyebrow">Packing</div>'
                    . '<h1>' . e($formTitle) . '</h1>'
                    . '<p>' . e($formSubtitle) . '</p></div>'
                    . '<div class="packing-glassline-hero__meta">'
                    . '<span><strong>' . number_format(count($exportChoices)) . '</strong> phiếu xuất khả dụng</span>'
                    . '<span><strong>' . number_format(count($warehouseChoices)) . '</strong> kho khả dụng</span>'
                    . '</div></section>'
                    . '<section class="packing-glassline-card">'
                    . '<div class="packing-glassline-section-heading"><div><span>Thông tin phiếu</span><strong>Thiết lập chứng từ đóng gói</strong></div><small>Các tổng số kiện, trọng lượng và số lượng sẽ tự tính từ danh sách kiện bên dưới.</small></div>',
            ])

            ->add('title_main', 'html', [
                'html' => '<h4 class="mb-3">Phiếu đóng gói</h4>',
            ])

            ->add('row_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('row_1_col_open', 'html', [
                'html' => '<div class="col-12">',
            ])

            ->add('section_document_title', 'html', [
                'html' => '<div class="mb-3 mt-2"><h5 class="mb-1">Thông tin phiếu đóng hàng</h5><hr class="mt-2"></div>',
            ])

            ->add('row_doc_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('code', TextField::class, [
                'label' => 'Mã đóng hàng',
                'attr' => [
                    'placeholder' => 'Nhập mã đóng hàng',
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
                'required' => true,
            ])

            ->add('export_id', SelectField::class, [
                'label' => 'Phiếu xuất',
                'choices' => $exportChoices,
                'attr' => [
                    'id' => 'packing-export-id',
                ],
                'empty_value' => 'Chọn phiếu xuất',
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
                'required' => true,
            ])

            ->add('warehouse_id', SelectField::class, [
                'label' => 'Kho',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chọn kho',
                'attr' => [
                    'id' => 'warehouse-id',
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
                'required' => true,
            ])

            ->add('status', SelectField::class, [
                'label' => 'Trạng thái',
                'choices' => [
                    'draft' => 'Nháp',
                    'packing' => 'Đang đóng gói',
                    'packed' => 'Đã đóng gói',
                    'cancelled' => 'Đã hủy',
                ],
                'empty_value' => 'Chọn trạng thái',
                'attr' => [
                    'id' => 'packing-status',
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
                'required' => true,
            ])

            ->add('row_doc_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('export_preview_panel', 'html', [
                'html' => '<div class="packing-export-preview" data-packing-export-preview hidden>'
                    . '<div class="packing-export-preview__header">'
                    . '<div><span>Phiếu xuất đã chọn</span><strong data-export-preview-code>-</strong></div>'
                    . '<small data-export-preview-status>-</small>'
                    . '</div>'
                    . '<div class="packing-export-preview__grid">'
                    . '<div><span>Kho xuất</span><strong data-export-preview-warehouse>-</strong></div>'
                    . '<div><span>Đối tác</span><strong data-export-preview-partner>-</strong></div>'
                    . '<div><span>Người nhận</span><strong data-export-preview-receiver>-</strong></div>'
                    . '<div><span>Vận chuyển</span><strong data-export-preview-shipping>-</strong></div>'
                    . '</div>'
                    . '<div class="packing-export-preview__note" data-export-preview-note></div>'
                    . '</div>',
            ])

            ->add('row_doc_2_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('packer_id', SelectField::class, [
                'label' => 'Người đóng gói',
                'choices' => [
                    '0' => '--- Khác (nhập tay) ---',
                ],
                'empty_value' => 'Chọn nhân viên',
                'attr' => [
                    'id' => 'requested-by-id',
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
            ])

            ->add('packer_name', TextField::class, [
                'label' => 'Nhập tên người đóng gói',
                'attr' => [
                    'placeholder' => 'Nhập tên...',
                    'id' => 'requested-by-name',
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12 d-none',
                    'id' => 'requested-by-name-wrapper',
                ],
            ])

            ->add('packed_at', 'datePicker', [
                'label' => 'Ngày đóng gói',
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
            ])

            ->add('total_packages', 'number', [
                'label' => 'Tổng số kiện hàng',
                'attr' => [
                    'id' => 'packing-total-packages',
                    'min' => 1,
                    'step' => 1,
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
            ])

            ->add('total_weight', 'number', [
                'label' => 'Tổng trọng lượng',
                'attr' => [
                    'min' => 0,
                    'step' => '0.01',
                    'readonly' => true,
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
            ])

            ->add('total_items', 'number', [
                'label' => 'Tổng SL đóng gói',
                'attr' => [
                    'min' => 0,
                    'step' => '0.0001',
                    'readonly' => true,
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
            ])

            ->add('total_volume', 'number', [
                'label' => 'Tổng thể tích',
                'attr' => [
                    'min' => 0,
                    'step' => '0.0001',
                    'readonly' => true,
                ],
                'wrapper' => [
                    'class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12',
                ],
            ])

            ->add('row_doc_2_close', 'html', [
                'html' => '</div>',
            ])

            ->add('note', 'textarea', [
                'label' => 'Ghi chú',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Nhập ghi chú',
                ],
                'wrapper' => [
                    'class' => 'form-group',
                ],
            ])

            ->add('row_1_col_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('form_end', 'html', [
                'html' => '</section>',
            ])

            ->add('packages_prd', 'html', [
                'html' => view('plugins/inventory::forms.partials.packing-packages', [
                    'packages' => $packageValues,
                    'exportItems' => $exportItemChoices,
                    'exportItemsData' => $exportItemsData,
                    'exportPreviewUrl' => route('inventory.packing.export-preview', ['export' => '__EXPORT_ID__']),
                ])->render() . '</div>',
            ]);
    }

    protected function formatExportItemForClient(ExportItem $item): array
    {
        $documentQty = (float) ($item->document_qty ?? 0);
        $packedQty = (float) ($item->packed_qty ?? 0);
        $remainingQty = max($documentQty - $packedQty, 0);

        return [
            'id' => $item->getKey(),
            'label' => sprintf(
                '%s | %s - %s | còn %.4f %s',
                $item->export?->code ?: 'EXP-' . $item->export_id,
                $item->product_code ?: $item->product_id,
                $item->product_name ?: 'Sản phẩm',
                $remainingQty,
                $item->unit_name ?: ''
            ),
            'export_id' => $item->export_id,
            'product_id' => $item->product_id,
            'product_variation_id' => $item->product_variation_id,
            'product_code' => $item->product_code,
            'product_name' => $item->product_name,
            'document_qty' => $documentQty,
            'packed_qty' => $packedQty,
            'remaining_qty' => $remainingQty,
            'unit_id' => $item->unit_id,
            'unit_name' => $item->unit_name,
            'warehouse_location_id' => $item->warehouse_location_id,
            'pallet_id' => $item->pallet_id,
            'batch_id' => $item->batch_id,
            'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
            'stock_balance_id' => $item->stock_balance_id,
            'lot_no' => $item->lot_no,
            'expiry_date' => optional($item->expiry_date)->format('Y-m-d'),
            'note' => $item->note,
        ];
    }

    protected function glasslineStyles(): string
    {
        return <<<'HTML'
<style>
    .packing-glassline-page {
        --packing-primary: #0F1419;
        --packing-secondary: #4A5568;
        --packing-tertiary: #2C5EF5;
        --packing-neutral: #F1F3F5;
        --packing-surface: #FFFFFF;
        --packing-border: rgba(74, 85, 104, .18);
        color: var(--packing-primary);
        display: grid;
        gap: 24px;
        font-family: Geist, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        max-width: 100%;
        min-width: 0;
        width: 100%;
    }

    .packing-glassline-page *,
    .packing-glassline-page *::before,
    .packing-glassline-page *::after {
        box-sizing: border-box;
        min-width: 0;
    }

    .packing-glassline-hero,
    .packing-glassline-card,
    .packing-package-panel,
    .package-card {
        background: var(--packing-surface);
        border: 1px solid var(--packing-border);
        border-radius: 16px;
        box-shadow: none;
    }

    .packing-glassline-hero {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: space-between;
        padding: 24px;
    }

    .packing-glassline-eyebrow,
    .packing-package-panel__eyebrow,
    .packing-glassline-page label,
    .packing-glassline-page .card-title,
    .packing-glassline-page th {
        color: var(--packing-secondary);
        font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
        font-size: .75rem;
        font-weight: 500;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .packing-glassline-hero h1 {
        color: var(--packing-primary);
        font-size: 2.25rem;
        font-weight: 600;
        letter-spacing: 0;
        line-height: 1.1;
        margin: 4px 0 10px;
    }

    .packing-glassline-hero p,
    .packing-package-panel__hint,
    .packing-package-muted {
        color: var(--packing-secondary);
        font-size: .95rem;
        line-height: 1.55;
        margin: 0;
    }

    .packing-glassline-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
        max-width: 100%;
        min-width: 0;
    }

    .packing-glassline-hero__meta span,
    .packing-package-chip {
        background: var(--packing-neutral);
        border: 1px solid var(--packing-border);
        border-radius: 10px;
        color: var(--packing-secondary);
        display: inline-flex;
        gap: 8px;
        padding: 10px 14px;
    }

    .packing-glassline-hero__meta strong {
        color: var(--packing-primary);
        font-weight: 600;
    }

    .packing-glassline-card,
    .packing-package-panel {
        max-width: 100%;
        min-width: 0;
        padding: 24px;
    }

    .packing-glassline-page .row {
        --bs-gutter-x: 16px;
    }

    .packing-glassline-page .row > [class*="col-"] {
        max-width: 100%;
        min-width: 0;
    }

    .packing-glassline-card > .card {
        border: 0;
        box-shadow: none;
        margin: 0;
    }

    .packing-glassline-card > .card > .card-body {
        padding: 0;
    }

    .packing-glassline-card h4.mb-3,
    .packing-glassline-card .mb-3.mt-2 {
        display: none;
    }

    .packing-glassline-section-heading,
    .packing-package-panel__header,
    .package-card .card-header,
    .packing-package-items__header {
        align-items: flex-start;
        display: flex;
        gap: 16px;
        justify-content: space-between;
    }

    .packing-glassline-section-heading {
        margin-bottom: 18px;
    }

    .packing-glassline-page h4,
    .packing-glassline-page h5,
    .packing-package-panel__title,
    .package-title,
    .packing-package-items__header strong {
        color: var(--packing-primary);
        font-weight: 600;
        letter-spacing: 0;
    }

    .packing-glassline-divider {
        background: var(--packing-neutral);
        border: 0;
        height: 1px;
        margin: 18px 0;
    }

    .packing-export-preview {
        background: var(--packing-neutral);
        border: 1px solid var(--packing-border);
        border-radius: 16px;
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
        padding: 16px;
    }

    .packing-export-preview[hidden] {
        display: none;
    }

    .packing-export-preview__header,
    .packing-export-preview__grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
    }

    .packing-export-preview__header span,
    .packing-export-preview__grid span {
        color: var(--packing-secondary);
        display: block;
        font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
        font-size: .75rem;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .packing-export-preview__header strong,
    .packing-export-preview__grid strong {
        color: var(--packing-primary);
        display: block;
        font-weight: 600;
        margin-top: 4px;
        overflow-wrap: anywhere;
    }

    .packing-export-preview__grid > div {
        background: #fff;
        border: 1px solid var(--packing-border);
        border-radius: 10px;
        flex: 1 1 190px;
        padding: 12px;
    }

    .packing-export-preview__note {
        color: var(--packing-secondary);
        font-size: .9rem;
        overflow-wrap: anywhere;
    }

    .packing-glassline-page .form-group {
        margin-bottom: 16px;
    }

    .packing-glassline-page .form-control,
    .packing-glassline-page .form-select,
    .packing-glassline-page select.form-control,
    .packing-glassline-page textarea.form-control {
        border-color: var(--packing-border);
        border-radius: 10px;
        color: var(--packing-primary);
        min-height: 44px;
        box-shadow: none;
    }

    .packing-glassline-page .form-control:focus,
    .packing-glassline-page .form-select:focus,
    .packing-glassline-page select.form-control:focus,
    .packing-glassline-page textarea.form-control:focus {
        border-color: var(--packing-tertiary);
        box-shadow: 0 0 0 3px rgba(44, 94, 245, .12);
    }

    .packing-glassline-page .btn {
        border-radius: 10px;
        font-weight: 600;
        min-height: 40px;
        padding: 9px 14px;
    }

    .packing-glassline-page .btn-primary {
        background: var(--packing-tertiary);
        border-color: var(--packing-tertiary);
        color: #fff;
    }

    .packing-glassline-page .btn-primary:hover,
    .packing-glassline-page .btn-primary:focus {
        background: #244bd2;
        border-color: #244bd2;
        color: #fff;
    }

    .packing-glassline-page .btn-outline-secondary,
    .packing-glassline-page .btn-light,
    .packing-glassline-page .btn-danger,
    .packing-glassline-page .btn-info {
        background: var(--packing-surface);
        border-color: var(--packing-border);
        color: var(--packing-primary);
    }

    .packing-package-panel {
        display: grid;
        gap: 18px;
    }

    .packing-package-panel__actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        max-width: 100%;
    }

    .packing-package-panel__actions .btn,
    .packing-package-chip {
        max-width: 100%;
        white-space: normal;
    }

    .package-card {
        max-width: 100%;
        min-width: 0;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .package-card .card-header {
        background: var(--packing-neutral);
        border: 0;
        padding: 16px 18px;
    }

    .package-card .card-body {
        padding: 18px;
    }

    .packing-package-items {
        background: var(--packing-neutral);
        border: 1px solid var(--packing-border);
        border-radius: 16px;
        max-width: 100%;
        min-width: 0;
        padding: 16px;
    }

    .packing-package-items__header {
        align-items: center;
        margin-bottom: 12px;
    }

    .packing-glassline-page .table {
        color: var(--packing-primary);
        margin-bottom: 0;
    }

    .packing-glassline-page .table-responsive {
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .packing-glassline-page .package-items-table {
        min-width: 720px;
    }

    .packing-glassline-page .package-item-col-export {
        width: 42%;
    }

    .packing-glassline-page .package-item-col-qty,
    .packing-glassline-page .package-item-col-unit {
        width: 16%;
    }

    .packing-glassline-page .package-item-col-note {
        width: 20%;
    }

    .packing-glassline-page .package-item-summary {
        background: #fff;
        border: 1px solid var(--packing-border);
        border-radius: 10px;
        display: grid;
        gap: 8px;
        margin-top: 10px;
        padding: 10px 12px;
    }

    .packing-glassline-page .package-item-summary[hidden] {
        display: none;
    }

    .packing-glassline-page .package-item-summary__title {
        color: var(--packing-primary);
        font-weight: 600;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .packing-glassline-page .package-item-summary__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .packing-glassline-page .package-item-summary__meta span {
        background: var(--packing-neutral);
        border-radius: 999px;
        color: var(--packing-secondary);
        font-size: .78rem;
        line-height: 1.2;
        padding: 5px 8px;
    }

    .packing-glassline-page .package-item-picker {
        align-items: center;
        background: var(--packing-neutral);
        border: 1px solid var(--packing-border);
        border-radius: 10px;
        color: var(--packing-primary);
        cursor: pointer;
        display: inline-flex;
        font-weight: 600;
        gap: 8px;
        margin-bottom: 10px;
        padding: 8px 10px;
    }

    .packing-glassline-page .package-item-picker input {
        height: 18px;
        margin: 0;
        width: 18px;
    }

    .packing-glassline-page .package-item-row:not(.is-selected) {
        opacity: .72;
    }

    .packing-glassline-page .package-item-row:not(.is-selected) .package-item-summary {
        background: var(--packing-neutral);
    }

    .packing-glassline-page .packing-products-locked .add-item-btn,
    .packing-glassline-page .packing-products-locked .remove-item-btn,
    .packing-glassline-page .packing-products-locked .remove-package-btn {
        display: none;
    }

    .packing-glassline-page .packing-products-locked .package-item-row .select2-container {
        display: none;
    }

    .packing-glassline-page .packing-products-locked .package-item-row .select2-container,
    .packing-glassline-page .packing-products-locked .package-item-row .export-item-select {
        pointer-events: none;
    }

    .packing-glassline-page .packing-products-locked .package-item-row .select2-selection {
        background: var(--packing-neutral);
        cursor: not-allowed;
    }

    .packing-glassline-page .table thead th {
        background: #fff;
        border-color: var(--packing-border);
        white-space: nowrap;
    }

    .packing-glassline-page .table td {
        border-color: rgba(74, 85, 104, .12);
        vertical-align: middle;
    }

    .packing-glassline-page .select2-container--default .select2-selection--single {
        border-color: var(--packing-border);
        border-radius: 10px;
        min-height: 44px;
    }

    .packing-glassline-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .packing-glassline-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .packing-glassline-page .select2-container {
        max-width: 100%;
        width: 100% !important;
    }

    .packing-glassline-page .package-items-table .select2-container--default .select2-selection--single {
        height: auto;
        min-height: 44px;
    }

    .packing-glassline-page .package-items-table .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.35;
        min-height: 42px;
        overflow: visible;
        padding-bottom: 10px;
        padding-top: 10px;
        text-overflow: clip;
        white-space: normal;
    }

    @media (max-width: 1199.98px) {
        .packing-glassline-hero {
            display: grid;
        }

        .packing-glassline-hero__meta {
            justify-content: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        .packing-glassline-page {
            gap: 16px;
        }

        .packing-glassline-hero,
        .packing-glassline-card,
        .packing-package-panel {
            border-radius: 14px;
            padding: 18px;
        }

        .packing-glassline-hero h1 {
            font-size: 1.75rem;
        }

        .package-card .card-body,
        .packing-package-items {
            padding: 14px;
        }

        .packing-glassline-page .package-items-table,
        .packing-glassline-page .package-items-table thead,
        .packing-glassline-page .package-items-table tbody,
        .packing-glassline-page .package-items-table th,
        .packing-glassline-page .package-items-table td,
        .packing-glassline-page .package-items-table tr {
            display: block;
            width: 100%;
        }

        .packing-glassline-page .package-items-table {
            min-width: 0;
        }

        .packing-glassline-page .package-items-table thead {
            display: none;
        }

        .packing-glassline-page .package-item-row {
            background: #fff;
            border: 1px solid var(--packing-border);
            border-radius: 12px;
            margin-bottom: 12px;
            padding: 12px;
        }

        .packing-glassline-page .package-items-table td {
            border: 0;
            padding: 8px 0;
            text-align: left !important;
        }

        .packing-glassline-page .package-items-table td::before {
            color: var(--packing-secondary);
            content: attr(data-label);
            display: block;
            font-family: "Geist Mono", "SFMono-Regular", Consolas, monospace;
            font-size: .72rem;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
    }

    @media (max-width: 768px) {
        .packing-glassline-hero,
        .packing-glassline-section-heading,
        .packing-package-panel__header,
        .package-card .card-header,
        .packing-package-items__header {
            display: grid;
        }

        .packing-glassline-hero__meta {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .packing-glassline-page {
            gap: 12px;
        }

        .packing-glassline-hero,
        .packing-glassline-card,
        .packing-package-panel {
            padding: 14px;
        }

        .packing-glassline-hero h1 {
            font-size: 1.5rem;
        }

        .packing-glassline-hero__meta,
        .packing-package-panel__actions,
        .packing-export-preview__grid {
            display: grid;
            width: 100%;
        }

        .packing-glassline-hero__meta span,
        .packing-package-chip,
        .packing-package-panel__actions .btn {
            justify-content: center;
            width: 100%;
        }

        .package-card .card-header {
            padding: 14px;
        }

        .package-card .card-body,
        .packing-package-items {
            padding: 12px;
        }
    }
</style>
HTML;
    }

    protected function getPackageValues(): array
    {
        $request = $this->getRequest();

        if ($request && is_array($request->input('packages'))) {
            return $request->input('packages');
        }

        $model = $this->getModel();

        if (! $model instanceof PackingList || ! $model->exists) {
            return [];
        }

        $model->loadMissing(['packages.items', 'packages.legacyItems']);

        return $model->packages
            ->map(function ($package) {
                return [
                    'id' => $package->getKey(),
                    'package_code' => $package->package_code,
                    'package_no' => $package->package_no,
                    'package_type_id' => $package->package_type_id,
                    'status' => $package->status,
                    'length' => $package->length,
                    'width' => $package->width,
                    'height' => $package->height,
                    'dimension_unit' => $package->dimension_unit,
                    'volume' => $package->volume,
                    'volume_weight' => $package->volume_weight,
                    'weight' => $package->weight,
                    'weight_unit' => $package->weight_unit,
                    'tracking_code' => $package->tracking_code,
                    'shipping_label_url' => $package->shipping_label_url,
                    'note' => $package->note,
                    'items' => ($package->items->isNotEmpty() ? $package->items : $package->legacyItems)->map(function ($item) {
                        return [
                            'id' => $item->getKey(),
                            'export_item_id' => $item->export_item_id,
                            'product_id' => $item->product_id,
                            'product_variation_id' => $item->product_variation_id,
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name,
                            'packed_qty' => $item->packed_qty,
                            'unit_id' => $item->unit_id,
                            'unit_name' => $item->unit_name,
                            'warehouse_location_id' => $item->warehouse_location_id,
                            'pallet_id' => $item->pallet_id,
                            'batch_id' => $item->batch_id,
                            'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
                            'stock_balance_id' => $item->stock_balance_id,
                            'storage_item_id' => $item->storage_item_id,
                            'lot_no' => $item->lot_no,
                            'expiry_date' => $item->expiry_date,
                            'note' => $item->note,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
