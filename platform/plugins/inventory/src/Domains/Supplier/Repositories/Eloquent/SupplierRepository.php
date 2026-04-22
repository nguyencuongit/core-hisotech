<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Eloquent;

use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\SupplierInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class SupplierRepository extends RepositoriesAbstract implements SupplierInterface
{
    public function loadForShow(Supplier $supplier): Supplier
    {
        return $supplier->load([
            'contacts',
            'addresses',
            'banks',
            'supplierProducts.product',
            'approvals.actor',
            'creator',
            'submitter',
            'approver',
        ]);
    }

    public function loadForApproval(Supplier $supplier): Supplier
    {
        return $this->loadForShow($supplier);
    }

    public function loadForEdit(Supplier $supplier): Supplier
    {
        return $supplier->load([
            'contacts',
            'addresses',
            'banks',
            'supplierProducts.product',
            'creator',
            'submitter',
            'approver',
        ]);
    }
}
