<?php

namespace Botble\Inventory\Domains\Return\Forms;

use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\Return\Models\InventoryReturn;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

class ReturnForm extends FormAbstract
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
            ->model(InventoryReturn::class)
            ->template('plugins/inventory::forms.full-width-form')

            ->add('form_start', 'html', [
                'html' => '<div class="card mb-4"><div class="card-body">',
            ])

            ->add('title_main', 'html', [
                'html' => '<h4 class="mb-3">Phiếu trả hàng</h4>',
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
                'label' => 'Mã phiếu trả',
                'attr' => [
                    'placeholder' => 'Nhập mã phiếu trả',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('type', SelectField::class, [
                'label' => 'Loại trả hàng',
                'choices' => [
                    'customer_return' => 'Khách trả về kho',
                    'supplier_return' => 'Trả nhà cung cấp',
                ],
                'empty_value' => 'Chọn loại trả hàng',
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
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ],
                'empty_value' => 'Chọn trạng thái',
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

            ->add('row_doc_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_doc_2_open', 'html', [
                'html' => '<div class="row">',
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
                    'class' => 'form-group col-md-3',
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
                    'class' => 'form-group col-md-3 d-none',
                    'id' => 'requested-by-name-wrapper',
                ],
            ])

            ->add('approved_by', 'number', [
                'label' => 'Người duyệt',
                'attr' => [
                    'placeholder' => 'ID người duyệt',
                    'min' => 0,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('reason', TextField::class, [
                'label' => 'Lý do',
                'attr' => [
                    'placeholder' => 'Nhập lý do trả hàng',
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

            ->add('row_2_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('row_2_left_open', 'html', [
                'html' => '<div class="col-md-6">',
            ])

            ->add('section_partner_title', 'html', [
                'html' => '<div class="mb-3 mt-4"><h5 class="mb-1">Thông tin đối tượng</h5><hr class="mt-2"></div>',
            ])

            ->add('row_partner_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('partner_type', SelectField::class, [
                'label' => 'Loại đối tượng',
                'choices' => [
                    'customer' => 'Customer',
                    'supplier' => 'Supplier',
                ],
                'empty_value' => 'Chọn loại đối tượng',
                'attr' => [
                    'id' => 'partner-type',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
            ])

            ->add('partner_id', SelectField::class, [
                'label' => 'Chọn đối tượng',
                'choices' => [],
                'empty_value' => 'Chọn đối tượng',
                'attr' => [
                    'id' => 'partner-id',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
            ])

            ->add('partner_code', TextField::class, [
                'label' => 'Mã đối tượng',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
            ])

            ->add('partner_name', TextField::class, [
                'label' => 'Tên đối tượng',
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
            ])

            ->add('partner_phone', TextField::class, [
                'label' => 'Số điện thoại',
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
            ])

            ->add('row_partner_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_left_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_right_open', 'html', [
                'html' => '<div class="col-md-6">',
            ])

            ->add('section_reference_title', 'html', [
                'html' => '<div class="mb-3 mt-4"><h5 class="mb-1">Chứng từ tham chiếu</h5><hr class="mt-2"></div>',
            ])

            ->add('row_reference_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('reference_type', SelectField::class, [
                'label' => 'Loại tham chiếu',
                'choices' => [
                    'export' => 'Export',
                    'import' => 'Import',
                    'order' => 'Order',
                ],
                'empty_value' => 'Chọn loại tham chiếu',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
            ])

            ->add('reference_id', 'number', [
                'label' => 'ID tham chiếu',
                'attr' => [
                    'min' => 0,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
            ])

            ->add('reference_code', TextField::class, [
                'label' => 'Mã tham chiếu',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
            ])

            ->add('row_reference_close', 'html', [
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
