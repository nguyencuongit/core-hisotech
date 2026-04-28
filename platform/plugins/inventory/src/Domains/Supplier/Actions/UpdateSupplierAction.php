<?php

namespace Botble\Inventory\Domains\Supplier\Actions;

use Botble\Inventory\Domains\Supplier\DTO\SupplierDTO;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Services\SupplierService;

class UpdateSupplierAction
{
    public function __construct(
        protected SupplierService $service,
    ) {
    }

    public function execute(Supplier $supplier, SupplierDTO $dto): Supplier
    {
        return $this->service->update($supplier, $dto->toArray());
    }
}
