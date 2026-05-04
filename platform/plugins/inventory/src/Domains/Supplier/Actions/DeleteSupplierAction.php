<?php

namespace Botble\Inventory\Domains\Supplier\Actions;

use Botble\Inventory\Domains\Supplier\Entities\SupplierEntity;
use Botble\Inventory\Domains\Supplier\Services\SupplierService;

class DeleteSupplierAction
{
    public function __construct(
        protected SupplierService $service,
    ) {
    }

    public function execute(SupplierEntity $supplier): bool
    {
        return $this->service->delete($supplier);
    }
}
