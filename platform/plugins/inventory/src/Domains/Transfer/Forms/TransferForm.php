<?php

namespace Botble\Inventory\Domains\Transfer\Forms;

use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\Transfer\Models\InternalTransfer;
use Botble\Inventory\Domains\Transfer\Models\InternalTransferItem;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\StockBalance;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;

class TransferForm extends FormAbstract
{
    public function setup(): void
    {
        $model = $this->getModel();
        $status = $model instanceof InternalTransfer && $model->exists ? (string) $model->status : 'draft';
        $isItemLocked = in_array($status, ['confirmed', 'exporting', 'completed', 'cancelled'], true);
        $isDocumentLocked = in_array($status, ['confirmed', 'exporting', 'completed', 'cancelled'], true);
        $isReceiving = $status === 'exporting';

        $warehouseIds = inventory_warehouse_ids();
        $warehouseQuery = Warehouse::query();

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $warehouseQuery->whereIn('id', $warehouseIds);
        }

        $warehouseChoices = $warehouseQuery
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        $items = $this->getItemValues();
        $stockBalances = $this->getStockBalanceOptions($items, $warehouseIds);
        $locations = $this->getLocationOptions($items, $warehouseIds);
        $pallets = $this->getPalletOptions($items, $warehouseIds);

        $statusLabel = $this->statusLabels()[$status] ?? 'Nháp';
        $statusTone = $this->statusTones()[$status] ?? 'draft';
        $itemCount = count(array_filter($items, fn (array $item): bool => ! empty($item['stock_balance_id'])));
        $totalQty = array_reduce(
            $items,
            fn (float $carry, array $item): float => $carry + (float) ($item['requested_qty'] ?? 0),
            0.0
        );
        $inTransitQty = array_reduce(
            $items,
            fn (float $carry, array $item): float => $carry + max((float) ($item['exported_qty'] ?? 0) - (float) ($item['received_qty'] ?? 0), 0),
            0.0
        );
        $hiddenLockedFields = $isDocumentLocked && $model instanceof InternalTransfer
            ? sprintf(
                '<input type="hidden" name="from_warehouse_id" value="%s"><input type="hidden" name="to_warehouse_id" value="%s">',
                e((string) $model->from_warehouse_id),
                e((string) $model->to_warehouse_id)
            )
            : '';

