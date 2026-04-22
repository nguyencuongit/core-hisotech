<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Repositories\Eloquent;

use Botble\Inventory\Domains\WarehouseStaff\Repositories\Interfaces\WarehouseStaffAssignmentInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;


class WarehouseStaffAssignmentRepository extends RepositoriesAbstract implements WarehouseStaffAssignmentInterface
{
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
}
