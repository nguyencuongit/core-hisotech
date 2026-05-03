<?php

namespace Botble\Inventory\Domains\Transfer\Tables;

use Botble\Inventory\Domains\Transfer\Models\InternalTransfer;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class TransferTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->setView('plugins/inventory::transfer.table')
            ->model(InternalTransfer::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.transfer.create')->permission('transfer.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.transfer.edit')->permission('transfer.edit'),
                DeleteAction::make()->route('inventory.transfer.destroy')->permission('transfer.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('transfer.destroy'),
            ]);
    }

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with([
                'fromWarehouse:id,name,code',
                'toWarehouse:id,name,code',
                'exportDoc:id,code,status',
                'importDoc:id,doc_code,status',
            ])
            ->select([
                'id',
                'code',
                'status',
                'from_warehouse_id',
                'to_warehouse_id',
                'export_id',
                'import_id',
                'requested_by',
                'approved_by',
                'exported_by',
                'imported_by',
                'transfer_date',
                'reason',
                'note',
                'created_at',
            ])
            ->withSum('items as total_requested_qty', 'requested_qty')
            ->withSum('items as total_exported_qty', 'exported_qty')
            ->withSum('items as total_received_qty', 'received_qty')
            ->withSum('items as total_damaged_qty', 'damaged_qty');

        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('from_warehouse_id', $warehouseIds);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            FormattedColumn::make('code')
                ->title('Phiếu chuyển')
                ->alignStart()
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderTransferColumn($column->getItem())),
            FormattedColumn::make('route')
                ->title('Tuyến kho')
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderRouteColumn($column->getItem())),
            FormattedColumn::make('transfer_qty')
                ->title('Số lượng')
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderQuantityColumn($column->getItem())),
            FormattedColumn::make('in_transit_qty')
                ->title('Đang chuyển')
                ->alignCenter()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderInTransitColumn($column->getItem())),
            FormattedColumn::make('documents')
                ->title('Chứng từ')
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderDocumentsColumn($column->getItem())),
            FormattedColumn::make('status')
                ->title('Trạng thái')
                ->alignCenter()
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderStatusBadge((string) $column->getItem()->status)),
            CreatedAtColumn::make(),
        ];
    }

    protected function renderTransferColumn(InternalTransfer $transfer): string
    {
        $href = route('inventory.transfer.edit', $transfer->getKey());
        $code = e($transfer->code ?: 'TRF-' . $transfer->getKey());
        $note = trim((string) $transfer->note);
        $reason = trim((string) $transfer->reason);

        $html = '<div class="transfer-cell-main">';
        $html .= '<a class="transfer-code-link" href="' . e($href) . '">' . $code . '</a>';
        $html .= '<div class="transfer-cell-meta">ID #' . e((string) $transfer->getKey()) . '</div>';

        if ($reason !== '') {
            $html .= '<div class="transfer-cell-note">' . e($reason) . '</div>';
        } elseif ($note !== '') {
            $html .= '<div class="transfer-cell-note">' . e($note) . '</div>';
        }

        return $html . '</div>';
    }

    protected function renderRouteColumn(InternalTransfer $transfer): string
    {
        $fromName = $transfer->fromWarehouse?->name ?: 'Kho xuất';
        $toName = $transfer->toWarehouse?->name ?: 'Kho nhập';
        $fromCode = $transfer->fromWarehouse?->code;
        $toCode = $transfer->toWarehouse?->code;

        return '<div class="transfer-route">'
            . '<div><span>Xuất</span><strong>' . e($fromName) . '</strong>' . ($fromCode ? '<em>' . e($fromCode) . '</em>' : '') . '</div>'
            . '<i class="ti ti-arrow-right"></i>'
            . '<div><span>Nhập</span><strong>' . e($toName) . '</strong>' . ($toCode ? '<em>' . e($toCode) . '</em>' : '') . '</div>'
            . '</div>';
    }

    protected function renderQuantityColumn(InternalTransfer $transfer): string
    {
        $requested = (float) ($transfer->total_requested_qty ?? 0);
        $received = (float) ($transfer->total_received_qty ?? 0);
        $damaged = (float) ($transfer->total_damaged_qty ?? 0);

        return '<div class="transfer-metric-grid">'
            . $this->renderMetric('Yêu cầu', $this->formatNumber($requested))
            . $this->renderMetric('Đã nhận', $this->formatNumber($received))
            . $this->renderMetric('Hỏng', $this->formatNumber($damaged))
            . '</div>';
    }

    protected function renderInTransitColumn(InternalTransfer $transfer): string
    {
        $inTransit = max((float) ($transfer->total_exported_qty ?? 0) - (float) ($transfer->total_received_qty ?? 0), 0);

        return '<div class="transfer-in-transit">'
            . '<strong>' . e($this->formatNumber($inTransit)) . '</strong>'
            . '<span>đơn vị</span>'
            . '</div>';
    }

    protected function renderDocumentsColumn(InternalTransfer $transfer): string
    {
        $exportCode = $transfer->exportDoc?->code;
        $importCode = $transfer->importDoc?->doc_code;

        return '<div class="transfer-documents">'
            . '<div><span>Xuất</span><strong>' . e($exportCode ?: '-') . '</strong></div>'
            . '<div><span>Nhập</span><strong>' . e($importCode ?: '-') . '</strong></div>'
            . '</div>';
    }

    protected function renderStatusBadge(string $status): string
    {
        $labels = [
            'draft' => 'Nháp',
            'confirmed' => 'Đã xác nhận',
            'exporting' => 'Đang chuyển',
            'importing' => 'Đang nhập',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy',
        ];

        $classes = [
            'draft' => 'is-draft',
            'confirmed' => 'is-confirmed',
            'exporting' => 'is-moving',
            'importing' => 'is-moving',
            'completed' => 'is-completed',
            'cancelled' => 'is-cancelled',
        ];

        return '<span class="transfer-status-badge ' . e($classes[$status] ?? 'is-draft') . '">'
            . e($labels[$status] ?? ($status ?: 'Nháp'))
            . '</span>';
    }

    protected function renderMetric(string $label, string $value): string
    {
        return '<div><span>' . e($label) . '</span><strong>' . e($value) . '</strong></div>';
    }

    protected function formatNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ','), '0'), '.');
    }
}
