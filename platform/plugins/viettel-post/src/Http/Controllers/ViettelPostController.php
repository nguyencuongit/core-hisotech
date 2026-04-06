<?php
namespace Botble\ViettelPost\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Botble\Marketplace\Models\Store;
use Botble\Setting\Supports\SettingStore;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\ViettelPost\Services\ViettelPostApiService;

class ViettelPostController extends BaseController
{
    public function __construct(
        protected ViettelPostApiService $apiService,
        protected SettingStore $settingStore
    ) {
    }

    
    public function settings()
    {
        page_title()->setTitle(trans('plugins/viettel-post::viettel-post.settings'));

        $services = [];

        if (setting('viettel_post_status')) {
            try {
                $services = $this->apiService->getServices();
            } catch (\Exception $e) {
                // Ignore error, services will be empty
            }
        }

        return view('plugins/viettel-post::settings.index', compact('services'));
    }

   
    public function saveSettings(Request $request, BaseHttpResponse $response)
    {
        Log::info('ViettelPost saveSettings called', $request->all());

        $settings = [
            'viettel_post_status'               => $request->input('viettel_post_status') ? '1' : '0',
            'viettel_post_username'             => $request->input('viettel_post_username'),
            'viettel_post_password'             => $request->input('viettel_post_password'),
            'viettel_post_partner_code'         => $request->input('viettel_post_partner_code'),
            'viettel_post_shop_id'              => $request->input('viettel_post_shop_id'),
            'viettel_post_customer_id'          => $request->input('viettel_post_customer_id'),
            'viettel_post_default_service'      => $request->input('viettel_post_default_service'),
            'viettel_post_use_store_address'    => $request->input('viettel_post_use_store_address') ? '1' : '0',
            'viettel_post_sender_name'          => $request->input('viettel_post_sender_name'),
            'viettel_post_sender_phone'         => $request->input('viettel_post_sender_phone'),
            'viettel_post_sender_address'       => $request->input('viettel_post_sender_address'),
            'viettel_post_sender_province_id'   => $request->input('viettel_post_sender_province_id'),
            'viettel_post_sender_district_id'   => $request->input('viettel_post_sender_district_id'),
            'viettel_post_sender_ward_id'       => $request->input('viettel_post_sender_ward_id'),
            'viettel_post_auto_create_shipment' => $request->input('viettel_post_auto_create_shipment') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            $this->settingStore->set($key, $value);
        }

        $this->settingStore->save();

        Log::info('ViettelPost settings saved to DB', ['enabled' => DB::table('settings')->where('key', 'viettel_post_status')->value('value')]);

        return $response
            ->setPreviousUrl(url()->previous())
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    
    public function getProvincesFromApi()
    {
        $provinces = $this->apiService->getProvinces();

        $formatted = [];
        foreach ($provinces as $province) {
            $formatted[] = [
                'id'   => $province['PROVINCE_ID'] ?? $province['id'] ?? 0,
                'name' => $province['PROVINCE_NAME'] ?? $province['name'] ?? '',
            ];
        }

        return response()->json($formatted);
    }

    
    public function getDistrictsByProvince(Request $request)
    {
        $provinceId = $request->get('province_id');

        if (! $provinceId) {
            return response()->json([]);
        }

        $districts = $this->apiService->getDistricts($provinceId);

        $formatted = [];
        foreach ($districts as $district) {
            $formatted[] = [
                'id'   => $district['DISTRICT_ID'] ?? $district['id'] ?? 0,
                'name' => $district['DISTRICT_NAME'] ?? $district['name'] ?? '',
            ];
        }

        return response()->json($formatted);
    }

   
    public function listInventories()
    {
        try {
            $inventories = $this->apiService->getInventories();
            return response()->json([
                'error' => false,
                'data'  => $inventories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Không thể lấy danh sách kho hàng: ' . $e->getMessage(),
            ]);
        }
    }

    
    public function registerInventory(Request $request, $storeId)
    {
        try {
            $store = Store::findOrFail($storeId);

            $result = $this->apiService->registerInventory([
                'name'        => $request->input('name'),
                'phone'       => $request->input('phone'),
                'address'     => $request->input('address'),
                'province_id' => $request->input('province_id'),
                'district_id' => $request->input('district_id'),
                'ward_id'     => $request->input('ward_id'),
            ]);

            if ($result && isset($result['GROUPADDRESS_ID'])) {
                $store->viettelpost_groupaddress_id = $result['GROUPADDRESS_ID'];
                $store->viettelpost_inventory_name  = $request->input('name');
                $store->save();

                return response()->json([
                    'error'   => false,
                    'message' => 'Đăng ký kho hàng thành công!',
                ]);
            }

            return response()->json([
                'error'   => true,
                'message' => $result['message'] ?? 'Không thể đăng ký kho hàng',
            ]);
        } catch (\Exception $e) {
            Log::error('ViettelPost Register Inventory Error: ' . $e->getMessage());
            return response()->json([
                'error'   => true,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ]);
        }
    }

  
    public function linkInventory(Request $request, $storeId)
    {
        try {
            $store = \Botble\Marketplace\Models\Store::findOrFail($storeId);

            $store->viettelpost_groupaddress_id = $request->input('groupaddress_id');
            $store->viettelpost_inventory_name  = $request->input('inventory_name');
            $store->save();

            return response()->json([
                'error'   => false,
                'message' => 'Liên kết kho hàng thành công!',
            ]);
        } catch (\Exception $e) {
            Log::error('ViettelPost Link Inventory Error: ' . $e->getMessage());
            return response()->json([
                'error'   => true,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ]);
        }
    }
}