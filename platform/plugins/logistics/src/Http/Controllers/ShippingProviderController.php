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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Botble\Logistics\Models\shippingProvider;

class ShippingProviderController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/logistics::logistics.name')), route('logistics.index'));
    }

    public function index(LogisticsTable $table)
    {
        $shippingProvider = shippingProvider::all();
        $nameProvider = [];
        foreach($shippingProvider as $item){
            $nameProvider[$item->id] = $item->name;
        }
        $address_admin = DB::table('settings')
            ->where('key', 'like', 'logistics_admin_%')
            ->pluck('value', 'key');
        $this->pageTitle(trans('plugins/logistics::logistics.name'));

        $states = DB::table('states')->get();

        return view('plugins/logistics::admin.shipping.settings.index', compact('shippingProvider','nameProvider','address_admin','states'));
    }

    

    public function update(shippingProvider $provider, Request $request)
    {
        $data = $request->data;
        $config = [];
        foreach ($data as $item) {
            if (!empty($item['key'])) {
                $config[$item['key']] = $item['value'];
            }
        }
        $provider->update([
            'is_active' => $request->active,
            'information' => $config,
        ]);
        
        Cache::forget('provider:' . $provider->code);
        return $this
            ->httpResponse()
            ->setPreviousUrl(route('logistics.providers.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    
    // public function destroy(Logistics $logistics)
    // {
    //     return DeleteResourceAction::make($logistics);
    // }
    // public function create()
    // {
    //     $this->pageTitle(trans('plugins/logistics::logistics.create'));

    //     return LogisticsForm::create()->renderForm();
    // }

    // public function store(LogisticsRequest $request)
    // {
    //     $form = LogisticsForm::create()->setRequest($request);

    //     $form->save();

    //     return $this
    //         ->httpResponse()
    //         ->setPreviousUrl(route('logistics.index'))
    //         ->setNextUrl(route('logistics.edit', $form->getModel()->getKey()))
    //         ->setMessage(trans('core/base::notices.create_success_message'));
    // }

    // public function edit(Logistics $logistics)
    // {
    //     $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $logistics->name]));

    //     return LogisticsForm::createFromModel($logistics)->renderForm();
    // }
}
