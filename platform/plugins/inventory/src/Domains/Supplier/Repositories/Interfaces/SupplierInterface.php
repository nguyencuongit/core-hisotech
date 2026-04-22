<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Interfaces;

use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface SupplierInterface extends RepositoryInterface
{
    public function loadForShow(Supplier $supplier): Supplier;

    public function loadForApproval(Supplier $supplier): Supplier;

    public function loadForEdit(Supplier $supplier): Supplier;
}
