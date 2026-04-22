<?php

namespace Botble\Inventory\Domains\Supplier\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierRequest;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Enums\SupplierStatusEnum;
use Botble\Inventory\Enums\SupplierTypeEnum;

class SupplierForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Supplier::class)
            ->setValidatorClass(SupplierRequest::class)
            ->columns()
            ->add('code', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/inventory::inventory.supplier.code'))
                ->placeholder(trans('plugins/inventory::inventory.supplier.code_placeholder'))
            )
            ->add('name', TextField::class, NameFieldOption::make()
                ->label(trans('plugins/inventory::inventory.supplier.name'))
                ->required()
            )
            ->add('type', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/inventory::inventory.supplier.type.label'))
                ->choices(
                    collect(SupplierTypeEnum::cases())
                        ->mapWithKeys(fn (SupplierTypeEnum $case) => [$case->value => $case->label()])
                        ->all()
                )
                ->defaultValue(SupplierTypeEnum::COMPANY->value)
            )
            ->add('tax_code', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/inventory::inventory.supplier.tax_code'))
            )
            ->add('website', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/inventory::inventory.supplier.website'))
            )
            ->add('status', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/inventory::inventory.supplier.status.label'))
                ->choices(
                    collect(SupplierStatusEnum::cases())
                        ->mapWithKeys(fn (SupplierStatusEnum $case) => [$case->value => $case->label()])
                        ->all()
                )
                ->defaultValue(SupplierStatusEnum::ACTIVE->value)
            )
            ->add('note', TextareaField::class, TextareaFieldOption::make()
                ->label(trans('plugins/inventory::inventory.supplier.note'))
                ->rows(4)
            )
            ->setBreakFieldPoint('status');
    }
}
