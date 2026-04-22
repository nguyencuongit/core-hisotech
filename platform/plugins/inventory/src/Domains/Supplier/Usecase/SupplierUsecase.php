<?php

namespace Botble\Inventory\Domains\Supplier\Usecase;

use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\SupplierInterface;

class SupplierUsecase
{
    public function __construct(
        private SupplierInterface $supplierRepository
    ) {
    }

    public function loadForShow(Supplier $supplier): Supplier
    {
        return $this->supplierRepository->loadForShow($supplier);
    }

    public function loadForApproval(Supplier $supplier): Supplier
    {
        return $this->supplierRepository->loadForApproval($supplier);
    }

    public function loadForEdit(Supplier $supplier): Supplier
    {
        return $this->supplierRepository->loadForEdit($supplier);
    }
}
