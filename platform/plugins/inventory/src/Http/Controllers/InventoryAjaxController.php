<?php

namespace Botble\Inventory\Http\Controllers;

use Botble\Inventory\Services\LocationService;
use Illuminate\Http\JsonResponse;

class InventoryAjaxController
{
    public function getCitiesByState(int $state, LocationService $locationService): JsonResponse
    {
        return response()->json([
            'data' => $locationService
                ->showCity($state)
                ->pluck('name', 'id')
                ->toArray(),
        ]);
    }
}
