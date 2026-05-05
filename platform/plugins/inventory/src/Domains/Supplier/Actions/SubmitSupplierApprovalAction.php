<?php

namespace Botble\Inventory\Domains\Supplier\Actions;

use Botble\Inventory\Domains\Supplier\DTO\SupplierApprovalDTO;
use Botble\Inventory\Domains\Supplier\Entities\SupplierEntity;
use Botble\Inventory\Domains\Supplier\Services\SupplierService;

class SubmitSupplierApprovalAction
{
    public function __construct(
        protected SupplierService $service,
    ) {
    }

    public function execute(SupplierEntity $supplier, SupplierApprovalDTO $dto): SupplierEntity
    {
        return $this->service->submitForApproval($supplier, $dto->note);
    }
}
