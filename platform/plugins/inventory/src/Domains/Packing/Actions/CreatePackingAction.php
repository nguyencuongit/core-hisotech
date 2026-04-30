<?php

namespace Botble\Inventory\Domains\Packing\Actions;

use Botble\Inventory\Domains\Packing\DTO\PackingDTO;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Services\PackingService;

class CreatePackingAction
{
    public function __construct(
        protected PackingService $service,
    ) {
    }

    public function execute(PackingDTO $dto): PackingList
    {
        return $this->service->create($dto);
    }
}
