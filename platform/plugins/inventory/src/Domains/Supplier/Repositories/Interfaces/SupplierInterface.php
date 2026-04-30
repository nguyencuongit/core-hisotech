<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Interfaces;

use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Models\SupplierApproval;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface SupplierInterface extends RepositoryInterface
{
    public function createSupplier(array $attributes): Supplier;

    public function updateSupplier(Supplier $supplier, array $attributes): Supplier;

    public function deleteSupplier(Supplier $supplier): bool;

    public function loadForShow(Supplier $supplier): Supplier;

    public function loadForApproval(Supplier $supplier): Supplier;

    public function loadForEdit(Supplier $supplier): Supplier;

    public function reload(Supplier $supplier, array $with = []): Supplier;

    public function deleteChildren(Supplier $supplier): void;

    public function createContact(Supplier $supplier, array $attributes): void;

    public function createAddress(Supplier $supplier, array $attributes): void;

    public function createBank(Supplier $supplier, array $attributes): void;

    public function updateOrCreateProduct(Supplier $supplier, int $productId, array $attributes): void;

    public function createApproval(Supplier $supplier, array $attributes): SupplierApproval;

    public function codeExists(string $code): bool;
}
