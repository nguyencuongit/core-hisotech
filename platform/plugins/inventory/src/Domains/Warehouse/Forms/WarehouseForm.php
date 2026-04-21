<?php

namespace Botble\Inventory\Domains\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Http\Requests\WarehouseRequest;
class WarehouseForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Warehouse::class)
            ->setValidatorClass(WarehouseRequest::class)
            ->add('general_title', HtmlField::class, HtmlFieldOption::make()
                ->content('<h3 class="mb-3">Thông tin kho</h3>')
            )

            // Row 1
            ->add('row_1_start', HtmlField::class, HtmlFieldOption::make()
                ->content('<div class="row my-3">')
            )

            ->add('name', TextField::class, NameFieldOption::make()
                ->label('Tên kho')
                ->wrapperAttributes(['class' => 'col-md-6'])
                ->required()
            )

            ->add('code', TextField::class, TextFieldOption::make()
                ->label('Mã kho')
                ->wrapperAttributes(['class' => 'col-md-6'])
                ->required()
            )

            ->add('row_1_end', HtmlField::class, HtmlFieldOption::make()
                ->content('</div>')
            )

            // Row 2
            ->add('row_2_start', HtmlField::class, HtmlFieldOption::make()
                ->content('<div class="row my-3">')
            )

            ->add('type', TextField::class, TextFieldOption::make()
                ->label('Kiểu kho')
                ->wrapperAttributes(['class' => 'col-md-6'])
            )

            

            ->add('row_2_end', HtmlField::class, HtmlFieldOption::make()
                ->content('</div>')
            )

            // Row 3
            ->add('row_3_start', HtmlField::class, HtmlFieldOption::make()
                ->content('<div class="row my-3">')
            )

            ->add('phone', TextField::class, TextFieldOption::make()
                ->label('Số điện thoại')
                ->wrapperAttributes(['class' => 'col-md-6'])
                ->required()
            )

            ->add('email', TextField::class, TextFieldOption::make()
                ->label('Email')
                ->wrapperAttributes(['class' => 'col-md-6'])
            )

            ->add('row_3_end', HtmlField::class, HtmlFieldOption::make()
                ->content('</div>')
            )

            // Row 4
            ->add('address', TextField::class, TextFieldOption::make()
                ->label('Địa chỉ')
                ->wrapperAttributes(['class' => 'col-12'])
                ->required()
            )

            // Row 5
            ->add('description', TextareaField::class, TextareaFieldOption::make()
                ->label('Ghi chú')
                ->rows(4)
                ->wrapperAttributes(['class' => 'col-12 mt-3'])
            )
            ->add('status', SelectField::class, SelectFieldOption::make()
                ->label('Trạng thái')
                ->choices([
                    1 => 'Kích hoạt',
                    0 => 'Không kích hoạt',
                ])
            )
            ->setBreakFieldPoint('status');
    }
}