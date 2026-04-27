<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Repositories\Eloquent;

use Botble\Inventory\Domains\WarehouseStaff\Repositories\Interfaces\WarehouseStaffAssignmentInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class WarehouseStaffAssignmentRepository extends RepositoriesAbstract implements WarehouseStaffAssignmentInterface
{
    public function query(){
        $warehouseIds = inventory_warehouse_ids();
        $isAdmin = inventory_is_super_admin();
        if (! $isAdmin && ! empty($warehouseIds)) {
            return $this->model->whereIn('warehouse_id', $warehouseIds);
        }
        return $this->model;
    }
    public function findByStaff(int $id){
        return $this->model->where('staff_id', $id)->toArray();
    }

    public function removeUnassignedWarehousesByStaff(int $staffId, array $warehouseIds){
        return $this->model
                ->where('staff_id', $staffId)
                ->whereNotIn('warehouse_id', $warehouseIds) 
                ->delete();
    }

    public function findByStaffIdWarehouseId( int $staffId, int $warehouseId){
        return $this->model->firstOrNew([
                'staff_id' => $staffId,
                'warehouse_id' => $warehouseId,
            ]);
    }
    public function findByWarehouseIdStaff(int $warehouse){
        return $this->query()
        ->where('warehouse_id',$warehouse)
        ->with('staff')
        ->get() 
        ->pluck('staff.full_name', 'staff.id')
        ->unique()
        ->toArray();
    }
}
