<?php

namespace Botble\Inventory\Domains\Transactions\Repositories\Interfaces;

use Botble\Inventory\Domains\Packing\Models\PackingListItem;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Illuminate\Database\Eloquent\Collection;

interface ExportShipmentInterface
{
    /**
     * @return Collection<int, PackingListItem>
     */
    public function packedItemsForExport(Export $export): Collection;
}
