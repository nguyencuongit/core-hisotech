<?php

namespace Botble\Inventory\Domains\Packing\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Inventory\Domains\Packing\Models\PackingList;
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

class PackingTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->setView('plugins/inventory::packing.table')
            ->model(PackingList::class)
            ->addHeaderAction(
                CreateHeaderAction::make()->route('inventory.packing.create')->permission('packing.create')
            )
            ->addActions([
                EditAction::make()->route('inventory.packing.edit')->permission('packing.edit'),
                DeleteAction::make()->route('inventory.packing.destroy')->permission('packing.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('packing.destroy'),
            ]);
    }

    public function query(): Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with([
                'warehouse:id,name,code',
                'export:id,code,partner_code,partner_name,partner_phone,receiver_name,receiver_phone,shipping_unit,tracking_code,status,document_date',
            ])
            ->select([
                'id',
                'export_id',
                'warehouse_id',
                'code',
                'status',
                'packer_id',
                'packed_at',
                'started_at',
                'completed_at',
                'total_packages',
                'total_items',
                'total_weight',
                'total_volume',
                'note',
                'created_at',
            ])
            ->withCount(['packages', 'items']);

        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin()) {
            if (empty($warehouseIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('warehouse_id', $warehouseIds);
            }
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            FormattedColumn::make('code')
                ->title('Phiếu đóng gói')
                ->alignStart()
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderPackingColumn($column->getItem())),
            FormattedColumn::make('export_id')
                ->title('Phiếu xuất / người nhận')
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderExportColumn($column->getItem())),
            FormattedColumn::make('warehouse_id')
                ->title('Kho xử lý')
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderWarehouseColumn($column->getItem())),
            FormattedColumn::make('packing_totals')
                ->title('Tổng đóng gói')
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderTotalsColumn($column->getItem())),
            FormattedColumn::make('packed_at')
                ->title('Mốc thời gian')
                ->alignStart()
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderTimelineColumn($column->getItem())),
            FormattedColumn::make('status')
                ->title('Trạng thái')
                ->alignCenter()
                ->renderUsing(fn (FormattedColumn $column): string => $this->renderStatusBadge((string) $column->getItem()->status)),
            CreatedAtColumn::make(),
        ];
    }

    protected function renderPackingColumn(PackingList $packing): string
    {
        $href = route('inventory.packing.edit', $packing->getKey());
        $code = e($packing->code ?: 'PACK-' . $packing->getKey());
        $note = trim((string) $packing->note);

        $html = '<div class="packing-cell-main">';
        $html .= '<a class="packing-code-link" href="' . e($href) . '">' . $code . '</a>';
        $html .= '<div class="packing-cell-meta">ID #' . e((string) $packing->getKey()) . '</div>';

        if ($note !== '') {
            $html .= '<div class="packing-cell-note">' . e($note) . '</div>';
        }

        return $html . '</div>';
    }

    protected function renderExportColumn(PackingList $packing): string
    {
        $export = $packing->export;

        if (! $export) {
            return (string) Html::tag('span', 'Chưa có phiếu xuất', ['class' => 'packing-muted']);
        }

        $receiver = $export->receiver_name ?: $export->partner_name;
        $phone = $export->receiver_phone ?: $export->partner_phone;
        $shipping = array_filter([$export->shipping_unit, $export->tracking_code]);

        $html = '<div class="packing-cell-main">';
        $html .= '<div class="packing-export-code">' . e($export->code ?: 'EXP-' . $export->getKey()) . '</div>';

        if ($receiver || $phone) {
            $html .= '<div class="packing-cell-meta">' . e(implode(' - ', array_filter([$receiver, $phone]))) . '</div>';
        }

        if ($shipping) {
            $html .= '<div class="packing-cell-note">' . e(implode(' - ', $shipping)) . '</div>';
        }

        return $html . '</div>';
    }

    protected function renderWarehouseColumn(PackingList $packing): string
    {
        $warehouse = $packing->warehouse;

        if (! $warehouse) {
            return (string) Html::tag('span', 'Chưa chọn kho', ['class' => 'packing-muted']);
        }

        $html = '<div class="packing-cell-main">';
        $html .= '<div class="packing-warehouse-name">' . e($warehouse->name ?: 'Kho #' . $warehouse->getKey()) . '</div>';

        if ($warehouse->code) {
            $html .= '<div class="packing-cell-meta">' . e($warehouse->code) . '</div>';
        }

        return $html . '</div>';
    }

    protected function renderTotalsColumn(PackingList $packing): string
    {
        $packages = (int) ($packing->total_packages ?: $packing->packages_count);
        $lines = (int) ($packing->items_count ?: 0);
        $items = (float) ($packing->total_items ?: 0);
        $weight = (float) ($packing->total_weight ?: 0);
        $volume = (float) ($packing->total_volume ?: 0);

        return '<div class="packing-total-grid">'
            . $this->renderMetric('Kiện', number_format($packages))
            . $this->renderMetric('Dòng', number_format($lines))
            . $this->renderMetric('SL', $this->formatNumber($items))
            . $this->renderMetric('Kg', $this->formatNumber($weight))
            . $this->renderMetric('m3', $this->formatNumber($volume))
            . '</div>';
    }

    protected function renderTimelineColumn(PackingList $packing): string
    {
        $rows = [
            'Tạo' => $packing->created_at,
            'Bắt đầu' => $packing->started_at,
            'Đóng xong' => $packing->packed_at ?: $packing->completed_at,
        ];

        $html = '<div class="packing-timeline">';

        foreach ($rows as $label => $date) {
            if (! $date) {
                continue;
            }

            $html .= '<div><span>' . e($label) . '</span><strong>' . e(BaseHelper::formatDateTime($date)) . '</strong></div>';
        }

        return $html . '</div>';
    }

    protected function renderStatusBadge(string $status): string
    {
        $labels = [
            'draft' => 'Nháp',
            'packing' => 'Đang đóng gói',
            'packed' => 'Đã đóng gói',
            'cancelled' => 'Đã hủy',
        ];

        $classes = [
            'draft' => 'is-draft',
            'packing' => 'is-packing',
            'packed' => 'is-packed',
            'cancelled' => 'is-cancelled',
        ];

        return '<span class="packing-status-badge ' . e($classes[$status] ?? 'is-draft') . '">'
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
