<?php

namespace Botble\Inventory\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Http\Requests\InventoryRequest;
use Botble\Inventory\Models\Inventory;

class InventoryForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Inventory::class)
            ->setValidatorClass(InventoryRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
