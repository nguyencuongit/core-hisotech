<?php

namespace Botble\Inventory\Domains\Supplier\UseCases;

use Botble\Inventory\Domains\Supplier\Actions\ApproveSupplierAction;
use Botble\Inventory\Domains\Supplier\Actions\CreateSupplierAction;
use Botble\Inventory\Domains\Supplier\Actions\DeleteSupplierAction;
use Botble\Inventory\Domains\Supplier\Actions\RejectSupplierAction;
use Botble\Inventory\Domains\Supplier\Actions\SubmitSupplierApprovalAction;
use Botble\Inventory\Domains\Supplier\Actions\UpdateSupplierAction;
use Botble\Inventory\Domains\Supplier\DTO\SupplierApprovalDTO;
use Botble\Inventory\Domains\Supplier\DTO\SupplierDTO;
use Botble\Inventory\Domains\Supplier\DTO\SupplierProductSearchDTO;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\ProductReadInterface;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\SupplierInterface;

class SupplierUsecase
{
    public function __construct(
        protected SupplierInterface $suppliers,
        protected ProductReadInterface $products,
        protected CreateSupplierAction $createAction,
        protected UpdateSupplierAction $updateAction,
        protected DeleteSupplierAction $deleteAction,
        protected SubmitSupplierApprovalAction $submitAction,
        protected ApproveSupplierAction $approveAction,
        protected RejectSupplierAction $rejectAction,
    ) {
    }

    public function create(SupplierDTO $dto): Supplier
    {
        return $this->createAction->execute($dto);
    }

    public function update(int|string $supplierId, SupplierDTO $dto): Supplier
    {
        return $this->updateAction->execute($this->findSupplierOrFail($supplierId), $dto);
    }

    public function delete(int|string $supplierId): bool
    {
        return $this->deleteAction->execute($this->findSupplierOrFail($supplierId));
    }

    public function submit(int|string $supplierId, SupplierApprovalDTO $dto): Supplier
    {
        return $this->submitAction->execute($this->findSupplierOrFail($supplierId), $dto);
    }

    public function approve(int|string $supplierId, SupplierApprovalDTO $dto): Supplier
    {
        return $this->approveAction->execute($this->findSupplierOrFail($supplierId), $dto);
    }

    public function reject(int|string $supplierId, SupplierApprovalDTO $dto): Supplier
    {
        return $this->rejectAction->execute($this->findSupplierOrFail($supplierId), $dto);
    }

    public function loadForShow(int|string $supplierId): Supplier
    {
        return $this->suppliers->loadForShow($this->findSupplierOrFail($supplierId));
    }

    public function loadForApproval(int|string $supplierId): Supplier
    {
        return $this->suppliers->loadForApproval($this->findSupplierOrFail($supplierId));
    }

    public function loadForEdit(int|string $supplierId): Supplier
    {
        return $this->suppliers->loadForEdit($this->findSupplierOrFail($supplierId));
    }

    public function searchProducts(SupplierProductSearchDTO $dto): array
    {
        return $this->products
            ->searchForSupplier($dto->query)
            ->map(function ($product): array {
                $image = null;
                $name = trim((string) $product->name);
                $sku = trim((string) $product->sku);
                $text = $name ?: $sku ?: (string) $product->id;

                if ($name !== '' && $sku !== '') {
                    $text .= ' (' . $sku . ')';
                }

                if (! empty($product->image)) {
                    try {
                        $image = rv_media()->getImageUrl($product->image, 'thumb');
                    } catch (\Throwable) {
                        $image = null;
                    }
                }

                return [
                    'id' => $product->id,
                    'text' => $text,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'image' => $image,
                ];
            })
            ->values()
            ->all();
    }

    protected function findSupplierOrFail(int|string $supplierId): Supplier
    {
        return $this->suppliers->findOrFail($supplierId);
    }
}