        $this
            ->model(InternalTransfer::class)
            ->template('plugins/inventory::forms.full-width-form')
            ->setActionButtons(' ')
            ->add('workflow_state', 'html', [
                'html' => sprintf(
                    '<input type="hidden" id="transfer-workflow-action" name="workflow_action" value="save">'
                    . '<input type="hidden" name="status" value="%s">'
                    . '%s',
                    e($status),
                    $hiddenLockedFields
                ),
            ])
            ->add('glassline_style', 'html', [
                'html' => $this->glasslineStyle(),
            ])
            ->add('form_start', 'html', [
                'html' => '<div class="inventory-transfer-page"><div class="inventory-transfer-panel">',
            ])
            ->add('title_main', 'html', [
                'html' => sprintf(
                    '<div class="inventory-transfer-header">'
                    . '<div class="inventory-transfer-heading"><span>TRANSFER</span><h1>Phiếu chuyển kho</h1></div>'
                    . '<div class="inventory-transfer-summary">'
                    . '<div><span>Trạng thái</span><strong class="transfer-status-pill is-%s">%s</strong></div>'
                    . '<div><span>Số dòng</span><strong>%s</strong></div>'
                    . '<div><span>Tổng SL</span><strong>%s</strong></div>'
                    . '<div><span>Đang chuyển</span><strong>%s</strong></div>'
                    . '</div>'
                    . '</div>',
                    e($statusTone),
                    e($statusLabel),
                    number_format($itemCount),
                    number_format($totalQty, 4),
                    number_format($inTransitQty, 4)
                ),
            ])
            ->add('section_document_open', 'html', [
                'html' => '<div class="inventory-transfer-section"><div class="inventory-transfer-section__head"><span>Chứng từ</span></div>',
            ])
            ->add('row_doc_open', 'html', [
                'html' => '<div class="row inventory-transfer-grid">',
            ])
            ->add('code', TextField::class, [
                'label' => 'Mã phiếu chuyển',
                'attr' => $this->attrs([
                    'placeholder' => 'Tự sinh nếu để trống',
                    'readonly' => $isDocumentLocked,
                ]),
                'wrapper' => [
                    'class' => 'form-group col-lg-3 col-md-6',
                ],
            ])
            ->add('transfer_date', 'datePicker', [
                'label' => 'Ngày chuyển kho',
                'attr' => $this->attrs([
                    'readonly' => $isDocumentLocked,
                ]),
                'wrapper' => [
                    'class' => 'form-group col-lg-3 col-md-6',
                ],
            ])
            ->add('from_warehouse_id', SelectField::class, [
                'label' => 'Kho xuất',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chọn kho xuất',
                'attr' => $this->attrs([
                    'id' => 'transfer-from-warehouse',
                    'disabled' => $isDocumentLocked,
                ]),
                'wrapper' => [
                    'class' => 'form-group col-lg-3 col-md-6',
                ],
                'required' => true,
            ])
            ->add('to_warehouse_id', SelectField::class, [
                'label' => 'Kho nhập',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chọn kho nhập',
                'attr' => $this->attrs([
                    'id' => 'transfer-to-warehouse',
                    'disabled' => $isDocumentLocked,
                ]),
                'wrapper' => [
                    'class' => 'form-group col-lg-3 col-md-6',
                ],
                'required' => true,
            ])
            ->add('row_doc_close', 'html', [
                'html' => '</div>',
            ])
            ->add('row_meta_open', 'html', [
                'html' => '<div class="row inventory-transfer-grid">',
            ])
            ->add('requested_by', 'number', [
                'label' => 'Người yêu cầu',
                'attr' => $this->attrs([
                    'placeholder' => 'ID người yêu cầu',
                    'min' => 0,
                    'readonly' => $isDocumentLocked,
                ]),
                'wrapper' => [
                    'class' => 'form-group col-lg-3 col-md-6',
                ],
            ])
            ->add('reason', TextField::class, [
                'label' => 'Lý do chuyển',
                'attr' => $this->attrs([
                    'placeholder' => 'Nhập lý do chuyển kho',
                    'readonly' => $isDocumentLocked,
                ]),
                'wrapper' => [
                    'class' => 'form-group col-lg-9 col-md-6',
                ],
            ])
            ->add('row_meta_close', 'html', [
                'html' => '</div>',
            ])
            ->add('note', 'textarea', [
                'label' => 'Ghi chú',
                'attr' => $this->attrs([
                    'rows' => 3,
                    'placeholder' => 'Nhập ghi chú chuyển kho',
                    'readonly' => $isDocumentLocked,
                ]),
                'wrapper' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('section_document_close', 'html', [
                'html' => '</div>',
            ])
            ->add('workflow_actions', 'html', [
                'html' => view('plugins/inventory::forms.partials.transfer-workflow-actions', [
                    'status' => $status,
                ])->render(),
            ])
            ->add('form_end', 'html', [
                'html' => '</div></div>',
            ])
            ->add('items_prd', 'html', [
                'html' => view('plugins/inventory::forms.partials.transfer-items', [
                    'items' => $items,
                    'stockBalances' => $stockBalances,
                    'locations' => $locations,
                    'pallets' => $pallets,
                    'status' => $status,
                    'isLocked' => $isItemLocked,
                    'isReceiving' => $isReceiving,
                ])->render(),
            ])
            ->add('transfer_logs', 'html', [
                'html' => view('plugins/inventory::forms.partials.transfer-logs', [
                    'logs' => $model instanceof InternalTransfer && $model->exists
                        ? $model->logs()->with('user')->limit(20)->get()
                        : collect(),
                ])->render(),
            ]);
    }

    protected function statusLabels(): array
    {
        return [
            'draft' => 'Nháp',
            'confirmed' => 'Đã xác nhận',
            'exporting' => 'Đang chuyển',
            'importing' => 'Đang nhập',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy',
        ];
    }

    protected function statusTones(): array
    {
        return [
            'draft' => 'draft',
            'confirmed' => 'confirmed',
            'exporting' => 'moving',
            'importing' => 'moving',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
        ];
    }

    protected function glasslineStyle(): string
    {
        return <<<'HTML'
<style>
    .inventory-transfer-page {
        --gl-primary: #0F1419;
        --gl-secondary: #4A5568;
        --gl-tertiary: #2C5EF5;
        --gl-neutral: #F1F3F5;
        --gl-surface: #FFFFFF;
        --gl-border: #D9DEE6;
        --gl-muted: #EEF1F4;
        color: var(--gl-primary);
        font-family: Geist, Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .inventory-transfer-panel,
    .transfer-items-card {
        background: var(--gl-surface);
        border: 1px solid var(--gl-border);
        border-radius: 16px;
        box-shadow: none;
    }

    .inventory-transfer-panel {
        margin-bottom: 16px;
        padding: 24px;
    }

    .inventory-transfer-header {
        align-items: flex-start;
        display: flex;
        gap: 24px;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .inventory-transfer-heading span,
    .inventory-transfer-section__head span,
    .inventory-transfer-summary span,
    .transfer-items-table th,
    .inventory-transfer-page label {
        color: var(--gl-secondary);
        font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .inventory-transfer-heading h1 {
        color: var(--gl-primary);
        font-size: clamp(1.75rem, 3vw, 2.25rem);
        font-weight: 650;
        letter-spacing: 0;
        line-height: 1.15;
        margin: 4px 0 0;
    }

    .inventory-transfer-summary {
        display: grid;
        gap: 8px;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        min-width: min(100%, 480px);
    }

    .inventory-transfer-summary > div {
        background: var(--gl-neutral);
        border-radius: 10px;
        padding: 12px 14px;
    }

    .inventory-transfer-summary strong {
        color: var(--gl-primary);
        display: block;
        font-size: 1.05rem;
        font-weight: 750;
        line-height: 1.3;
        margin-top: 4px;
    }

    .transfer-status-pill {
        background: var(--gl-surface);
        border: 1px solid var(--gl-border);
        border-radius: 999px;
        display: inline-flex !important;
        padding: 5px 10px;
    }

    .transfer-status-pill.is-moving,
    .transfer-status-pill.is-confirmed {
        border-color: rgba(44, 94, 245, 0.28);
        color: var(--gl-tertiary);
    }

    .inventory-transfer-section {
        border-top: 1px solid var(--gl-border);
        padding-top: 18px;
    }

    .inventory-transfer-section__head {
        margin-bottom: 12px;
    }

    .inventory-transfer-grid {
        row-gap: 4px;
    }

    .inventory-transfer-page .form-control,
    .transfer-items-card .form-control,
    .inventory-transfer-page .form-select,
    .transfer-items-card .form-select {
        border: 1px solid var(--gl-border);
        border-radius: 10px;
        box-shadow: none;
        color: var(--gl-primary);
        min-height: 48px;
    }

    .inventory-transfer-page .form-control:focus,
    .transfer-items-card .form-control:focus,
    .inventory-transfer-page .form-select:focus,
    .transfer-items-card .form-select:focus {
        border-color: var(--gl-tertiary);
        box-shadow: 0 0 0 3px rgba(44, 94, 245, 0.12);
    }

    .inventory-transfer-page textarea.form-control {
        min-height: 96px;
    }

    @media (max-width: 991.98px) {
        .inventory-transfer-header {
            display: block;
        }

        .inventory-transfer-summary {
            grid-template-columns: 1fr;
            margin-top: 16px;
            min-width: 0;
        }
    }
</style>
HTML;
    }

    protected function attrs(array $attributes): array
    {
        return array_filter(
            $attributes,
            static fn ($value): bool => $value !== false && $value !== null
        );
    }

    protected function getItemValues(): array
    {
        $request = $this->getRequest();

        if ($request && is_array($request->input('items'))) {
            return $request->input('items');
        }

        $model = $this->getModel();

        if (! $model instanceof InternalTransfer || ! $model->exists) {
            return [];
        }

        $model->loadMissing('items');

        return $model->items
            ->map(fn (InternalTransferItem $item): array => [
                'id' => $item->getKey(),
                'stock_balance_id' => $item->stock_balance_id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'product_code' => $item->product_code,
                'product_name' => $item->product_name,
                'requested_qty' => $item->requested_qty,
                'exported_qty' => $item->exported_qty,
                'received_qty' => $item->received_qty,
                'damaged_qty' => $item->damaged_qty,
                'shortage_qty' => $item->shortage_qty,
                'overage_qty' => $item->overage_qty,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit_name,
                'from_location_id' => $item->from_location_id,
                'to_location_id' => $item->to_location_id,
                'pallet_id' => $item->pallet_id,
                'to_pallet_id' => $item->to_pallet_id,
                'batch_id' => $item->batch_id,
                'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
                'lot_no' => $item->lot_no,
                'expiry_date' => optional($item->expiry_date)->format('Y-m-d'),
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'note' => $item->note,
            ])
            ->values()
            ->all();
    }

    protected function getStockBalanceOptions(array $items, array $warehouseIds): array
    {
        $selectedIds = collect($items)
            ->pluck('stock_balance_id')
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->all();

        $query = StockBalance::query()
            ->with([
                'product:id,name,sku',
                'warehouse:id,name,code',
                'warehouseLocation:id,warehouse_id,code,name',
                'pallet:id,code',
            ])
            ->where(function ($query) use ($selectedIds): void {
                $query->where('available_qty', '>', 0);

                if ($selectedIds !== []) {
                    $query->orWhereIn('id', $selectedIds);
                }
            });

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        return $query
            ->orderByDesc('updated_at')
            ->limit(1000)
            ->get()
            ->map(fn (StockBalance $balance): array => [
                'id' => (string) $balance->getKey(),
                'warehouse_id' => (int) $balance->warehouse_id,
                'warehouse_name' => $balance->warehouse?->name,
                'warehouse_code' => $balance->warehouse?->code,
                'product_id' => (int) $balance->product_id,
                'product_variation_id' => $balance->product_variation_id,
                'product_code' => $balance->product?->sku ?: (string) $balance->product_id,
                'product_name' => $balance->product?->name ?: 'Sản phẩm #' . $balance->product_id,
                'from_location_id' => $balance->warehouse_location_id,
                'from_location_label' => $balance->warehouseLocation?->displayLabel() ?: '-',
                'pallet_id' => $balance->pallet_id,
                'pallet_code' => $balance->pallet?->code,
                'batch_id' => $balance->batch_id,
                'goods_receipt_batch_id' => $balance->goods_receipt_batch_id,
                'available_qty' => (float) $balance->available_qty,
                'quantity' => (float) $balance->quantity,
                'unit_price' => (float) ($balance->last_unit_cost ?: $balance->average_cost ?: 0),
                'label' => sprintf(
                    '%s - %s | %s | còn %s',
                    $balance->product?->sku ?: $balance->product_id,
                    $balance->product?->name ?: 'Sản phẩm',
                    $balance->warehouseLocation?->displayLabel() ?: 'Không vị trí',
                    number_format((float) $balance->available_qty, 4)
                ),
            ])
            ->values()
            ->all();
    }

    protected function getLocationOptions(array $items, array $warehouseIds): array
    {
        $selectedLocationIds = collect($items)
            ->pluck('to_location_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->all();

        $query = WarehouseLocation::query()
            ->where(function ($query) use ($selectedLocationIds): void {
                $query->where('status', true);

                if ($selectedLocationIds !== []) {
                    $query->orWhereIn('id', $selectedLocationIds);
                }
            });

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        return $query
            ->orderBy('warehouse_id')
            ->orderBy('code')
            ->get(['id', 'warehouse_id', 'code', 'name', 'type'])
            ->map(fn (WarehouseLocation $location): array => [
                'id' => (int) $location->getKey(),
                'warehouse_id' => (int) $location->warehouse_id,
                'label' => $location->displayLabel(),
                'type' => $location->type,
            ])
            ->values()
            ->all();
    }

    protected function getPalletOptions(array $items, array $warehouseIds): array
    {
        $selectedPalletIds = collect($items)
            ->pluck('to_pallet_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->all();

        $query = Pallet::query()
            ->with(['warehouse:id,name,code', 'currentLocation:id,warehouse_id,code,name'])
            ->where(function ($query) use ($selectedPalletIds): void {
                $query->whereIn('status', ['empty', 'open', 'in_use']);

                if ($selectedPalletIds !== []) {
                    $query->orWhereIn('id', $selectedPalletIds);
                }
            });

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        return $query
            ->orderBy('warehouse_id')
            ->orderBy('code')
            ->limit(1000)
            ->get()
            ->map(fn (Pallet $pallet): array => [
                'id' => (int) $pallet->getKey(),
                'warehouse_id' => (int) $pallet->warehouse_id,
                'code' => $pallet->code,
                'label' => sprintf(
                    '%s | %s | %s',
                    $pallet->code,
                    $pallet->warehouse?->name ?: 'Kho',
                    $pallet->currentLocation?->displayLabel() ?: 'Chưa có vị trí'
                ),
            ])
            ->values()
            ->all();
    }
}
