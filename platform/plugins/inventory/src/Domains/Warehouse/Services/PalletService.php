<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\PalletMovement;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Support\PalletLocationRules;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PalletService
{
    public function create(Warehouse $warehouse, array $data): Pallet
    {
        return DB::transaction(function () use ($warehouse, $data): Pallet {
            $payload = $this->prepareData($warehouse, $data);
            $pallet = Pallet::query()->create($payload + ['created_by' => auth()->id()]);

            PalletMovement::query()->create([
                'pallet_id' => $pallet->getKey(),
                'warehouse_id' => $warehouse->getKey(),
                'from_location_id' => null,
                'to_location_id' => $payload['current_location_id'] ?? null,
                'movement_type' => 'create',
                'note' => Arr::get($data, 'note'),
                'created_by' => auth()->id(),
            ]);

            return $pallet;
        });
    }

    public function move(Warehouse $warehouse, Pallet $pallet, ?int $toLocationId, ?string $note = null): Pallet
    {
        return DB::transaction(function () use ($warehouse, $pallet, $toLocationId, $note): Pallet {
            $this->ensureBelongsToWarehouse($warehouse, $pallet);
            $this->ensureLocationBelongsToWarehouse($warehouse, $toLocationId);

            $from = $pallet->current_location_id;

            if ((int) ($from ?? 0) === (int) ($toLocationId ?? 0)) {
                return $pallet->refresh();
            }

            $fromLocation = $from ? WarehouseLocation::query()->find($from) : null;
            $toLocation = $toLocationId ? WarehouseLocation::query()->find($toLocationId) : null;

            app(StockLedgerService::class)->movePallet($pallet, $fromLocation, $toLocation, 'internal_move', $note);

            $pallet->update(['current_location_id' => $toLocationId]);

            PalletMovement::query()->create([
                'pallet_id' => $pallet->getKey(),
                'warehouse_id' => $warehouse->getKey(),
                'from_location_id' => $from,
                'to_location_id' => $toLocationId,
                'movement_type' => 'internal_move',
                'note' => $note,
                'created_by' => auth()->id(),
            ]);

            return $pallet->refresh();
        });
    }

    public function deleteOrDeactivate(Warehouse $warehouse, Pallet $pallet): bool
    {
        $this->ensureBelongsToWarehouse($warehouse, $pallet);

        if ($this->hasUsage($pallet)) {
            return (bool) $pallet->update(['status' => 'locked']);
        }

        return (bool) $pallet->delete();
    }

    public function listByWarehouse(Warehouse $warehouse)
    {
        return Pallet::query()
            ->with('currentLocation')
            ->where('warehouse_id', $warehouse->getKey())
            ->latest()
            ->get();
    }

    protected function prepareData(Warehouse $warehouse, array $data): array
    {
        $currentLocationId = Arr::get($data, 'current_location_id') ? (int) Arr::get($data, 'current_location_id') : null;
        $this->ensureLocationBelongsToWarehouse($warehouse, $currentLocationId);

        return [
            'code' => trim((string) Arr::get($data, 'code')),
            'warehouse_id' => $warehouse->getKey(),
            'current_location_id' => $currentLocationId,
            'type' => Arr::get($data, 'type'),
            'status' => Arr::get($data, 'status', 'empty'),
            'note' => Arr::get($data, 'note'),
        ];
    }

    protected function ensureBelongsToWarehouse(Warehouse $warehouse, Pallet $pallet): void
    {
        if ((int) $pallet->warehouse_id !== (int) $warehouse->getKey()) {
            abort(404);
        }
    }

    protected function ensureLocationBelongsToWarehouse(Warehouse $warehouse, ?int $locationId): void
    {
        if (! $locationId) {
            return;
        }

        $location = WarehouseLocation::query()->whereKey($locationId)->first();

        if (! $location || (int) $location->warehouse_id !== (int) $warehouse->getKey()) {
            throw ValidationException::withMessages([
                'current_location_id' => 'Location does not belong to warehouse.',
            ]);
        }

        if (! PalletLocationRules::isAllowed($location->type)) {
            throw ValidationException::withMessages([
                'current_location_id' => 'Location type is not allowed for pallet.',
            ]);
        }
    }

    protected function hasUsage(Pallet $pallet): bool
    {
        return $pallet->movements()->exists()
            || DB::table('inv_stock_transactions')->where('pallet_id', $pallet->getKey())->exists()
            || DB::table('inv_stock_balances')->where('pallet_id', $pallet->getKey())->exists();
    }
}
