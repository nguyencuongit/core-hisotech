<?php

namespace Botble\Inventory\Domains\Packing\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\Packing\Forms\Concerns\InteractsWithPackingFormData;
use Botble\Inventory\Domains\Packing\Http\Requests\PackingRequest;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

class PackingForm extends FormAbstract
{
    use InteractsWithPackingFormData;

    public function setup(): void
    {
        $isEdit = $this->getModel() instanceof PackingList && $this->getModel()->exists;
        $formTitle = $isEdit ? 'Chỉnh phiếu đóng gói' : 'Tạo phiếu đóng gói';
        $formSubtitle = 'Chọn phiếu xuất, kho xử lý và chia hàng vào từng kiện trước khi chuyển sang packed.';
        $warehouseChoices = $this->loadWarehouseChoices();
        $exportChoices = $this->loadExportChoices($isEdit);
        $selectedExportId = $this->getRequest()?->input('export_id')
            ?: ($this->getModel() instanceof PackingList ? $this->getModel()->export_id : null);
        $exportItems = $this->loadExportItems($selectedExportId ? (int) $selectedExportId : null);
        $packageValues = $this->getPackageValues();
        $exportCount = count($exportChoices);
        $warehouseCount = count($warehouseChoices);

        Assets::addStylesDirectly('vendor/core/plugins/inventory/css/packing-form.css');

        $this
            ->model(PackingList::class)
            ->template('plugins/inventory::forms.full-width-form')
            ->setValidatorClass(PackingRequest::class)
            ->add('form_start', 'html', [
                'html' => view('plugins/inventory::packing.form._hero', compact(
                    'formTitle',
                    'formSubtitle',
                    'exportCount',
                    'warehouseCount'
                ))->render(),
            ])
            ->add('title_main', 'html', ['html' => '<h4 class="mb-3">Phiếu đóng gói</h4>']);

        $this
            ->openRow('row_1')
            ->openColumn('row_1_col')
            ->sectionTitle('section_document_title', 'Thông tin phiếu đóng hàng')
            ->openRow('row_doc_1')
            ->add('code', TextField::class, [
                'label' => 'Mã đóng hàng',
                'attr' => ['placeholder' => 'Nhập mã đóng hàng'],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
                'required' => true,
            ])
            ->add('export_id', SelectField::class, [
                'label' => 'Phiếu xuất',
                'choices' => $exportChoices,
                'attr' => ['id' => 'packing-export-id'],
                'empty_value' => 'Chọn phiếu xuất',
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
                'required' => true,
            ])
            ->add('warehouse_id', SelectField::class, [
                'label' => 'Kho',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chọn kho',
                'attr' => ['id' => 'warehouse-id'],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
                'required' => true,
            ])
            ->add('status', SelectField::class, [
                'label' => 'Trạng thái',
                'choices' => ['draft' => 'Nháp', 'packing' => 'Đang đóng gói', 'packed' => 'Đã đóng gói', 'cancelled' => 'Đã hủy'],
                'empty_value' => 'Chọn trạng thái',
                'attr' => ['id' => 'packing-status'],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
                'required' => true,
            ])
            ->closeRow('row_doc_1')
            ->add('export_preview_panel', 'html', ['html' => $this->exportPreviewHtml()])
            ->openRow('row_doc_2')
            ->add('packer_id', SelectField::class, [
                'label' => 'Người đóng gói',
                'choices' => ['0' => '--- Khác (nhập tay) ---'],
                'empty_value' => 'Chọn nhân viên',
                'attr' => ['id' => 'requested-by-id'],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
            ])
            ->add('packer_name', TextField::class, [
                'label' => 'Nhập tên người đóng gói',
                'attr' => ['placeholder' => 'Nhập tên...', 'id' => 'requested-by-name'],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12 d-none', 'id' => 'requested-by-name-wrapper'],
            ])
            ->add('packed_at', 'datePicker', [
                'label' => 'Ngày đóng gói',
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
            ])
            ->add('total_packages', 'number', [
                'label' => 'Tổng số kiện hàng',
                'attr' => ['id' => 'packing-total-packages', 'min' => 1, 'step' => 1],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
            ])
            ->add('total_weight', 'number', [
                'label' => 'Tổng trọng lượng',
                'attr' => ['min' => 0, 'step' => '0.01', 'readonly' => true],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
            ])
            ->add('total_items', 'number', [
                'label' => 'Tổng SL đóng gói',
                'attr' => ['min' => 0, 'step' => '0.0001', 'readonly' => true],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
            ])
            ->add('total_volume', 'number', [
                'label' => 'Tổng thể tích',
                'attr' => ['min' => 0, 'step' => '0.0001', 'readonly' => true],
                'wrapper' => ['class' => 'form-group col-xl-3 col-lg-4 col-md-6 col-12'],
            ])
            ->closeRow('row_doc_2')
            ->add('note', 'textarea', [
                'label' => 'Ghi chú',
                'attr' => ['rows' => 3, 'placeholder' => 'Nhập ghi chú'],
                'wrapper' => ['class' => 'form-group'],
            ])
            ->closeColumn('row_1_col')
            ->closeRow('row_1')
            ->add('form_end', 'html', [
                'html' => view('plugins/inventory::packing.form._close')->render(),
            ])
            ->add('packages_prd', 'html', [
                'html' => view('plugins/inventory::forms.partials.packing-packages', [
                    'packages' => $packageValues,
                    'exportItems' => $exportItems['choices'],
                    'exportItemsData' => $exportItems['data'],
                    'exportPreviewUrl' => route('inventory.packing.export-preview', ['export' => '__EXPORT_ID__']),
                ])->render() . '</div>',
            ]);
    }

