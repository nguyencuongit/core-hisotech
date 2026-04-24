<?php

namespace Botble\Inventory\Domains\Transactions\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Transactions\Enums\DocumentStatusEnum;
use Botble\Inventory\Domains\Transactions\Enums\ExportTypeEnum;
use Botble\Inventory\Domains\Transactions\Enums\PartnerTypeEnum;

class ExportForm extends FormAbstract
{

    
    public function setup(): void
    {   
        $query = Warehouse::query();
        $warehouseIds = inventory_warehouse_ids();
        $isAdmin = inventory_is_super_admin();
        if (! $isAdmin && ! empty($warehouseIds)) {
            $query->whereIn('id', $warehouseIds);
        }
        $warehouseChoices = $query
            ->pluck('name', 'id')
            ->all();
        
        $this
            ->model(Export::class)
            ->template('plugins/inventory::forms.full-width-form')

            ->add('form_start', 'html', [
                'html' => '<div class="card mb-4"><div class="card-body">',
            ])

            ->add('title_main', 'html', [
                'html' => '<h4 class="mb-3">Phiếu xuất kho</h4>',
            ])

            /*
            * =========================
            * ROW 1: THÔNG TIN CHỨNG TỪ
            * =========================
            */
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
                'label' => 'Mã phiếu',
                'attr' => [
                    'placeholder' => 'Tự động sinh hoặc nhập mã phiếu',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('reference_code', TextField::class, [
                'label' => 'Mã tham chiếu',
                'attr' => [
                    'placeholder' => 'Ví dụ: SO-001, INV-001...',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('type', SelectField::class, [
                'label' => 'Loại xuất',
                'choices' => ExportTypeEnum::options(),
                'empty_value' => 'Chọn loại xuất',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'id' => 'document-type',
                ],
                'required' => true,
            ])

            ->add('type_note', TextField::class, [
                'label' => 'Mô tả loại nhập khác',
                'wrapper' => [
                    'class' => 'form-group col-md-3 d-none',
                    'id' => 'type-note-wrapper',
                ],
                'attr' => [
                    'placeholder' => 'Nhập loại nhập...',
                ],
            ])

            ->add('status', SelectField::class, [
                'label' => 'Trạng thái',
                'choices' => DocumentStatusEnum::options(),
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

            ->add('document_date', 'datePicker', [
                'label' => 'Ngày chứng từ',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('posting_date', 'datePicker', [
                'label' => 'Ngày hạch toán',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('note', 'textarea', [
                'label' => 'Ghi chú chứng từ',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Nhập ghi chú',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
            ])

            ->add('row_doc_2_close', 'html', [
                'html' => '</div>',
            ])

            

            ->add('row_1_col_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_1_close', 'html', [
                'html' => '</div>',
            ])

            /*
            * =========================
            * ROW 2: KHO + KHÁCH HÀNG / ĐỐI TƯỢNG
            * =========================
            */
            ->add('row_2_open', 'html', [
                'html' => '<div class="row">',
            ])

            // LEFT: THÔNG TIN KHO
            ->add('row_2_left_open', 'html', [
                'html' => '<div class="col-md-6">',
            ])

            ->add('section_warehouse_title', 'html', [
                'html' => '<div class="mb-3 mt-4"><h5 class="mb-1">Thông tin kho</h5><hr class="mt-2"></div>',
            ])

            ->add('row_warehouse_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('warehouse_id', SelectField::class, [
                'label' => 'Kho nhập',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chọn kho',
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

            ->add('row_warehouse_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_warehouse_2_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('delivery_name', TextField::class, [
                'label' => 'Người giao hàng',
                'attr' => [
                    'placeholder' => 'Tên người giao',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'required' => true,
            ])

            ->add('delivery_phone', TextField::class, [
                'label' => 'SĐT người giao',
                'attr' => [
                    'placeholder' => 'Nhập số điện thoại',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'required' => true,
            ])

            ->add('row_warehouse_2_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_left_close', 'html', [
                'html' => '</div>',
            ])

            // RIGHT: THÔNG TIN KHÁCH HÀNG / ĐỐI TƯỢNG
            ->add('row_2_right_open', 'html', [
                'html' => '<div class="col-md-6">',
            ])

            ->add('section_partner_title', 'html', [
                'html' => '<div class="mb-3 mt-4"><h5 class="mb-1">Thông tin khách hàng / đối tượng</h5><hr class="mt-2"></div>',
            ])

            ->add('row_partner_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('partner_type', SelectField::class, [
                'label' => 'Loại đối tượng',
                'choices' => PartnerTypeEnum::options(),
                'empty_value' => 'Chọn loại đối tượng',
                'attr' => [
                    'id' => 'partner-type',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])
            ->add('partner_id', SelectField::class, [
                'label' => 'Chọn đối tác',
                'choices' => [],
                'attr' => [
                    'id' => 'partner-id',
                ],
                'empty_value' => 'Chọn đối tượng',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
            ])

            ->add('partner_code', TextField::class, [
                'label' => 'Mã đối tượng',
                'attr' => [
                    'placeholder' => 'Mã khách hàng / NCC',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])

        

            ->add('row_partner_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_partner_2_open', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('partner_name', TextField::class, [
                'label' => 'Tên đối tượng',
                'attr' => [
                    'placeholder' => 'Tên khách hàng / nhà cung cấp',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])
            ->add('partner_phone', TextField::class, [
                'label' => 'Số điện thoại',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])

            ->add('partner_email', TextField::class, [
                'label' => 'Email',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])


            ->add('row_partner_2_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_partner_3_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('province_id', TextField::class, [
                'label' => 'Tỉnh / Thành phố',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('ward_id', TextField::class, [
                'label' => 'Phường / Xã',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('partner_address', 'textarea', [
                'label' => 'Địa chỉ đối tượng',
                'attr' => [
                    'rows' => 3,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'required' => true,
            ])

            ->add('row_partner_3_close', 'html', [
                'html' => '</div>',
            ])

        

            ->add('row_2_right_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_close', 'html', [
                'html' => '</div>',
            ])

            /*
            * =========================
            * ROW 3: THÔNG TIN GIAO HÀNG / VẬN CHUYỂN
            * =========================
            */
            ->add('row_3_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('row_3_col_open', 'html', [
                'html' => '<div class="col-12">',
            ])

            ->add('section_shipping_title', 'html', [
                'html' => '<div class="mb-3 mt-2"><h5 class="mb-1">Thông tin giao hàng / vận chuyển</h5><hr class="mt-2"></div>',
            ])

            ->add('row_shipping_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('receiver_name', TextField::class, [
                'label' => 'Người nhận',
                'wrapper' => [
                    'class' => 'form-group col-md-2',
                ],
                'required' => true,
            ])

            ->add('receiver_phone', TextField::class, [
                'label' => 'SĐT người nhận',
                'wrapper' => [
                    'class' => 'form-group col-md-2',
                ],
                'required' => true,
            ])

            ->add('shipping_unit', TextField::class, [
                'label' => 'Đơn vị vận chuyển',
                'wrapper' => [
                    'class' => 'form-group col-md-2',
                ],
            ])

            ->add('tracking_code', TextField::class, [
                'label' => 'Mã vận đơn',
                'wrapper' => [
                    'class' => 'form-group col-md-2',
                ],
            ])

            ->add('shipping_fee', 'number', [
                'label' => 'Phí vận chuyển',
                'attr' => [
                    'min' => 0,
                    'step' => '0.01',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-2',
                ],
            ])

            ->add('shipped_at', 'datePicker', [
                'label' => 'Ngày giao hàng',
                'wrapper' => [
                    'class' => 'form-group col-md-2',
                ],
            ])
            ->add('row_shipping_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('receiver_address', 'textarea', [
                'label' => 'Địa chỉ giao hàng',
                'attr' => [
                    'rows' => 3,
                ],
                'wrapper' => [
                    'class' => 'form-group',
                ],
                'required' => true,
            ])

            ->add('row_3_col_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_3_close', 'html', [
                'html' => '</div>',
            ])

            ->add('form_end', 'html', [
                'html' => '</div></div>',
            ])

            ->add('items_prd', 'html', [
                    'html' => view('plugins/inventory::forms.partials.form-table', [
                        'model' => 1,
                    ])->render(),
                ]);
    }
}