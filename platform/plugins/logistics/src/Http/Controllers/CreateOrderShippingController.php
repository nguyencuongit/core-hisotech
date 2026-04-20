<?php

namespace Botble\Logistics\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Logistics\Models\shippingOrder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Logistics\Tables\OrderShippingTable;
use Botble\Logistics\Forms\OrderShippingInformationForm;
use Illuminate\Http\Request;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Location\Models\State;
use Botble\Ecommerce\Models\Address;
use Botble\Marketplace\Models\Store;
use Botble\Logistics\Http\Requests\CreateOrderShippingRequest;

//erro
use Botble\Logistics\Exceptions\ShippingException;


use Botble\Logistics\DTO\ShippingCreateDTO;


use Botble\Logistics\Usecase\OrderShippingUsecase;
use Botble\Logistics\Usecase\CreateShippingUsecase;
use Botble\Logistics\Usecase\CancelShippingOrderUsecase;

class CreateOrderShippingController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/logistics::logistics.name')), route('logistics.shipping.order.index'));
    }

    public function index(OrderShippingTable $table)
    {
        $this->pageTitle(trans('plugins/logistics::logistics.name'));

         return $table->render("plugins/logistics::admin.shipping.order.index");
    }

    public function create(Request $orderShipping, OrderShippingUsecase $OrderShippingUsecase)
    {
        $this->pageTitle(trans('plugins/logistics::logistics.create'));
        $key = $orderShipping->keys()[0];
        $inf_from = $OrderShippingUsecase->informationFrom($key);
        $inf_to = $OrderShippingUsecase->informationTo($key);
        $products = $OrderShippingUsecase->products($key);
        $shippingUnit = $OrderShippingUsecase->shippingUnit($key);
        $shippingProviders = $OrderShippingUsecase->shippingProvider();
        $states = State::all();
        $type = "create-shipping";
        return view("plugins/logistics::admin.shipping.order.form",compact('inf_from', 'inf_to', 'states', 'shippingProviders', 'products','key','shippingUnit' ));
    }

    public function store(CreateOrderShippingRequest $request,CreateShippingUsecase $createShippingUsecase)
    {
        try {

            $data_form_create = ShippingCreateDTO::fromRequest($request);

            $orderShipping = $createShippingUsecase->createShipping($data_form_create);
            return $this
                ->httpResponse()
                ->setPreviousUrl(route('logistics.index'))
                ->setNextUrl(route('logistics.shipping.order.index', ['status' => 'CREATED']))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (ShippingException $e) {
            return $this
            ->httpResponse()
            ->setError()
            ->setMessage($e->getMessage());
        }
    }

    public function edit(OrderShippingUsecase $OrderShippingUsecase)
    {
        try {
           $order_id = request()->route('shipping_create');
            $information = $OrderShippingUsecase->informationOrderShipping($order_id);


            $status = $information->status;
            $color = match($status) {
                'delivered' => 'text-success',
                'shipping'  => 'text-primary',
                'cancel'    => 'text-danger',
                'failed'    => 'text-danger',
                default     => 'text-warning',
            };
            $icon = match($status) {
                'delivered' => 'fa-check-circle',
                'shipping'  => 'fa-truck',
                'cancel'    => 'fa-times-circle',
                'failed'    => 'fa-exclamation-circle',
                default     => 'fa-clock',
            };


            $this->pageTitle(trans('core/base::forms.edit_item'));
            return view("plugins/logistics::admin.shipping.order.form_show", compact('information','order_id','icon','color'));
        } catch (ShippingException $e) {
             return $this
            ->httpResponse()
            ->setPreviousUrl(url()->previous())
            ->setError()
            ->setMessage($e->getMessage());
        }
        

    }

    // public function update(shippingOrder $shippingOrder, Request $request)
    // {
    //     dd($request);
    //     OrderShippingInformationForm::createFromModel($shippingOrder)
    //         ->setRequest($request)
    //         ->save();

    //     return $this
    //         ->httpResponse()
    //         ->setPreviousUrl(route('logistics.index'))
    //         ->setMessage(trans('core/base::notices.update_success_message'));
    // }

    public function destroy(CancelShippingOrderUsecase $cancelShippingOrderUsecase, Request $request)
    {
        try {
            $order_id = request()->route('shipping_create');
            $cancel = $cancelShippingOrderUsecase->cancelOrder($request->provider_code, $request->code, $order_id);

            return $this
                    ->httpResponse()
                    ->setPreviousUrl(route('logistics.index'))
                    ->setNextUrl(route('logistics.shipping.order.index', ['status' => 'CANCELLED']))
                ->setMessage(trans($cancel->message));
        } catch (ShippingException $e) {
             return $this
            ->httpResponse()
            ->setPreviousUrl(url()->previous())
            ->setError()
            ->setMessage($e->getMessage());
        }
        
    }
}
