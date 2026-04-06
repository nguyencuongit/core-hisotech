<?php

namespace Botble\Logistics\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\Models\shippingDistrictMapping;


class GetAddressController extends BaseController
{
    
    public function getDistricts(Request $request){
        $request->validate([
            'province_id' => 'required|integer|exists:states,id'
        ]);
        $provinceId = $request->province_id;
        $districts = DB::table('cities')->where('state_id',$provinceId)->pluck('name', 'id');
        return $districts;
    }

    public function getWard(Request $request){
        $wardId = $request['district_id'];
        $code = $request['code'];
        $ward_id = shippingDistrictMapping::where('provider',$code)->where('city_id',$wardId)->value('district_id');

        $shipping = ShippingFactory::make($code);
        $data = $shipping->getWard($ward_id);
        return response()->json(
                collect($data)->pluck('WARDS_NAME', 'WARDS_ID')
            );
    }
}