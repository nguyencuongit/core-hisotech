<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Inventory\Http\Requests\InventoryRequest;

use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaff;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehousePosition;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;

use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Illuminate\Support\Facades\DB;


class WarehouseStaffForm extends FormAbstract
{
    public function setup(): void
    {
        $model = $this->getModel();
        $warehouseStaff = $model instanceof WarehouseStaff ? $model : null;

        $users = DB::table('users')
        ->select(DB::raw("id, CONCAT(first_name, ' ', last_name) as full_name"))
        ->pluck('full_name', 'id')
        ->toArray();

        $warehouser = Warehouse::query()->pluck('name', 'id')->toArray();
        $position = WarehousePosition::query()->pluck('name', 'id')->toArray();
        $selectedWarehouses = $warehouseStaff?->getKey()
            ? $warehouseStaff->assignments()->pluck('warehouse_id')->all()
            : [];
        $this
            ->model(WarehouseStaff::class)
            // ->setValidatorClass(InventoryRequest::class)
            ->add('user_id', SelectField::class, SelectFieldOption::make()
                ->label('Nhân viên hệ thống')
                ->choices([
                    '' => 'Không có tài khoản', 
                ] + $users)
            )
            ->add('full_name', TextField::class, NameFieldOption::make()->required())
            ->add('staff_code', TextField::class, TextFieldOption::make()->label('Mã nhân viên')->required())
            ->add('phone', TextField::class, TextFieldOption::make()->label('Số điện thoại')->required())
            ->add('email', TextField::class, TextFieldOption::make()->label('email')->required())
            
            ->add('warehouse_id[]', SelectField::class, SelectFieldOption::make()->required()
                ->label('Kho')
                ->choices($warehouser)
                ->selected($selectedWarehouses)
                ->multiple()
                ->searchable()
            )
            ->add('position', SelectField::class, SelectFieldOption::make()
                ->label('Chức vụ')
                ->choices($position)
            )

            ->add('status', SelectField::class, SelectFieldOption::make()
                ->label('Trạng thái')
                ->choices([
                    1 => 'Kích hoạt',
                    0 => 'Không Kích hoạt',
                ])
            )
            ->setBreakFieldPoint('status');
    }
}
