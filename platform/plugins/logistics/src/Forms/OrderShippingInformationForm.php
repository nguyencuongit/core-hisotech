<?php

namespace Botble\Logistics\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Logistics\Http\Requests\LogisticsRequest;
use Botble\Logistics\Models\Logistics;
use Botble\Logistics\Models\shippingOrderInformation;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;

class OrderShippingInformationForm extends FormAbstract
{
    public function setup(): void
    {
        $this
        ->model(ShippingOrderInformation::class)
        ->setValidatorClass(LogisticsRequest::class)

        ->add('from_title', HtmlField::class, HtmlFieldOption::make()->content('<h3 class="mb-3">Thông tin người gửi</h3>'))

        // ROW 1
        ->add('from_row_start', HtmlField::class, HtmlFieldOption::make()->content('<div class="row my-3">'))
        ->add('from_name', TextField::class, TextFieldOption::make()
            ->label('Họ tên')
            ->wrapperAttributes(['class' => 'col-md-6'])
            ->required()
        )
        ->add('from_phone', TextField::class, TextFieldOption::make()
            ->label('Số điện thoại')
            ->wrapperAttributes(['class' => 'col-md-6'])
            ->required()
        )
        ->add('from_row_end', HtmlField::class, HtmlFieldOption::make()->content('</div>'))

        // ROW 2
        ->add('from_address', TextField::class, TextFieldOption::make()
            ->label('Địa chỉ')
            ->wrapperAttributes(['class' => 'col-12'])
            ->required()
        )

        // ROW 3
        ->add('from_row_start_3', HtmlField::class, HtmlFieldOption::make()->content('<div class="row my-3">'))

        ->add('from_ward', TextField::class, TextFieldOption::make()
            ->label('Phường/Xã')
            ->wrapperAttributes(['class' => 'col-md-4'])
        )
        ->add('from_district', TextField::class, TextFieldOption::make()
            ->label('Quận/Huyện')
            ->wrapperAttributes(['class' => 'col-md-4'])
        )
        ->add('from_province', TextField::class, TextFieldOption::make()
            ->label('Tỉnh/Thành')
            ->wrapperAttributes(['class' => 'col-md-4']))
        ->add('from_row_end_3', HtmlField::class, HtmlFieldOption::make()->content('</div>'))

        // TO
        ->add('to_title', HtmlField::class, HtmlFieldOption::make()->content('<h3 class="mb-3 mt-4">Thông tin người nhận</h3>'))

        // ROW 1
        ->add('to_row_start_1', HtmlField::class, HtmlFieldOption::make()->content('<div class="row my-3">'))

        ->add('to_name', TextField::class, TextFieldOption::make()
            ->label('Họ tên')
            ->wrapperAttributes(['class' => 'col-md-6'])
            ->required()
        )
        ->add('to_phone', TextField::class, TextFieldOption::make()
            ->label('Số điện thoại')
            ->wrapperAttributes(['class' => 'col-md-6'])
            ->required()
        )
        ->add('to_row_end_1', HtmlField::class, HtmlFieldOption::make()->content('</div>'))

        // ROW 2
        ->add('to_address', TextField::class, TextFieldOption::make()
            ->label('Địa chỉ')
            ->wrapperAttributes(['class' => 'col-12'])
            ->required()
        )

        // ROW 3
        ->add('to_row_start_3', HtmlField::class, HtmlFieldOption::make()->content('<div class="row my-3">'))

        ->add('to_ward', TextField::class, TextFieldOption::make()
            ->label('Phường/Xã')
            ->wrapperAttributes(['class' => 'col-md-4'])
        )
        ->add('to_district', TextField::class, TextFieldOption::make()
            ->label('Quận/Huyện')
            ->wrapperAttributes(['class' => 'col-md-4'])
        )
        ->add('to_province', TextField::class, TextFieldOption::make()
            ->label('Tỉnh/Thành')
            ->wrapperAttributes(['class' => 'col-md-4']))
        ->add('to_row_end_3', HtmlField::class, HtmlFieldOption::make()->content('</div>'))


        // SHIPMENT
        ->add('cod_amount', NumberField::class, NumberFieldOption::make()->label('COD amount')->defaultValue(0))
        ->add('weight', NumberField::class, NumberFieldOption::make()->label('Weight'))
        ->add('length', NumberField::class, NumberFieldOption::make()->label('Length'))
        ->add('width', NumberField::class, NumberFieldOption::make()->label('Width'))
        ->add('height', NumberField::class, NumberFieldOption::make()->label('Height'))

        ->setBreakFieldPoint('cod_amount');
    }
}
