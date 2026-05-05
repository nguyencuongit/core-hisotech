<?php

namespace Botble\Inventory\Domains\Transactions\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Inventory\Domains\Transactions\Models\Import as ImportModel;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Enums\DocumentStatusEnum;
use Botble\Inventory\Domains\Transactions\Enums\ImportTypeEnum;
use Botble\Inventory\Domains\Transactions\Enums\PartnerTypeEnum;
use Botble\Inventory\Domains\WarehouseStaff\Repositories\Interfaces\WarehouseStaffAssignmentInterface;
use Botble\Inventory\Services\LocationService;
use Botble\Inventory\Services\ProductFormService;

class ImportForm extends FormAbstract
{
    public function setup(): void
    {
        $model = $this->getModel();
        $import = $model instanceof ImportModel ? $model : null;

        $query = Warehouse::query();
        $warehouseIds = inventory_warehouse_ids();
        if (! inventory_is_super_admin() && ! empty($warehouseIds)) {
            $query->whereIn('id', $warehouseIds);
        }
        $warehouseChoices = $query
            ->pluck('name', 'id')
            ->all();
        $requestedByChoices = [
            '0' => '--- Khác (Nhập tay) ---',
        ];

        if ($import?->warehouse_id) {
            $requestedByChoices += app(WarehouseStaffAssignmentInterface::class)
                ->findByWarehouseIdStaff((int) $import->warehouse_id);
        }

        if ($import?->requested_by && ! array_key_exists($import->requested_by, $requestedByChoices)) {
            $requestedByChoices[$import->requested_by] = $import->requested_by_name ?: sprintf('NhÃ¢n viÃªn #%s', $import->requested_by);
        }
        $locationService = app(LocationService::class);
        $stateChoices = $locationService
            ->showState()
            ->pluck('name', 'id')
            ->all();
        $cityChoices = $import?->province_id
            ? $locationService->showCity((int) $import->province_id)->pluck('name', 'id')->all()
            : [];

        if ($import?->ward_id && ! array_key_exists($import->ward_id, $cityChoices)) {
            $city = $locationService->findCity((int) $import->ward_id);
            $cityChoices[$import->ward_id] = $city?->name ?: sprintf('City #%s', $import->ward_id);
        }

        $products = app(ProductFormService::class)->showProductForm();
        $this
            ->model(ImportModel::class)
            ->template('plugins/inventory::forms.full-width-form')

            ->add('form_start', 'html', [
                'html' => '<div class="card mb-4"><div class="card-body">',
            ])

            ->add('title_main', 'html', [
                'html' => '<h4 class="mb-3">Phiếu nhập kho</h4>',
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

            ->add('doc_code', TextField::class, [
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
                    'placeholder' => 'VD: PO-001, TRF-001...',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('type', SelectField::class, [
                'label' => 'Loại nhập',
                'choices' => ImportTypeEnum::options(),
                'empty_value' => 'Chọn loại nhập',
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

            ->add('received_at', 'datePicker', [
                'label' => 'Ngày nhận hàng',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
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
             * ROW 2: KHO + ĐỐI TƯỢNG
             * =========================
             */
            ->add('row_2_open', 'html', [
                'html' => '<div class="row">',
            ])

            /*
             * LEFT: THÔNG TIN KHO
             */
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

            ->add('lot_no', TextField::class, [
                'label' => 'Số lô',
                'attr' => [
                    'placeholder' => 'Nhập số lô',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'required' => true,
            ])

            ->add('requested_by', SelectField::class, [
                'label' => 'Người yêu cầu',
                'choices' => $requestedByChoices + [
                    '0' => '--- Khác (nhập tay) ---',
                ],
                'empty_value' => 'Chọn người yêu cầu',
                'attr' => [
                    'id' => 'requested-by-id',
                ],
                'selected' => $import?->requested_by,
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

            ->add('expiry_date', 'datePicker', [
                'label' => 'Hạn sử dụng',
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                
            ])

            ->add('row_warehouse_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_warehouse_2_open', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('note', 'textarea', [
                'label' => 'Ghi chú kho',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Nhập ghi chú',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-8',
                ],
            ])

            ->add('row_warehouse_2_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_2_left_close', 'html', [
                'html' => '</div>',
            ])

            /*
             * RIGHT: THÔNG TIN NHÀ CUNG CẤP / ĐỐI TƯỢNG
             */
            ->add('row_2_right_open', 'html', [
                'html' => '<div class="col-md-6">',
            ])

            ->add('section_partner_title', 'html', [
                'html' => '<div class="mb-3 mt-4"><h5 class="mb-1">Thông tin nhà cung cấp / đối tượng</h5><hr class="mt-2"></div>',
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
                    'placeholder' => 'Mã NCC / kho / khách hàng',
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
                    'placeholder' => 'Tên NCC / kho / khách hàng',
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
            ])

            

            ->add('row_partner_2_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_partner_3_open', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('province_id', SelectField::class, [
                'choices' => $stateChoices,
                'empty_value' => 'Chon tinh / thanh pho',
                'attr' => [
                    'id' => 'province-id',
                ],
                'selected' => $import?->province_id,
                'label' => 'Tỉnh / Thành phố',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])
            ->add('ward_id', SelectField::class, [
                'choices' => $cityChoices,
                'empty_value' => 'Chon quan / huyen',
                'attr' => [
                    'id' => 'ward-id',
                ],
                'selected' => $import?->ward_id,
                'label' => 'Quận / Huyện',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])

            ->add('partner_address', 'textarea', [
                'label' => 'Địa chỉ đối tượng',
                'attr' => [
                    'rows' => 3,
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-8',
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
             * ROW 3: THÔNG TIN GIAO NHẬN
             * =========================
             */
            ->add('row_3_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('row_3_col_open', 'html', [
                'html' => '<div class="col-12">',
            ])

            ->add('section_receiver_title', 'html', [
                'html' => '<div class="mb-3 mt-2"><h5 class="mb-1">Thông tin giao nhận</h5><hr class="mt-2"></div>',
            ])

            ->add('row_receiver_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('receiver_name', TextField::class, [
                'label' => 'Người nhận hàng',
                'attr' => [
                    'placeholder' => 'Người nhận hàng tại kho',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])

            ->add('receiver_phone', TextField::class, [
                'label' => 'SĐT người nhận',
                'wrapper' => [
                    'class' => 'form-group col-md-4',
                ],
                'required' => true,
            ])

            // ->add('receiver_id', TextField::class, [
            //     'label' => 'Mã người nhận',
            //     'attr' => [
            //         'placeholder' => 'ID nhân sự nếu có',
            //     ],
            //     'wrapper' => [
            //         'class' => 'form-group col-md-4',
            //     ],
            // ])

            ->add('row_receiver_1_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_3_col_close', 'html', [
                'html' => '</div>',
            ])

            ->add('row_3_close', 'html', [
                'html' => '</div>',
            ])

            /*
             * Hidden fields
             */
            // ->add('partner_id', 'hidden')
            // ->add('requested_by', 'hidden')
            ->add('reference_id', 'hidden')

            ->add('form_end', 'html', [
                'html' => '</div></div>',
            ])

            ->add('items_prd', 'html', [
                'html' => view('plugins/inventory::forms.partials.form-table', [
                    'model' => $products,
                ])->render(),
            ]);
    }
}
