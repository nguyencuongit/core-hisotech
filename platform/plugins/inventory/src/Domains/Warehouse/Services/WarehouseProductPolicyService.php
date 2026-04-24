<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\Warehouse\Models\WarehouseProduct;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseProductPolicy;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseSetting;
use Botble\Inventory\Domains\Warehouse\Support\WarehousePolicyPresets;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseProductPolicyService
{
    public function upsert(WarehouseProduct $warehouseProduct, array $data): WarehouseProductPolicy
    {
        return DB::transaction(function () use ($warehouseProduct, $data): WarehouseProductPolicy {
            $payload = $this->prepareData($warehouseProduct, $data);

            return WarehouseProductPolicy::query()->updateOrCreate(
                ['warehouse_product_id' => $warehouseProduct->getKey()],
                array_merge($payload, ['warehouse_product_id' => $warehouseProduct->getKey()])
            );
        });
    }

    public function presets(): array
    {
        return WarehousePolicyPresets::all();
    }

    public function applyPreset(WarehouseProduct $warehouseProduct, string $presetCode): WarehouseProductPolicy
    {
        $preset = WarehousePolicyPresets::all()[$presetCode] ?? null;

        if (! $preset) {
            throw ValidationException::withMessages([
                'preset_code' => 'Preset chính sách không hợp lệ.',
            ]);
        }

        return $this->upsert($warehouseProduct, $preset['payload']);
    }

    protected function prepareData(WarehouseProduct $warehouseProduct, array $data): array
    {
        $warehouse = $warehouseProduct->warehouse()->with('setting')->first();
        $settings = $warehouse?->setting ?: app(WarehouseSettingService::class)->firstOrCreateDefault($warehouseProduct->warehouse);

        $allowPallet = (bool) Arr::get($data, 'allow_pallet', false);
        $requirePallet = (bool) Arr::get($data, 'require_pallet', false);
        $requireQc = (bool) Arr::get($data, 'require_qc', false);
        $trackingType = Arr::get($data, 'tracking_type', 'none');
        $isExpirable = (bool) Arr::get($data, 'is_expirable', false);

        if (! $settings->use_pallet) {
            $allowPallet = false;
            $requirePallet = false;
        }

        if (! $settings->use_qc) {
            $requireQc = false;
        }

        if ($requirePallet) {
            $allowPallet = true;
        }

        if ($isExpirable && $trackingType !== 'batch') {
            $trackingType = 'batch';
        }

        if (! in_array($trackingType, ['none', 'batch', 'serial'], true)) {
            $trackingType = 'none';
        }

        return [
            'tracking_type' => $trackingType,
            'is_expirable' => $isExpirable,
            'require_mfg_date' => (bool) Arr::get($data, 'require_mfg_date', false),
            'require_expiry_date' => (bool) Arr::get($data, 'require_expiry_date', false),
            'allow_pallet' => $allowPallet,
            'require_pallet' => $requirePallet,
            'require_qc' => $requireQc,
            'placement_mode' => Arr::get($data, 'placement_mode', 'putaway_after_receipt'),
            'allow_mixed_batch_on_pallet' => (bool) Arr::get($data, 'allow_mixed_batch_on_pallet', false),
            'allow_receive_without_location' => (bool) Arr::get($data, 'allow_receive_without_location', true),
            'is_active' => (bool) Arr::get($data, 'is_active', true),
        ];
    }
}
