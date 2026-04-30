<?php

namespace Botble\Inventory\Domains\Packing\UseCases;

use Botble\Inventory\Domains\Packing\Actions\CreatePackingAction;
use Botble\Inventory\Domains\Packing\Actions\DeletePackingAction;
use Botble\Inventory\Domains\Packing\Actions\UpdatePackingAction;
use Botble\Inventory\Domains\Packing\DTO\PackingDTO;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Repositories\Interfaces\PackingInterface;

class PackingUsecase
{
    public function __construct(
        protected PackingInterface $packings,
        protected CreatePackingAction $createAction,
        protected UpdatePackingAction $updateAction,
        protected DeletePackingAction $deleteAction,
    ) {
    }

    public function create(PackingDTO $dto): PackingList
    {
        return $this->createAction->execute($dto);
    }

    public function update(int|string $packingId, PackingDTO $dto): PackingList
    {
        return $this->updateAction->execute($this->findOrFail($packingId), $dto);
    }

    public function delete(int|string $packingId): bool
    {
        return $this->deleteAction->execute($this->findOrFail($packingId));
    }

    public function loadForEdit(int|string $packingId): PackingList
    {
        return $this->packings->loadForEdit($this->findOrFail($packingId));
    }

    public function findOrFail(int|string $packingId): PackingList
    {
        return $this->packings->findOrFail($packingId);
    }
}
