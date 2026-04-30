<?php

namespace Botble\Inventory\Domains\Supplier\Actions;

use Botble\Inventory\Domains\Supplier\DTO\SupplierApprovalDTO;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Services\SupplierService;

class ApproveSupplierAction
{
    public function __construct(
        protected SupplierService $service,
    ) {
    }

    public function execute(Supplier $supplier, SupplierApprovalDTO $dto): Supplier
    {
        return $this->service->approve($supplier, $dto->note);
    }
}
