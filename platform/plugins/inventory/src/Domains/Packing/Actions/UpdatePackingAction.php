<?php

namespace Botble\Inventory\Domains\Packing\Actions;

use Botble\Inventory\Domains\Packing\DTO\PackingDTO;
use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Services\PackingService;

class UpdatePackingAction
{
    public function __construct(
        protected PackingService $service,
    ) {
    }

    public function execute(PackingList $packingList, PackingDTO $dto): PackingList
    {
        return $this->service->update($packingList, $dto);
    }
}
