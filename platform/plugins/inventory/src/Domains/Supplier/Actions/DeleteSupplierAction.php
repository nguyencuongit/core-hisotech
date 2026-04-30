<?php

namespace Botble\Inventory\Domains\Supplier\Actions;

use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\SupplierInterface;

class DeleteSupplierAction
{
    public function __construct(
        protected SupplierInterface $suppliers,
    ) {
    }

    public function execute(Supplier $supplier): bool
    {
        return $this->suppliers->deleteSupplier($supplier);
    }
}
