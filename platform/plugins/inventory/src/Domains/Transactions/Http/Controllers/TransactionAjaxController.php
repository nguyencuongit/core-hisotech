<?php

namespace Botble\Inventory\Domains\Transactions\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Botble\Inventory\Domains\WarehouseStaff\Repositories\Interfaces\WarehouseStaffAssignmentInterface;
use Botble\Inventory\Domains\Transactions\Enums\PartnerTypeEnum;

class TransactionAjaxController
{
    public function __construct(
        private WarehouseStaffAssignmentInterface $warehouseStaffAssignmentInterface
    ){}
    public function getStaffByWarehouse(int $warehouse): JsonResponse
    {
        $staff = $this->warehouseStaffAssignmentInterface->findByWarehouseIdStaff($warehouse);

        return response()->json([
            'data' => $staff,
        ]);
    }
    public function getMenber($type){
        $data = [];
         try {
            $typeEnum = PartnerTypeEnum::from($type);
        } catch (\ValueError $e) {
            return response()->json(['data' => []]);
        }

        switch ($typeEnum) {
            case PartnerTypeEnum::CUSTOMER:
                $data = [
                    1=> 'khách hàng 1',
                    2=> 'khách hàng 2',
                    3=> 'khách hàng 3',
                ];
                break;

            case PartnerTypeEnum::SUPPLIER:
                $data = [
                    1=> 'SUPPLIER 1',
                    2=> 'SUPPLIER 2',
                    3=> 'SUPPLIER 3',
                ];
                break;

            case PartnerTypeEnum::WAREHOUSE:
                $data = [
                    1=> 'WAREHOUSE 1',
                    2=> 'WAREHOUSE 2',
                    3=> 'WAREHOUSE 3',
                ];
                break;
        }

        return response()->json([
            'data' => $data,
        ]);
    }
}