<?php

namespace Botble\Inventory\Domains\Transactions\Repositories\Eloquent;

use Botble\Inventory\Domains\Packing\Models\PackingListItem;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Repositories\Interfaces\ExportShipmentInterface;
use Illuminate\Database\Eloquent\Collection;

class ExportShipmentRepository implements ExportShipmentInterface
{
    public function packedItemsForExport(Export $export): Collection
    {
        return PackingListItem::query()
            ->with(['packingList', 'exportItem'])
            ->whereHas('packingList', function ($query) use ($export): void {
                $query
                    ->where('export_id', $export->getKey())
                    ->where('status', 'packed');
            })
            ->where('packed_qty', '>', 0)
            ->whereNotNull('export_item_id')
            ->whereNotNull('stock_balance_id')
            ->get();
    }
}
