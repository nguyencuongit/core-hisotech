<?php

namespace Botble\Logistics\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Logistics\Http\Requests\LogisticsRequest;
use Botble\Logistics\Models\Logistics;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Logistics\Tables\LogisticsTable;
use Botble\Logistics\Forms\LogisticsForm;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\DTO\ShippingData;

class LogisticsController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/logistics::logistics.name')), route('logistics.index'));
    }

    public function index(LogisticsTable $table)
    {
        // $shipping = ShippingFactory::make('viettel');
        // // $shipping = ShippingFactory::make('ghn');

        // $data = new ShippingData(
        //     fromProvinceID: 'HN',
        // );

        // $fee = $shipping->calculateFee($data);

        // dd($fee);
        $this->pageTitle(trans('plugins/logistics::logistics.name'));

        return view('plugins/logistics::admin.shipping.settings.index');
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/logistics::logistics.create'));

        return LogisticsForm::create()->renderForm();
    }

    public function store(LogisticsRequest $request)
    {
        $form = LogisticsForm::create()->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('logistics.index'))
            ->setNextUrl(route('logistics.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Logistics $logistics)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $logistics->name]));

        return LogisticsForm::createFromModel($logistics)->renderForm();
    }

    public function update(Logistics $logistics, LogisticsRequest $request)
    {
        LogisticsForm::createFromModel($logistics)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('logistics.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Logistics $logistics)
    {
        return DeleteResourceAction::make($logistics);
    }
}
