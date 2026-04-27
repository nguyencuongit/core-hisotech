<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Usecase;

use Botble\Inventory\Domains\WarehouseStaff\Repositories\Interfaces\WarehouseStaffAssignmentInterface;

class AssignmentsUsercase
{
    public function __construct( 
        private WarehouseStaffAssignmentInterface $warehouseStaffAssignmentInterface
    ){}

    public function updateWarehouseId(int $staffId, array $warehouseIds, int $positionId){
        try {
            $this->warehouseStaffAssignmentInterface->removeUnassignedWarehousesByStaff($staffId,$warehouseIds);
            foreach ($warehouseIds as $warehouseId) {
                $record = $this->warehouseStaffAssignmentInterface->findByStaffIdWarehouseId($staffId,$warehouseId);
                if (! $record->exists) {
                    $record->start_date = now();
                }
                $record->position_id = $positionId;
                $record->save();
            }
            
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }
}