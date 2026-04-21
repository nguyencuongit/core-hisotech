<?php

namespace Botble\Inventory\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\FormField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Enums\SupplierStatusEnum;
use Botble\Inventory\Enums\SupplierTypeEnum;
use Botble\Inventory\Http\Requests\SupplierRequest;
use Botble\Inventory\Models\Supplier;

class SupplierForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Supplier::class)
            ->setValidatorClass(SupplierRequest::class)
            ->columns()
            ->add('code', TextField::class, [
                'label' => trans('plugins/inventory::inventory.supplier.code'),
                'attr' => [
                    'placeholder' => trans('plugins/inventory::inventory.supplier.code_placeholder'),
                ],
            ])
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('type', SelectField::class, [
                'label' => trans('plugins/inventory::inventory.supplier.type.label'),
                'choices' => collect(SupplierTypeEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all(),
                'selected' => SupplierTypeEnum::COMPANY->value,
            ])
            ->add('tax_code', TextField::class, ['label' => trans('plugins/inventory::inventory.supplier.tax_code')])
            ->add('website', TextField::class, ['label' => trans('plugins/inventory::inventory.supplier.website')])
            ->add('status', SelectField::class, [
                'label' => trans('plugins/inventory::inventory.supplier.status.label'),
                'choices' => collect(SupplierStatusEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all(),
                'selected' => SupplierStatusEnum::ACTIVE->value,
            ])
            ->add('note', HtmlField::class, [
                'html' => '<textarea name="note" class="form-control" rows="4">' . e(old('note', $this->getModel()->note ?? '')) . '</textarea>',
            ])
            ->setBreakFieldPoint('status');
    }
}
