<?php

namespace Botble\Inventory\Http\Middleware;

use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Inventory\Support\InventoryContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaff;
use Botble\Inventory\Domains\WarehouseStaff\Models\WarehouseStaffAssignments;

class InventoryContextMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();



        $context = app(InventoryContext::class);

        $context
            ->setWarehouseIds(null)
            ->setSuperAdmin(false);

        if (! $user) {
            return $next($request);
        }
        if($user->super_user === 1){
            $context->setSuperAdmin(true);
        }
        $warehouseIds = $this->warehouseIds($user->id);
        $context
            ->setWarehouseIds($warehouseIds);
        return $next($request);
    }

    public function warehouseIds($user_id){
        $staffId = WarehouseStaff::query()
        ->where('user_id', $user_id)
        ->value('id');
        $warehouseIds = WarehouseStaffAssignments::query()
            ->where('staff_id', $staffId)
            ->pluck('warehouse_id')
            ->toArray();

        return $warehouseIds;
    }

    
}