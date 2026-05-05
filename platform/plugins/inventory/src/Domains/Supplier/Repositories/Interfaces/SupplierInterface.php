<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Interfaces;

use Botble\Inventory\Domains\Supplier\Entities\SupplierEntity;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface SupplierInterface extends RepositoryInterface
{
    public function createSupplier(array $attributes): SupplierEntity;

    public function updateSupplier(int|string $supplierId, array $attributes): SupplierEntity;

    public function deleteSupplier(int|string $supplierId): bool;

    public function readForShow(int|string $supplierId): SupplierEntity;

    public function readForApproval(int|string $supplierId): SupplierEntity;

    public function readForEdit(int|string $supplierId): SupplierEntity;

    public function reload(int|string $supplierId, array $with = []): SupplierEntity;

    public function deleteChildren(int|string $supplierId): void;

    public function createContact(int|string $supplierId, array $attributes): void;

    public function createAddress(int|string $supplierId, array $attributes): void;

    public function createBank(int|string $supplierId, array $attributes): void;

    public function updateOrCreateProduct(int|string $supplierId, int $productId, array $attributes): void;

    public function createApproval(int|string $supplierId, array $attributes): void;

    public function codeExists(string $code): bool;
}
