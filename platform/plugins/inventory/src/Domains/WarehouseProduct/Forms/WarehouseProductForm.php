<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Forms;

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\CheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductRequest;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;

class WarehouseProductForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(WarehouseProduct::class)
            ->setValidatorClass(WarehouseProductRequest::class)
            ->columns()
            ->add('product_id', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/inventory::inventory.warehouse_product.product'))
                ->required()
            )
            ->add('product_variation_id', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/inventory::inventory.warehouse_product.product_variation'))
            )
            ->add('supplier_id', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/inventory::inventory.warehouse_product.supplier'))
            )
            ->add('supplier_product_id', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/inventory::inventory.warehouse_product.supplier_product'))
            )
            ->add('is_active', CheckboxField::class, CheckboxFieldOption::make()
                ->label(trans('plugins/inventory::inventory.warehouse_product.is_active'))
                ->defaultValue(true)
            )
            ->add('note', TextareaField::class, TextareaFieldOption::make()
                ->label(trans('plugins/inventory::inventory.warehouse_product.note'))
                ->rows(4)
            );
    }
}
