<?php

namespace Botble\Logistics\Http\Controllers;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Logistics\Usecase\ShippingFeeUsecase;
use Illuminate\Http\Request;
class ShippingFeeController extends BaseController
{
    public function shippingFee(Request $request, ShippingFeeUsecase $ShippingFeeUsecase){
        
        $data=$request->all();
        $fee = $ShippingFeeUsecase->shippingFee($data);

        return response()->json([
            'fee' => $fee
        ]);
    }

}