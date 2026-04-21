<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Domains\WarehouseStaff\Http\Requests\WarehousePositionRequest;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehousePosition;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;


class WarehousePositionForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(WarehousePosition::class)
            ->setValidatorClass(WarehousePositionRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('code', TextField::class, TextFieldOption::make()->label('Mã nhân viên')->required())
            ->add('level', NumberField::class, NumberFieldOption::make()
                    ->label('Level')
                )
            ->add('is_active', SelectField::class, SelectFieldOption::make()
                ->label('Trạng thái')
                ->choices([
                    1 => 'Kích hoạt',
                    0 => 'Không Kích hoạt',
                ])
            )
            ->setBreakFieldPoint('is_active');
    }
}
