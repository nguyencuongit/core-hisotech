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
        $warehouseQuery = Warehouse::query();
        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $warehouseQuery->whereIn('id', $warehouseIds);
        }

        $warehouseChoices = $warehouseQuery
            ->pluck('name', 'id')
            ->all();

        $exportChoices = Export::query()
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Export $export) => [
                $export->getKey() => sprintf(
                    '%s - %s',
                    $export->code ?: 'EXP-' . $export->getKey(),
                    $export->partner_name ?: 'Khong co doi tac'
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

        $exportItemChoices = $exportItemQuery
            ->get()
            ->mapWithKeys(fn (ExportItem $item) => [
                $item->getKey() => sprintf(
                    '%s | %s - %s | con %.4f %s',
                    $item->export?->code ?: 'EXP-' . $item->export_id,
                    $item->product_code ?: $item->product_id,
                    $item->product_name ?: 'San pham',
                    max((float) $item->document_qty - (float) $item->packed_qty, 0),
                    $item->unit_name ?: ''
                ),
            ])
            ->all();

        $packageValues = $this->getPackageValues();

        $this
            ->model(PackingList::class)
            ->template('plugins/inventory::forms.full-width-form')
            ->setValidatorClass(PackingRequest::class)

            ->add('form_start', 'html', [
                'html' => '<div class="card mb-4"><div class="card-body">',
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
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('export_id', SelectField::class, [
                'label' => 'Phiếu xuất',
                'choices' => $exportChoices,
                'empty_value' => 'Chọn phiếu xuất',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
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
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('status', SelectField::class, [
                'label' => 'Trạng thái',
                'choices' => [
                    'draft' => 'Draft',
                    'packing' => 'Packing',
                    'packed' => 'Packed',
                    'cancelled' => 'Cancelled',
                ],
                'empty_value' => 'Chọn trạng thái',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('row_doc_1_close', 'html', [
                'html' => '</div>',
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
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('packer_name', TextField::class, [
                'label' => 'Nhập tên người đóng gói',
                'attr' => [
                    'placeholder' => 'Nhập tên...',
                    'id' => 'requested-by-name',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3 d-none',
                    'id' => 'requested-by-name-wrapper',
                ],
            ])

            ->add('packed_at', 'datePicker', [
                'label' => 'Ngày đóng gói',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('total_packages', 'number', [
                'label' => 'Tổng số kiện hàng',
                'attr' => [
                    'min' => 0,
                    'step' => 1,
                    'readonly' => true,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
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
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('total_items', 'number', [
                'label' => 'Tong SL dong goi',
                'attr' => [
                    'min' => 0,
                    'step' => '0.0001',
                    'readonly' => true,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('total_volume', 'number', [
                'label' => 'Tong the tich',
                'attr' => [
                    'min' => 0,
                    'step' => '0.0001',
                    'readonly' => true,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
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
                'html' => '</div></div>',
            ])

            ->add('packages_prd', 'html', [
                'html' => view('plugins/inventory::forms.partials.packing-packages', [
                    'packages' => $packageValues,
                    'exportItems' => $exportItemChoices,
                ])->render(),
            ]);
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
