<?php

namespace Botble\Inventory\Domains\Transfer\Services;

use Botble\Inventory\Domains\Transactions\Enums\DocumentStatusEnum;
use Botble\Inventory\Domains\Transactions\Enums\ExportTypeEnum;
use Botble\Inventory\Domains\Transactions\Enums\ImportTypeEnum;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Botble\Inventory\Domains\Transactions\Models\Import as InventoryImport;
use Botble\Inventory\Domains\Transactions\Models\ImportItem;
use Botble\Inventory\Domains\Transfer\DTO\TransferDTO;
use Botble\Inventory\Domains\Transfer\Models\InternalTransfer;
use Botble\Inventory\Domains\Transfer\Models\InternalTransferItem;
use Botble\Inventory\Domains\Transfer\Models\InternalTransferLog;
use Botble\Inventory\Domains\Warehouse\Models\Pallet;
use Botble\Inventory\Domains\Warehouse\Models\StockBalance;
use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Services\StockLedgerService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function __construct(
        protected StockLedgerService $stockLedger,
    ) {
    }

    public function create(TransferDTO $dto): InternalTransfer
    {
        return DB::transaction(function () use ($dto): InternalTransfer {
            $this->ensureActionPermission($dto->action);
            $this->ensureActionSequence($dto, null);

            $attributes = $this->prepareAttributes($dto);
            $this->validatePayload($dto, $attributes);

            $transfer = InternalTransfer::query()->create($attributes);
            $this->syncItems($transfer, $dto);
            $this->syncReceivingItemsIfNeeded($transfer, $dto, null);
            $this->markExportedQuantitiesIfNeeded($transfer);
            $this->syncLinkedDocuments($transfer);
            $this->postLedgerIfNeeded($transfer, null);
            $this->logAction($transfer, null, (string) $transfer->status, $dto->action);

            return $this->reload($transfer);
        });
    }

    public function update(InternalTransfer $transfer, TransferDTO $dto): InternalTransfer
    {
        return DB::transaction(function () use ($transfer, $dto): InternalTransfer {
            $this->ensureActionPermission($dto->action);

            $oldStatus = (string) $transfer->status;
            $this->ensureActionSequence($dto, $transfer);

            if ($oldStatus === 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'Phiếu chuyển kho đã hoàn tất, không thể sửa trực tiếp. Cần tạo nghiệp vụ đảo riêng.',
                ]);
            }

            if ($oldStatus === 'exporting' && ! in_array($dto->action, ['complete'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Phiếu đã xuất khỏi kho nguồn. Chỉ được hoàn tất nhập kho đích.',
                ]);
            }

            if ($oldStatus === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Phiếu chuyển kho đã hủy, không thể sửa.',
                ]);
            }

            $attributes = $this->prepareAttributes($dto, $transfer);
            $this->validatePayload($dto, $attributes, $transfer);

            $transfer->fill($attributes);
            $transfer->save();

            if (in_array($oldStatus, ['confirmed', 'exporting'], true)) {
                $this->syncReceivingItemsIfNeeded($transfer, $dto, $oldStatus);
            } else {
                $this->syncItems($transfer, $dto);
                $this->syncReceivingItemsIfNeeded($transfer, $dto, $oldStatus);
            }

            $this->markExportedQuantitiesIfNeeded($transfer);
            $this->syncLinkedDocuments($transfer);
            $this->postLedgerIfNeeded($transfer, $oldStatus);
            $this->logAction($transfer, $oldStatus, (string) $transfer->status, $dto->action);

            return $this->reload($transfer);
        });
    }

    public function delete(InternalTransfer $transfer): bool
    {
        return DB::transaction(function () use ($transfer): bool {
            if (in_array((string) $transfer->status, ['exporting', 'completed'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Không thể xóa phiếu chuyển kho đã xuất kho hoặc hoàn tất.',
                ]);
            }

            InternalTransferItem::query()
                ->where('transfer_id', $transfer->getKey())
                ->delete();

            $deleted = (bool) $transfer->delete();

            if ($deleted) {
                $this->logAction($transfer, (string) $transfer->status, 'deleted', 'delete');
            }

            return $deleted;
        });
    }

    protected function prepareAttributes(TransferDTO $dto, ?InternalTransfer $transfer = null): array
    {
        $attributes = Arr::only($dto->attributes, [
            'code',
            'from_warehouse_id',
            'to_warehouse_id',
            'requested_by',
            'approved_by',
            'exported_by',
            'imported_by',
            'transfer_date',
            'reason',
            'note',
        ]);

        $attributes['code'] = $attributes['code']
            ?: $transfer?->code
            ?: $this->nextTransferCode();
        $attributes['transfer_date'] = $attributes['transfer_date'] ?: $transfer?->transfer_date ?: now()->toDateString();
        $attributes['requested_by'] = $attributes['requested_by'] ?: $transfer?->requested_by ?: auth()->id();

        $status = $this->resolveStatus($dto, $transfer);
        $attributes['status'] = $status;

        if (in_array($status, ['confirmed', 'exporting', 'completed'], true)) {
            $attributes['approved_by'] = $attributes['approved_by'] ?: $transfer?->approved_by ?: auth()->id();
        }

        if (in_array($status, ['exporting', 'completed'], true)) {
            $attributes['exported_by'] = $attributes['exported_by'] ?: $transfer?->exported_by ?: auth()->id();
            $attributes['in_transit_at'] = $transfer?->in_transit_at ?: now();
        }

        if ($status === 'completed') {
            $attributes['imported_by'] = $attributes['imported_by'] ?: $transfer?->imported_by ?: auth()->id();
            $attributes['received_at'] = $transfer?->received_at ?: now();
            $attributes['completed_at'] = $transfer?->completed_at ?: now();
        }

        if ($status === 'cancelled') {
            $attributes['cancelled_at'] = $transfer?->cancelled_at ?: now();
            $attributes['cancelled_by'] = $transfer?->cancelled_by ?: auth()->id();
            $attributes['cancelled_reason'] = $attributes['reason'] ?: $transfer?->cancelled_reason;
        }

        return $attributes;
    }

    protected function resolveStatus(TransferDTO $dto, ?InternalTransfer $transfer = null): string
    {
        return match ($dto->action) {
            'confirm' => 'confirmed',
            'export' => 'exporting',
            'complete' => 'completed',
            'cancel' => 'cancelled',
            'save_draft' => 'draft',
            default => (string) ($dto->attributes['status'] ?? $transfer?->status ?? 'draft'),
        };
    }

    protected function validatePayload(TransferDTO $dto, array $attributes, ?InternalTransfer $transfer = null): void
    {
        $fromWarehouseId = (int) Arr::get($attributes, 'from_warehouse_id');
        $toWarehouseId = (int) Arr::get($attributes, 'to_warehouse_id');
        $status = (string) Arr::get($attributes, 'status', 'draft');

        if (! Warehouse::query()->whereKey($fromWarehouseId)->exists()) {
            throw ValidationException::withMessages(['from_warehouse_id' => 'Kho xuất không tồn tại.']);
        }

        if (! Warehouse::query()->whereKey($toWarehouseId)->exists()) {
            throw ValidationException::withMessages(['to_warehouse_id' => 'Kho nhập không tồn tại.']);
        }

        if ($fromWarehouseId === $toWarehouseId) {
            throw ValidationException::withMessages(['to_warehouse_id' => 'Kho nhập phải khác kho xuất.']);
        }

        if (! inventory_is_super_admin()) {
            $warehouseIds = array_values(array_map('intval', inventory_warehouse_ids()));

            if ($warehouseIds !== [] && (! in_array($fromWarehouseId, $warehouseIds, true) || ! in_array($toWarehouseId, $warehouseIds, true))) {
                throw ValidationException::withMessages(['from_warehouse_id' => 'Bạn không có quyền thao tác một trong hai kho này.']);
            }
        }

        if ($status === 'cancelled') {
            if ($transfer && (string) $transfer->status === 'exporting') {
                throw ValidationException::withMessages(['status' => 'Phiếu đã xuất kho nguồn, không thể hủy trực tiếp.']);
            }

            return;
        }

        if (in_array($status, ['confirmed', 'exporting', 'completed'], true) && $dto->items === [] && ! $transfer?->items()->exists()) {
            throw ValidationException::withMessages(['items' => 'Cần chọn ít nhất một sản phẩm để chuyển kho.']);
        }

        if (! in_array($status, ['confirmed', 'exporting', 'completed'], true)) {
            return;
        }

        $items = $this->itemsForValidation($transfer, $dto);
        $stockBalances = $this->stockBalancesForItems($items);

        $toLocationIds = array_values(array_unique(array_filter(array_map(
            fn (array $item): int => (int) Arr::get($item, 'to_location_id'),
            $items
        ))));
        $toLocations = WarehouseLocation::query()
            ->whereIn('id', $toLocationIds)
            ->get(['id', 'warehouse_id'])
            ->keyBy('id');

        $toPalletIds = array_values(array_unique(array_filter(array_map(
            fn (array $item): int => (int) Arr::get($item, 'to_pallet_id'),
            $items
        ))));
        $toPallets = Pallet::query()
            ->whereIn('id', $toPalletIds)
            ->get(['id', 'warehouse_id'])
            ->keyBy('id');

        foreach ($items as $index => $item) {
            $stockBalanceId = Arr::get($item, 'stock_balance_id');
            $requestedQty = (float) Arr::get($item, 'requested_qty', 0);
            $toLocationId = (int) Arr::get($item, 'to_location_id');
            $toPalletId = (int) Arr::get($item, 'to_pallet_id');

            if (! $stockBalanceId) {
                throw ValidationException::withMessages([sprintf('items.%s.stock_balance_id', $index) => 'Chọn tồn kho nguồn cho dòng chuyển.']);
            }

            if ($requestedQty <= 0) {
                throw ValidationException::withMessages([sprintf('items.%s.requested_qty', $index) => 'Số lượng chuyển phải lớn hơn 0.']);
            }

            $balance = $stockBalances->get((string) $stockBalanceId);

            if (! $balance || (int) $balance->warehouse_id !== $fromWarehouseId) {
                throw ValidationException::withMessages([sprintf('items.%s.stock_balance_id', $index) => 'Tồn kho nguồn không thuộc kho xuất đã chọn.']);
            }

            if ((string) ($transfer?->status ?? '') !== 'exporting' && (float) $balance->available_qty + 0.0001 < $requestedQty) {
                throw ValidationException::withMessages([
                    sprintf('items.%s.requested_qty', $index) => sprintf('Tồn khả dụng chỉ còn %.4f, không đủ chuyển %.4f.', (float) $balance->available_qty, $requestedQty),
                ]);
            }

            if (in_array($status, ['confirmed', 'exporting', 'completed'], true)) {
                if (! $toLocationId || ! $toLocations->has($toLocationId)) {
                    throw ValidationException::withMessages([sprintf('items.%s.to_location_id', $index) => 'Chọn vị trí nhập tại kho đích trước khi xác nhận hoặc xuất chuyển.']);
                }

                if ((int) $toLocations->get($toLocationId)->warehouse_id !== $toWarehouseId) {
                    throw ValidationException::withMessages([sprintf('items.%s.to_location_id', $index) => 'Vị trí nhập không thuộc kho đích.']);
                }

                if ($toPalletId && (! $toPallets->has($toPalletId) || (int) $toPallets->get($toPalletId)->warehouse_id !== $toWarehouseId)) {
                    throw ValidationException::withMessages([sprintf('items.%s.to_pallet_id', $index) => 'Pallet đích không thuộc kho nhập.']);
                }
            }

            if ($status === 'completed') {
                $receivedQty = (bool) Arr::get($item, '_has_received_qty')
                    ? (float) Arr::get($item, 'received_qty', 0)
                    : $requestedQty;
                $damagedQty = (bool) Arr::get($item, '_has_damaged_qty')
                    ? (float) Arr::get($item, 'damaged_qty', 0)
                    : 0.0;

                if ($damagedQty > $receivedQty + 0.0001) {
                    throw ValidationException::withMessages([sprintf('items.%s.damaged_qty', $index) => 'Số lượng hỏng không được lớn hơn số lượng nhận.']);
                }
            }
        }
    }

    protected function syncItems(InternalTransfer $transfer, TransferDTO $dto): void
    {
        InternalTransferItem::query()
            ->where('transfer_id', $transfer->getKey())
            ->delete();

        $stockBalances = $this->stockBalancesForItems($dto->items);

        foreach ($dto->items as $item) {
            $stockBalance = $stockBalances->get((string) Arr::get($item, 'stock_balance_id'));

            if (! $stockBalance || (float) Arr::get($item, 'requested_qty', 0) <= 0) {
                continue;
            }

            $product = $stockBalance->product;
            $unitPrice = (float) (Arr::get($item, 'unit_price') ?: $stockBalance->last_unit_cost ?: $stockBalance->average_cost ?: 0);
            $requestedQty = (float) Arr::get($item, 'requested_qty', 0);

            InternalTransferItem::query()->create([
                'transfer_id' => $transfer->getKey(),
                'stock_balance_id' => $stockBalance->getKey(),
                'product_id' => $stockBalance->product_id,
                'product_variation_id' => $stockBalance->product_variation_id,
                'product_code' => Arr::get($item, 'product_code') ?: $product?->sku ?: (string) $stockBalance->product_id,
                'product_name' => Arr::get($item, 'product_name') ?: $product?->name ?: 'Sản phẩm #' . $stockBalance->product_id,
                'requested_qty' => $requestedQty,
                'unit_id' => Arr::get($item, 'unit_id'),
                'unit_name' => Arr::get($item, 'unit_name'),
                'from_location_id' => $stockBalance->warehouse_location_id,
                'to_location_id' => Arr::get($item, 'to_location_id'),
                'pallet_id' => $stockBalance->pallet_id,
                'to_pallet_id' => Arr::get($item, 'to_pallet_id'),
                'batch_id' => $stockBalance->batch_id,
                'goods_receipt_batch_id' => $stockBalance->goods_receipt_batch_id,
                'lot_no' => Arr::get($item, 'lot_no'),
                'expiry_date' => Arr::get($item, 'expiry_date'),
                'unit_price' => $unitPrice,
                'amount' => $requestedQty * $unitPrice,
                'note' => Arr::get($item, 'note'),
            ]);
        }
    }

    protected function syncReceivingItemsIfNeeded(InternalTransfer $transfer, TransferDTO $dto, ?string $oldStatus): void
    {
        if ((string) $transfer->status !== 'completed') {
            return;
        }

        $rows = $oldStatus === 'exporting'
            ? $this->mergeReceivingPayload($transfer, $dto)
            : $transfer->items()->get()->map(fn (InternalTransferItem $item): array => $item->toArray())->all();
        $rowsById = collect($rows)->keyBy(fn (array $row): string => (string) Arr::get($row, 'id'));

        $transfer->loadMissing('items');

        foreach ($transfer->items as $item) {
            $row = $rowsById->get((string) $item->getKey(), []);
            $requestedQty = (float) $item->requested_qty;
            $receivedQty = (bool) Arr::get($row, '_has_received_qty')
                ? max((float) Arr::get($row, 'received_qty', 0), 0)
                : ($item->received_qty > 0 ? (float) $item->received_qty : $requestedQty);
            $damagedQty = (bool) Arr::get($row, '_has_damaged_qty')
                ? max((float) Arr::get($row, 'damaged_qty', 0), 0)
                : (float) $item->damaged_qty;

            $item->forceFill([
                'to_pallet_id' => array_key_exists('to_pallet_id', $row) ? Arr::get($row, 'to_pallet_id') : $item->to_pallet_id,
                'received_qty' => $receivedQty,
                'damaged_qty' => $damagedQty,
                'shortage_qty' => max($requestedQty - $receivedQty, 0),
                'overage_qty' => max($receivedQty - $requestedQty, 0),
            ])->save();
        }
    }

    protected function markExportedQuantitiesIfNeeded(InternalTransfer $transfer): void
    {
        if (! in_array((string) $transfer->status, ['exporting', 'completed'], true)) {
            return;
        }

        InternalTransferItem::query()
            ->where('transfer_id', $transfer->getKey())
            ->where(function ($query): void {
                $query->whereNull('exported_qty')->orWhere('exported_qty', '<=', 0);
            })
            ->get()
            ->each(function (InternalTransferItem $item): void {
                $item->forceFill(['exported_qty' => (float) $item->requested_qty])->save();
            });
    }

    protected function postLedgerIfNeeded(InternalTransfer $transfer, ?string $oldStatus): void
    {
        $transfer->loadMissing('items');

        if (in_array((string) $transfer->status, ['exporting', 'completed'], true) && $oldStatus !== 'exporting') {
            foreach ($transfer->items as $item) {
                $this->stockLedger->postInternalTransferOut($item);
            }
        }

        if ((string) $transfer->status === 'completed') {
            foreach ($transfer->items as $item) {
                $this->stockLedger->postInternalTransferIn($item);
            }
        }
    }

    protected function syncLinkedDocuments(InternalTransfer $transfer): void
    {
        $status = (string) $transfer->status;

        if ($status === 'draft' && ! $transfer->export_id && ! $transfer->import_id) {
            return;
        }

        if ($status === 'cancelled' && ! $transfer->export_id && ! $transfer->import_id) {
            return;
        }

        $transfer->loadMissing(['fromWarehouse', 'toWarehouse', 'items']);

        $export = $this->ensureExportDocument($transfer);
        $import = $this->ensureImportDocument($transfer);

        $exportStatus = $this->exportStatusForTransfer($status);
        $importStatus = $this->importStatusForTransfer($status);

        $export->fill([
            'status' => $exportStatus,
            'warehouse_id' => $transfer->from_warehouse_id,
            'partner_id' => $transfer->to_warehouse_id,
            'partner_name' => $transfer->toWarehouse?->name,
            'reference_id' => $transfer->getKey(),
            'reference_code' => $transfer->code,
            'document_date' => $transfer->transfer_date,
            'posting_date' => $transfer->transfer_date,
            'shipped_at' => in_array($status, ['exporting', 'completed'], true) ? ($transfer->in_transit_at ?: now()) : null,
            'approved_by' => in_array($status, ['confirmed', 'exporting', 'completed'], true) ? ($transfer->approved_by ?: auth()->id()) : null,
            'approved_at' => in_array($status, ['confirmed', 'exporting', 'completed'], true) ? ($export->approved_at ?: now()) : null,
            'completed_by' => $status === 'completed' ? ($transfer->exported_by ?: auth()->id()) : null,
            'completed_at' => $status === 'completed' ? ($transfer->in_transit_at ?: now()) : null,
            'note' => $transfer->note,
        ])->save();

        $import->fill([
            'status' => $importStatus,
            'warehouse_id' => $transfer->to_warehouse_id,
            'partner_id' => $transfer->from_warehouse_id,
            'partner_name' => $transfer->fromWarehouse?->name,
            'reference_id' => $transfer->getKey(),
            'reference_code' => $transfer->code,
            'document_date' => $transfer->transfer_date,
            'posting_date' => $transfer->transfer_date,
            'received_at' => $status === 'completed' ? ($transfer->received_at ?: now()) : null,
            'receiver_id' => $status === 'completed' ? ($transfer->imported_by ?: auth()->id()) : null,
            'approved_by' => in_array($status, ['confirmed', 'exporting', 'completed'], true) ? ($transfer->approved_by ?: auth()->id()) : null,
            'approved_at' => in_array($status, ['confirmed', 'exporting', 'completed'], true) ? ($import->approved_at ?: now()) : null,
            'note' => $transfer->note,
        ])->save();

        $transfer->forceFill([
            'export_id' => $export->getKey(),
            'import_id' => $import->getKey(),
        ])->save();

        $this->syncDocumentItems($transfer, $export, $import);
    }

    protected function ensureExportDocument(InternalTransfer $transfer): Export
    {
        $export = $transfer->export_id ? Export::query()->find($transfer->export_id) : null;

        if ($export) {
            return $export;
        }

        return Export::query()->create([
            'type' => ExportTypeEnum::TRANSFER->value,
            'status' => DocumentStatusEnum::DRAFT->value,
            'warehouse_id' => $transfer->from_warehouse_id,
            'partner_type' => 'warehouse',
            'partner_id' => $transfer->to_warehouse_id,
            'partner_name' => $transfer->toWarehouse?->name,
            'requested_by' => $transfer->requested_by,
            'code' => $this->nextDocumentCode('EXP-' . ($transfer->code ?: 'TRF'), Export::class, 'code'),
            'reference_id' => $transfer->getKey(),
            'reference_code' => $transfer->code,
            'document_date' => $transfer->transfer_date,
            'posting_date' => $transfer->transfer_date,
            'created_by' => auth()->id(),
            'note' => $transfer->note,
        ]);
    }

    protected function ensureImportDocument(InternalTransfer $transfer): InventoryImport
    {
        $import = $transfer->import_id ? InventoryImport::query()->find($transfer->import_id) : null;

        if ($import) {
            return $import;
        }

        return InventoryImport::query()->create([
            'type' => ImportTypeEnum::TRANSFER->value,
            'status' => DocumentStatusEnum::DRAFT->value,
            'warehouse_id' => $transfer->to_warehouse_id,
            'partner_type' => 'warehouse',
            'partner_id' => $transfer->from_warehouse_id,
            'partner_name' => $transfer->fromWarehouse?->name,
            'requested_by' => $transfer->requested_by,
            'doc_code' => $this->nextDocumentCode('IMP-' . ($transfer->code ?: 'TRF'), InventoryImport::class, 'doc_code'),
            'reference_id' => $transfer->getKey(),
            'reference_code' => $transfer->code,
            'document_date' => $transfer->transfer_date,
            'posting_date' => $transfer->transfer_date,
            'created_by' => auth()->id(),
            'note' => $transfer->note,
        ]);
    }

    protected function syncDocumentItems(InternalTransfer $transfer, Export $export, InventoryImport $import): void
    {
        ExportItem::query()->where('export_id', $export->getKey())->delete();
        ImportItem::query()->where('import_id', $import->getKey())->delete();

        $transfer->loadMissing('items');

        foreach ($transfer->items as $item) {
            $requestedQty = (float) $item->requested_qty;
            $exportedQty = (float) ($item->exported_qty ?: 0);
            $receivedQty = (float) ($item->received_qty ?: 0);
            $damagedQty = (float) ($item->damaged_qty ?: 0);
            $acceptedQty = max($receivedQty - $damagedQty, 0);

            $exportItem = ExportItem::query()->create([
                'export_id' => $export->getKey(),
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'document_qty' => $requestedQty,
                'reserved_qty' => in_array((string) $transfer->status, ['confirmed'], true) ? $requestedQty : 0,
                'picked_qty' => $exportedQty,
                'packed_qty' => $exportedQty,
                'shipped_qty' => $exportedQty,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit_name,
                'warehouse_location_id' => $item->from_location_id,
                'pallet_id' => $item->pallet_id,
                'batch_id' => $item->batch_id,
                'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
                'stock_balance_id' => $item->stock_balance_id,
                'lot_no' => $item->lot_no,
                'expiry_date' => $item->expiry_date,
                'amount' => $item->amount,
                'unit_price' => $item->unit_price,
                'note' => $item->note,
            ]);

            $importItem = ImportItem::query()->create([
                'import_id' => $import->getKey(),
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'document_qty' => $requestedQty,
                'received_qty' => $receivedQty,
                'accepted_qty' => $acceptedQty,
                'rejected_qty' => $damagedQty,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit_name,
                'warehouse_location_id' => $item->to_location_id,
                'pallet_id' => $item->to_pallet_id,
                'batch_id' => $item->batch_id,
                'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
                'amount' => $item->amount,
                'unit_cost' => $item->unit_price,
                'total_cost' => $acceptedQty * (float) $item->unit_price,
                'qc_status' => $damagedQty > 0 ? 'partial_damaged' : ($receivedQty > 0 ? 'accepted' : null),
                'lot_no' => $item->lot_no,
                'expiry_date' => $item->expiry_date,
                'note' => $item->note,
            ]);

            $item->forceFill([
                'export_item_id' => $exportItem->getKey(),
                'import_item_id' => $importItem->getKey(),
            ])->save();
        }
    }

    protected function exportStatusForTransfer(string $status): string
    {
        return match ($status) {
            'confirmed' => DocumentStatusEnum::CONFIRMED->value,
            'exporting' => DocumentStatusEnum::SHIPPING->value,
            'completed' => DocumentStatusEnum::COMPLETED->value,
            'cancelled' => DocumentStatusEnum::CANCELLED->value,
            default => DocumentStatusEnum::DRAFT->value,
        };
    }

    protected function importStatusForTransfer(string $status): string
    {
        return match ($status) {
            'confirmed' => DocumentStatusEnum::CONFIRMED->value,
            'exporting' => DocumentStatusEnum::RECEIVING->value,
            'completed' => DocumentStatusEnum::COMPLETED->value,
            'cancelled' => DocumentStatusEnum::CANCELLED->value,
            default => DocumentStatusEnum::DRAFT->value,
        };
    }

    protected function itemsForValidation(?InternalTransfer $transfer, TransferDTO $dto): array
    {
        if ($transfer && in_array((string) $transfer->status, ['confirmed', 'exporting'], true)) {
            return $this->mergeReceivingPayload($transfer, $dto);
        }

        return $dto->items;
    }

    protected function mergeReceivingPayload(InternalTransfer $transfer, TransferDTO $dto): array
    {
        $incomingById = collect($dto->items)
            ->filter(fn (array $row): bool => (int) Arr::get($row, 'id') > 0)
            ->keyBy(fn (array $row): string => (string) Arr::get($row, 'id'));

        return $transfer->items()
            ->get()
            ->map(function (InternalTransferItem $item) use ($incomingById): array {
                $row = $item->toArray();
                $incoming = $incomingById->get((string) $item->getKey());

                if ($incoming) {
                    foreach ([
                        '_has_received_qty',
                        '_has_damaged_qty',
                        'received_qty',
                        'damaged_qty',
                        'shortage_qty',
                        'overage_qty',
                        'to_pallet_id',
                    ] as $key) {
                        $row[$key] = Arr::get($incoming, $key, $row[$key] ?? null);
                    }
                } else {
                    $row['_has_received_qty'] = false;
                    $row['_has_damaged_qty'] = false;
                }

                return $row;
            })
            ->all();
    }

    protected function stockBalancesForItems(array $items): Collection
    {
        $ids = array_values(array_unique(array_filter(array_map(
            fn (array $item): ?string => Arr::get($item, 'stock_balance_id') ? (string) Arr::get($item, 'stock_balance_id') : null,
            $items
        ))));

        if ($ids === []) {
            return collect();
        }

        return StockBalance::query()
            ->with(['product:id,name,sku'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy(fn (StockBalance $balance): string => (string) $balance->getKey());
    }

    protected function reload(InternalTransfer $transfer): InternalTransfer
    {
        return $transfer->fresh([
            'fromWarehouse',
            'toWarehouse',
            'exportDoc',
            'importDoc',
            'items.stockBalance',
            'items.fromLocation',
            'items.toLocation',
            'items.pallet',
            'items.toPallet',
            'logs.user',
        ])
            ?: $transfer->load([
                'fromWarehouse',
                'toWarehouse',
                'exportDoc',
                'importDoc',
                'items.stockBalance',
                'items.fromLocation',
                'items.toLocation',
                'items.pallet',
                'items.toPallet',
                'logs.user',
            ]);
    }

    protected function nextTransferCode(): string
    {
        do {
            $code = 'TRF-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (InternalTransfer::query()->where('code', $code)->exists());

        return $code;
    }

    protected function nextDocumentCode(string $base, string $modelClass, string $column): string
    {
        $base = Str::limit(Str::upper($base), 96, '');
        $code = $base;
        $index = 1;

        while ($modelClass::query()->where($column, $code)->exists()) {
            $code = sprintf('%s-%02d', $base, $index++);
        }

        return $code;
    }

    protected function ensureActionPermission(string $action): void
    {
        $permission = match ($action) {
            'confirm' => 'transfer.approve',
            'export' => 'transfer.export',
            'complete' => 'transfer.receive',
            'cancel' => 'transfer.cancel',
            default => null,
        };

        if (! $permission) {
            return;
        }

        $user = auth()->user();

        if (! $user || (method_exists($user, 'isSuperUser') && $user->isSuperUser()) || $user->hasPermission($permission)) {
            return;
        }

        throw ValidationException::withMessages([
            'workflow_action' => 'Bạn không có quyền thực hiện bước này.',
        ]);
    }

    protected function ensureActionSequence(TransferDTO $dto, ?InternalTransfer $transfer): void
    {
        if (! $transfer) {
            if (! in_array($dto->action, ['save', 'save_draft', 'confirm'], true)) {
                throw ValidationException::withMessages([
                    'workflow_action' => 'Tạo mới chỉ được lưu nháp hoặc xác nhận. Hãy xác nhận phiếu trước khi xuất chuyển.',
                ]);
            }

            if (in_array($dto->action, ['save', 'save_draft'], true) && (string) ($dto->attributes['status'] ?? 'draft') !== 'draft') {
                throw ValidationException::withMessages([
                    'workflow_action' => 'Phiếu mới chỉ được lưu ở trạng thái nháp hoặc xác nhận bằng nút xác nhận.',
                ]);
            }

            return;
        }

        $oldStatus = (string) $transfer->status;
        $allowedActions = match ($oldStatus) {
            'draft' => ['save', 'save_draft', 'confirm', 'cancel'],
            'confirmed' => ['export', 'cancel'],
            'exporting' => ['complete'],
            default => [],
        };

        if (! in_array($dto->action, $allowedActions, true)) {
            throw ValidationException::withMessages([
                'workflow_action' => 'Bước thao tác không hợp lệ với trạng thái hiện tại của phiếu chuyển kho.',
            ]);
        }
    }

    protected function logAction(InternalTransfer $transfer, ?string $oldStatus, string $newStatus, string $action): void
    {
        if ($action === 'save' && $oldStatus === $newStatus) {
            return;
        }

        InternalTransferLog::query()->create([
            'transfer_id' => $transfer->getKey(),
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $transfer->note,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);
    }
}
