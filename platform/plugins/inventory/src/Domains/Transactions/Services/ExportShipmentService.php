<?php

namespace Botble\Inventory\Domains\Transactions\Services;

use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Repositories\Interfaces\ExportShipmentInterface;
use Botble\Inventory\Domains\Warehouse\Services\StockLedgerService;
use Illuminate\Support\Facades\DB;

class ExportShipmentService
{
    public function __construct(
        protected ExportShipmentInterface $shipments,
        protected StockLedgerService $stockLedger,
    ) {
    }

    public function shipPackedItems(Export $export): void
    {
        if (! in_array((string) $export->status, ['shipping', 'completed'], true)) {
            return;
        }

        DB::transaction(function () use ($export): void {
            foreach ($this->shipments->packedItemsForExport($export) as $item) {
                $this->stockLedger->postPackingListItem($item, 'export_shipped');
            }
        });
    }
}
