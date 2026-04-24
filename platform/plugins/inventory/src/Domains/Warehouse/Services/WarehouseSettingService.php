<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WarehouseSettingService
{
    public function upsert(Warehouse $warehouse, array $data): WarehouseSetting
    {
        return DB::transaction(function () use ($warehouse, $data): WarehouseSetting {
            $setting = WarehouseSetting::query()->firstOrNew([
                'warehouse_id' => $warehouse->getKey(),
            ]);

            $setting->fill([
                'warehouse_mode' => Arr::get($data, 'warehouse_mode', 'simple'),
                'use_pallet' => (bool) Arr::get($data, 'use_pallet', false),
                'require_pallet' => (bool) Arr::get($data, 'require_pallet', false),
                'use_qc' => (bool) Arr::get($data, 'use_qc', false),
                'use_batch' => (bool) Arr::get($data, 'use_batch', false),
                'use_serial' => (bool) Arr::get($data, 'use_serial', false),
                'use_map' => (bool) Arr::get($data, 'use_map', false),
                'default_receiving_location_id' => Arr::get($data, 'default_receiving_location_id') ?: null,
                'default_waiting_putaway_location_id' => Arr::get($data, 'default_waiting_putaway_location_id') ?: null,
                'default_qc_location_id' => Arr::get($data, 'default_qc_location_id') ?: null,
                'default_damaged_location_id' => Arr::get($data, 'default_damaged_location_id') ?: null,
                'default_rejected_location_id' => Arr::get($data, 'default_rejected_location_id') ?: null,
            ]);

            if ($setting->require_pallet) {
                $setting->use_pallet = true;
            }

            $setting->warehouse_id = $warehouse->getKey();
            $setting->save();

            return $setting->refresh();
        });
    }

    public function firstOrCreateDefault(Warehouse $warehouse): WarehouseSetting
    {
        return WarehouseSetting::query()->firstOrCreate(
            ['warehouse_id' => $warehouse->getKey()],
            [
                'warehouse_mode' => 'simple',
                'use_pallet' => false,
                'require_pallet' => false,
                'use_qc' => false,
                'use_batch' => false,
                'use_serial' => false,
                'use_map' => false,
            ]
        );
    }
}
