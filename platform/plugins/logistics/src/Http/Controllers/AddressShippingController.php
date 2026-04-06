<?php

namespace Botble\Logistics\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Illuminate\Support\Str;
use Botble\Location\Models\State;
use Botble\Logistics\Models\shippingProvinceMapping;
use Botble\Logistics\Models\shippingDistrictMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AddressShippingController extends BaseController
{
    public function provincenID($code)
    {
        try {
            $shipping = ShippingFactory::make($code);
            $data = $shipping->getProvince();
            if (empty($data)) {
                throw new \Exception('Không lấy được danh sách tỉnh');
            }
            foreach ($data as $items) {
                $slug = Str::slug($items['ProvinceName']);
                $id = State::where('slug', $slug)->value('id');
                if (!$id) {
                    continue; 
                }
                shippingProvinceMapping::updateOrCreate(
                    [
                        'state_id' => $id,
                        'provider' => $code,
                    ],
                    [
                        'province_id' => $items['ProvinceID'],
                    ]
                );
            }
            return $this
                ->httpResponse()
                ->setPreviousUrl(route('logistics.providers.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (\Throwable $e) {
            return $this
                ->httpResponse()
                ->setPreviousUrl(route('logistics.providers.index'))
                ->setError()
                ->setMessage('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function DistrictID($code){
        try {
            DB::transaction(function () use ($code){
            $shipping = ShippingFactory::make($code);
            $provinces = shippingProvinceMapping::where('provider', $code)->get();
            $cities = [];
            $mappings = [];

            foreach($provinces as $province){
                $data = $shipping->getDistrict($province->province_id);
                foreach($data as $value){
                    $slug = Str::slug($value['DistrictName']);
                    $cities[] = [
                        'slug' => $slug,
                        'state_id' => $province->state_id,
                        'name' => $value['DistrictName'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $mappings[] = [
                        'slug' => $slug,
                        'provider' => $code,
                        'district_id' => $value['DistrictID'],
                    ];
                }
            }
            DB::table('cities')->upsert(
                $cities,
                ['slug'],
                ['name', 'state_id', 'updated_at']
            );

            $cityMap = DB::table('cities')
            ->whereIn('slug', array_column($cities, 'slug'))
            ->pluck('id', 'slug');
            $mappingData = [];

            foreach ($mappings as $item) {
                if (isset($cityMap[$item['slug']])) {
                    $mappingData[] = [
                        'city_id' => $cityMap[$item['slug']],
                        'provider' => $item['provider'],
                        'district_id' => $item['district_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            DB::table('shipping_district_mappings')->upsert(
                $mappingData,
                ['city_id', 'provider'],
                ['district_id', 'updated_at']
            );
            return $this
                ->httpResponse()
                ->setPreviousUrl(route('logistics.providers.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        });
        } catch (\Throwable $th) {
            return $this
                ->httpResponse()
                ->setPreviousUrl(route('logistics.providers.index'))
                ->setError()
                ->setMessage('Có lỗi xảy ra: ' . $e->getMessage());
        }

    }


    public function addressAdmin(Request $request){
        try {
            $address = $request->except(['_token', '_method']);
            foreach ($address as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => 'logistics_admin_' . $key],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            return $this
                    ->httpResponse()
                    ->setPreviousUrl(route('logistics.providers.index'))
                    ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (\Throwable $th) {
            return $this
                ->httpResponse()
                ->setPreviousUrl(route('logistics.providers.index'))
                ->setError()
                ->setMessage('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}