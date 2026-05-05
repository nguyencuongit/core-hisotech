<?php

namespace Botble\Inventory\Domains\Supplier\Actions;

use Botble\Inventory\Domains\Supplier\DTO\SupplierDTO;
use Botble\Inventory\Domains\Supplier\Entities\SupplierEntity;
use Botble\Inventory\Domains\Supplier\Services\SupplierService;

class CreateSupplierAction
{
    public function __construct(
        protected SupplierService $service,
    ) {
    }

    public function execute(SupplierDTO $dto): SupplierEntity
    {
        return $this->service->create($dto->toArray());
    }
}
