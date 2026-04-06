<?php

namespace Botble\Logistics\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Logistics\Http\Requests\LogisticsRequest;
use Botble\Logistics\Models\Logistics;

class LogisticsForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Logistics::class)
            ->setValidatorClass(LogisticsRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
