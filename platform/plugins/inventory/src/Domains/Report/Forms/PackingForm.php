<?php

namespace Botble\Inventory\Domains\Packing\Forms;

use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Transactions\Models\Export;
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

        $productChoices = Product::query()
            ->select(['id', 'name', 'sku'])
            ->orderBy('name')
            ->limit(300)
            ->get()
            ->mapWithKeys(fn (Product $product) => [
                $product->getKey() => trim($product->name . ($product->sku ? ' - ' . $product->sku : '')),
            ])
            ->all();

        $packageValues = $this->getPackageValues();

        $this
            ->model(PackingList::class)
            ->template('plugins/inventory::forms.full-width-form')

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
                    'shipped' => 'Shipped',
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
                    'products' => $productChoices,
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

        $model->loadMissing(['packages.items']);

        return $model->packages
            ->map(function ($package) {
                return [
                    'package_code' => $package->package_code,
                    'package_type' => $package->package_type,
                    'length' => $package->length,
                    'width' => $package->width,
                    'height' => $package->height,
                    'weight' => $package->weight,
                    'weight_unit' => $package->weight_unit,
                    'note' => $package->note,
                    'items' => $package->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'packed_qty' => $item->packed_qty,
                            'unit_name' => $item->unit_name,
                            'note' => $item->note,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
