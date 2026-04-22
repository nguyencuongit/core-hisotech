<?php

namespace Botble\Inventory\Domains\WarehouseStaff\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface WarehouseStaffAssignmentInterface extends RepositoryInterface 
{
    public function findByStaff(int $id);
    public function removeUnassignedWarehousesByStaff(int $staffId, array $warehouseIds);
    public function findByStaffIdWarehouseId( int $staffId, int $warehouseId);
}