    protected function loadWarehouseChoices(): array
    {
        $warehouseQuery = Warehouse::query();
        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $warehouseQuery->whereIn('id', $warehouseIds);
        }

        return $warehouseQuery->pluck('name', 'id')->all();
    }

    protected function loadExportChoices(bool $isEdit): array
    {
        $query = Export::query();

        if (! $isEdit) {
            $query->whereNotIn('id', PackingList::query()
                ->select('export_id')
                ->whereNotNull('export_id')
                ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'cancelled'))
            );
        }

        return $query->orderByDesc('id')->limit(200)->get()
            ->mapWithKeys(fn (Export $export) => [
                $export->getKey() => sprintf('%s - %s', $export->code ?: 'EXP-' . $export->getKey(), $export->partner_name ?: 'Không có đối tác'),
            ])
            ->all();
    }

    protected function loadExportItems(?int $selectedExportId): array
    {
        $query = ExportItem::query()
            ->with('export')
            ->select(['id', 'export_id', 'product_id', 'product_variation_id', 'product_name', 'product_code', 'document_qty', 'packed_qty', 'unit_id', 'unit_name', 'warehouse_location_id', 'pallet_id', 'batch_id', 'goods_receipt_batch_id', 'stock_balance_id', 'lot_no', 'expiry_date'])
            ->orderByDesc('id')
            ->limit(500);

        if ($selectedExportId) {
            $query->where('export_id', $selectedExportId);
        }

        $items = $query->get();

        return [
            'choices' => $items->mapWithKeys(fn (ExportItem $item) => [
                $item->getKey() => sprintf(
                    '%s | %s - %s | còn %.4f %s',
                    $item->export?->code ?: 'EXP-' . $item->export_id,
                    $item->product_code ?: $item->product_id,
                    $item->product_name ?: 'Sản phẩm',
                    max((float) $item->document_qty - (float) $item->packed_qty, 0),
                    $item->unit_name ?: ''
                ),
            ])->all(),
            'data' => $items->map(fn (ExportItem $item): array => $this->formatExportItemForClient($item))->values()->all(),
        ];
    }

    protected function exportPreviewHtml(): string
    {
        return '<div class="packing-export-preview" data-packing-export-preview hidden>'
            . '<div class="packing-export-preview__header"><div><span>Phiếu xuất đã chọn</span><strong data-export-preview-code>-</strong></div><small data-export-preview-status>-</small></div>'
            . '<div class="packing-export-preview__grid">'
            . '<div><span>Kho xuất</span><strong data-export-preview-warehouse>-</strong></div>'
            . '<div><span>Đối tác</span><strong data-export-preview-partner>-</strong></div>'
            . '<div><span>Người nhận</span><strong data-export-preview-receiver>-</strong></div>'
            . '<div><span>Vận chuyển</span><strong data-export-preview-shipping>-</strong></div>'
            . '</div><div class="packing-export-preview__note" data-export-preview-note></div></div>';
    }

    protected function openRow(string $name): self
    {
        return $this->add($name . '_open', 'html', ['html' => '<div class="row">']);
    }

    protected function closeRow(string $name): self
    {
        return $this->add($name . '_close', 'html', ['html' => '</div>']);
    }

    protected function openColumn(string $name): self
    {
        return $this->add($name . '_open', 'html', ['html' => '<div class="col-12">']);
    }

    protected function closeColumn(string $name): self
    {
        return $this->add($name . '_close', 'html', ['html' => '</div>']);
    }

    protected function sectionTitle(string $name, string $title): self
    {
        return $this->add($name, 'html', [
            'html' => '<div class="mb-3 mt-2"><h5 class="mb-1">' . e($title) . '</h5><hr class="mt-2"></div>',
        ]);
    }
}
