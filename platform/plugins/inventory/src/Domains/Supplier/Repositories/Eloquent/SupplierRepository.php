<?php

namespace Botble\Inventory\Domains\Supplier\Repositories\Eloquent;

use Botble\Inventory\Domains\Supplier\Entities\SupplierEntity;
use Botble\Inventory\Domains\Supplier\Mappers\SupplierMapper;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Models\SupplierAddress;
use Botble\Inventory\Domains\Supplier\Models\SupplierApproval;
use Botble\Inventory\Domains\Supplier\Models\SupplierBank;
use Botble\Inventory\Domains\Supplier\Models\SupplierContact;
use Botble\Inventory\Domains\Supplier\Models\SupplierProduct;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\SupplierInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Support\Facades\DB;

class SupplierRepository extends RepositoriesAbstract implements SupplierInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function createSupplier(array $attributes): SupplierEntity
    {
        $supplier = Supplier::query()->create($attributes);

        return $this->toEntity($supplier);
    }

    public function updateSupplier(int|string $supplierId, array $attributes): SupplierEntity
    {
        $model = Supplier::query()->findOrFail($supplierId);
        $model->fill($attributes);
        $model->save();

        return $this->toEntity($model->refresh());
    }

    public function deleteSupplier(int|string $supplierId): bool
    {
        return (bool) Supplier::query()
            ->findOrFail($supplierId)
            ->delete();
    }

    public function readForShow(int|string $supplierId): SupplierEntity
    {
        $supplier = Supplier::query()
            ->with($this->showRelations())
            ->findOrFail($supplierId);

        return $this->toEntity($supplier);
    }

    public function readForApproval(int|string $supplierId): SupplierEntity
    {
        return $this->readForShow($supplierId);
    }

    public function readForEdit(int|string $supplierId): SupplierEntity
    {
        $supplier = Supplier::query()
            ->with($this->editRelations())
            ->findOrFail($supplierId);

        return $this->toEntity($supplier);
    }

    public function reload(int|string $supplierId, array $with = []): SupplierEntity
    {
        $model = Supplier::query()
            ->with($with)
            ->findOrFail($supplierId);

        return $this->toEntity($model);
    }

    public function deleteChildren(int|string $supplierId): void
    {
        SupplierContact::query()->where('supplier_id', $supplierId)->delete();
        SupplierAddress::query()->where('supplier_id', $supplierId)->delete();
        SupplierBank::query()->where('supplier_id', $supplierId)->delete();
        SupplierProduct::query()->where('supplier_id', $supplierId)->delete();
    }

    public function createContact(int|string $supplierId, array $attributes): void
    {
        SupplierContact::query()->create(array_merge($attributes, [
            'supplier_id' => $supplierId,
        ]));
    }

    public function createAddress(int|string $supplierId, array $attributes): void
    {
        SupplierAddress::query()->create(array_merge($attributes, [
            'supplier_id' => $supplierId,
        ]));
    }

    public function createBank(int|string $supplierId, array $attributes): void
    {
        SupplierBank::query()->create(array_merge($attributes, [
            'supplier_id' => $supplierId,
        ]));
    }

    public function updateOrCreateProduct(int|string $supplierId, int $productId, array $attributes): void
    {
        SupplierProduct::query()->updateOrCreate(
            [
                'supplier_id' => $supplierId,
                'product_id' => $productId,
            ],
            array_merge($attributes, [
                'supplier_id' => $supplierId,
                'product_id' => $productId,
            ])
        );
    }

    public function createApproval(int|string $supplierId, array $attributes): void
    {
        SupplierApproval::query()->create(array_merge($attributes, [
            'supplier_id' => $supplierId,
        ]));
    }

    public function codeExists(string $code): bool
    {
        return Supplier::query()
            ->where('code', $code)
            ->exists();
    }

    protected function toEntity(Supplier $supplier): SupplierEntity
    {
        return SupplierMapper::toEntity($supplier->toArray());
    }

    protected function showRelations(): array
    {
        return [
            'contacts',
            'addresses',
            'banks',
            'supplierProducts',
            'approvals',
        ];
    }

    protected function editRelations(): array
    {
        return [
            'contacts',
            'addresses',
            'banks',
            'supplierProducts',
        ];
    }
}
