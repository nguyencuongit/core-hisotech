<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Services;

use Botble\Inventory\Domains\Warehouse\Services\WarehouseSettingService;
use Botble\Inventory\Domains\Warehouse\Support\WarehousePolicyPresets;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductPolicyDTO;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProduct;
use Botble\Inventory\Domains\WarehouseProduct\Models\WarehouseProductPolicy;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseReadInterface;
use Botble\Inventory\Domains\WarehouseProduct\Repositories\Interfaces\WarehouseProductPolicyInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseProductPolicyService
{
    public function __construct(
        protected WarehouseProductPolicyInterface $policies,
        protected WarehouseReadInterface $warehouses,
        protected WarehouseSettingService $warehouseSettingService,
    ) {
    }

    public function save(WarehouseProduct $warehouseProduct, WarehouseProductPolicyDTO $dto): WarehouseProductPolicy
    {
        if ($dto->presetCode) {
            return $this->applyPreset($warehouseProduct, $dto->presetCode);
        }

        return $this->upsert($warehouseProduct, $dto->payload);
    }

    public function upsert(WarehouseProduct $warehouseProduct, array $data): WarehouseProductPolicy
    {
        return DB::transaction(function () use ($warehouseProduct, $data): WarehouseProductPolicy {
            return $this->policies->upsertForWarehouseProduct(
                $warehouseProduct,
                $this->prepareData($warehouseProduct, $data)
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
                'preset_code' => 'Invalid policy preset.',
            ]);
        }

        return $this->upsert($warehouseProduct, $preset['payload']);
    }

    protected function prepareData(WarehouseProduct $warehouseProduct, array $data): array
    {
        $warehouse = $this->warehouses->findWithSetting((int) $warehouseProduct->warehouse_id);
        $settings = $warehouse->setting ?: $this->warehouseSettingService->firstOrCreateDefault($warehouse);

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
