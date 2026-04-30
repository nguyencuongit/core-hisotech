<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Eloquent;

use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Models\SupplierApproval;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\SupplierInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class SupplierRepository extends RepositoriesAbstract implements SupplierInterface
{
    public function createSupplier(array $attributes): Supplier
    {
        return Supplier::query()->create($attributes);
    }

    public function updateSupplier(Supplier $supplier, array $attributes): Supplier
    {
        $supplier->fill($attributes);
        $supplier->save();

        return $supplier;
    }

    public function deleteSupplier(Supplier $supplier): bool
    {
        return (bool) $supplier->delete();
    }

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

    public function reload(Supplier $supplier, array $with = []): Supplier
    {
        $freshSupplier = $supplier->fresh($with);

        return $freshSupplier ?: $supplier->load($with);
    }

    public function deleteChildren(Supplier $supplier): void
    {
        $supplier->contacts()->delete();
        $supplier->addresses()->delete();
        $supplier->banks()->delete();
        $supplier->supplierProducts()->delete();
    }

    public function createContact(Supplier $supplier, array $attributes): void
    {
        $supplier->contacts()->create($attributes);
    }

    public function createAddress(Supplier $supplier, array $attributes): void
    {
        $supplier->addresses()->create($attributes);
    }

    public function createBank(Supplier $supplier, array $attributes): void
    {
        $supplier->banks()->create($attributes);
    }

    public function updateOrCreateProduct(Supplier $supplier, int $productId, array $attributes): void
    {
        $supplier->supplierProducts()->updateOrCreate(
            ['product_id' => $productId],
            $attributes
        );
    }

    public function createApproval(Supplier $supplier, array $attributes): SupplierApproval
    {
        return SupplierApproval::query()->create(array_merge($attributes, [
            'supplier_id' => $supplier->getKey(),
        ]));
    }

    public function codeExists(string $code): bool
    {
        return Supplier::query()->where('code', $code)->exists();
    }
}
