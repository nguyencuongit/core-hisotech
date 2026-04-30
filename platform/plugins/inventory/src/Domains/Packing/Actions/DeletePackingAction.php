<?php

namespace Botble\Inventory\Domains\Packing\Actions;

use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Packing\Services\PackingService;

class DeletePackingAction
{
    public function __construct(
        protected PackingService $service,
    ) {
    }

    public function execute(PackingList $packingList): bool
    {
        return $this->service->delete($packingList);
    }
}
