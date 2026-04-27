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
                'html' => '<h4 class="mb-3">Phiáº¿u Ä‘Ã³ng gÃ³i</h4>',
            ])

            ->add('row_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('row_1_col_open', 'html', [
                'html' => '<div class="col-12">',
            ])

            ->add('section_document_title', 'html', [
                'html' => '<div class="mb-3 mt-2"><h5 class="mb-1">ThÃ´ng tin phiáº¿u Ä‘Ã³ng hÃ ng</h5><hr class="mt-2"></div>',
            ])

            ->add('row_doc_1_open', 'html', [
                'html' => '<div class="row">',
            ])

            ->add('code', TextField::class, [
                'label' => 'MÃ£ Ä‘Ã³ng hÃ ng',
                'attr' => [
                    'placeholder' => 'Nháº­p mÃ£ Ä‘Ã³ng hÃ ng',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('export_id', SelectField::class, [
                'label' => 'Phiáº¿u xuáº¥t',
                'choices' => $exportChoices,
                'empty_value' => 'Chá»n phiáº¿u xuáº¥t',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('warehouse_id', SelectField::class, [
                'label' => 'Kho',
                'choices' => $warehouseChoices,
                'empty_value' => 'Chá»n kho',
                'attr' => [
                    'id' => 'warehouse-id',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'required' => true,
            ])

            ->add('status', SelectField::class, [
                'label' => 'Tráº¡ng thÃ¡i',
                'choices' => [
                    'draft' => 'Draft',
                    'packing' => 'Packing',
                    'packed' => 'Packed',
                    'shipped' => 'Shipped',
                    'cancelled' => 'Cancelled',
                ],
                'empty_value' => 'Chá»n tráº¡ng thÃ¡i',
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
                'label' => 'NgÆ°á»i Ä‘Ã³ng gÃ³i',
                'choices' => [
                    '0' => '--- KhÃ¡c (nháº­p tay) ---',
                ],
                'empty_value' => 'Chá»n nhÃ¢n viÃªn',
                'attr' => [
                    'id' => 'requested-by-id',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('packer_name', TextField::class, [
                'label' => 'Nháº­p tÃªn ngÆ°á»i Ä‘Ã³ng gÃ³i',
                'attr' => [
                    'placeholder' => 'Nháº­p tÃªn...',
                    'id' => 'requested-by-name',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-3 d-none',
                    'id' => 'requested-by-name-wrapper',
                ],
            ])

            ->add('packed_at', 'datePicker', [
                'label' => 'NgÃ y Ä‘Ã³ng gÃ³i',
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
            ])

            ->add('total_packages', 'number', [
                'label' => 'Tá»•ng sá»‘ kiá»‡n hÃ ng',
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
                'label' => 'Tá»•ng trá»ng lÆ°á»£ng',
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
                'label' => 'Ghi chÃº',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Nháº­p ghi chÃº',
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
