<?php

namespace Botble\Inventory\Domains\Transfer\Forms;

use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\Transfer\Models\InternalTransfer;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

class TransferForm extends FormAbstract
{
    public function setup(): void
    {
        $query = Warehouse::query();
        $warehouseIds = inventory_warehouse_ids();

        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('id', $warehouseIds);
        }

        $warehouseChoices = $query
            ->pluck('name', 'id')
            ->all();

        $this
            ->model(InternalTransfer::class)
            ->template('plugins/inventory::forms.full-width-form')

            ->add('form_start', 'html', [
                'html' => '<div class="card mb-4"><div class="card-body">',
            ])

            ->add('title_main', 'html', [
                'html' => '<h4 class="mb-3">Phiếu chuyển kho nội bộ</h4>',
            ])

            ->add('row_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('row_1_col_open', 'html', [
                'html' => '<div class="col-12">',
            ])

            ->add('section_document_title', 'html', [
                'html' => '<div class="mb-3 mt-2"><h5 class="mb-1">Thông tin chứng từ</h5><hr class="mt-2"></div>',
            ])

            ->add('row_doc_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('code', TextField::class, [
                'label' => 'Mã phiếu chuyển',
                'attr' => [
                    'placeholder' => 'Nhập mã phiếu chuyển',
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
                    'confirmed' => 'Confirmed',
                    'exporting' => 'Exporting',
                    'importing' => 'Importing',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ],
                'empty_value' => 'Chọn trạng thái',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('transfer_date', 'datePicker', [
                'label' => 'Ngày chuyển kho',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('reason', TextField::class, [
                'label' => 'Lý do chuyển',
                'attr' => [
                    'placeholder' => 'Nhập lý do chuyển kho',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('row_doc_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_1_col_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('row_2_left_open', 'html', [
                'html' => '<div class="col-md-6">',
            ])

            ->add('section_from_warehouse_title', 'html', [
                'html' => '<div class="mb-3 mt-4"><h5 class="mb-1">Kho xuất</h5><hr class="mt-2"></div>',
            ])

            ->add('row_from_warehouse_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('from_warehouse_id', SelectField::class, [
                'label' => 'Kho xuất',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chọn kho xuất',
                'attr' => [
                    'id' => 'warehouse-id',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'required' => true,
            ])

            ->add('requested_by', SelectField::class, [
                'label' => 'Người yêu cầu',
                'choices' => [
                    '0' => '--- Khác (nhập tay) ---',
                ],
                'empty_value' => 'Chọn người yêu cầu',
                'attr' => [
                    'id' => 'requested-by-id',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'required' => true,
            ])

            ->add('requested_by_name', TextField::class, [
                'label' => 'Nhập tên người yêu cầu',
                'attr' => [
                    'placeholder' => 'Nhập tên...',
                    'id' => 'requested-by-name',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6 d-none',
                    'id' => 'requested-by-name-wrapper',
                ],
            ])

            ->add('exported_by', 'number', [
                'label' => 'Người xuất kho',
                'attr' => [
                    'placeholder' => 'ID người xuất kho',
                    'min' => 0,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
            ])

            ->add('row_from_warehouse_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_left_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_right_open', 'html', [
                'html' => '<div class="col-md-6">',
            ])

            ->add('section_to_warehouse_title', 'html', [
                'html' => '<div class="mb-3 mt-4"><h5 class="mb-1">Kho nhập</h5><hr class="mt-2"></div>',
            ])

            ->add('row_to_warehouse_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('to_warehouse_id', SelectField::class, [
                'label' => 'Kho nhập',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chọn kho nhập',
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'required' => true,
            ])

            ->add('approved_by', 'number', [
                'label' => 'Người duyệt',
                'attr' => [
                    'placeholder' => 'ID người duyệt',
                    'min' => 0,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
            ])

            ->add('imported_by', 'number', [
                'label' => 'Người nhập kho',
                'attr' => [
                    'placeholder' => 'ID người nhập kho',
                    'min' => 0,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
            ])

            ->add('note', 'textarea', [
                'label' => 'Ghi chú',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Nhập ghi chú chuyển kho',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-12',
                ],
            ])

            ->add('row_to_warehouse_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_right_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_close', 'html', [
                'html' => '</div>',
            ])

            ->add('form_end', 'html', [
                'html' => '</div></div>',
            ])

            ->add('items_prd', 'html', [
                'html' => view('plugins/inventory::forms.partials.form-table', [
                    'model' => $this->getModel(),
                ])->render(),
            ]);
    }
}
