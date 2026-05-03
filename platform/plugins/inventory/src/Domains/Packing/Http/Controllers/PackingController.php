<?php

namespace Botble\Inventory\Domains\Packing\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Packing\DTO\PackingDTO;
use Botble\Inventory\Domains\Packing\Forms\PackingForm;
use Botble\Inventory\Domains\Packing\Http\Requests\PackingRequest;
use Botble\Inventory\Domains\Packing\Tables\PackingTable;
use Botble\Inventory\Domains\Packing\UseCases\PackingUsecase;
use Botble\Inventory\Domains\Transactions\Models\Export;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;
use Illuminate\Http\JsonResponse;

class PackingController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/inventory::inventory.packing.name')), route('inventory.packing.index'));
    }

    public function index(PackingTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.packing.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/inventory::inventory.create'));

        return PackingForm::create()->renderForm();
    }

    public function store(PackingRequest $request, PackingUsecase $usecase)
    {
        $packing = $usecase->create(PackingDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.packing.index'))
            ->setNextUrl(route('inventory.packing.edit', $packing->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function exportPreview(int|string $export): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermission('packing.create') || auth()->user()?->hasPermission('packing.edit'), 403);

        $export = Export::query()
            ->with(['warehouse', 'items.export', 'items' => fn ($query) => $query->orderBy('id')])
            ->findOrFail($export);

        if (! inventory_is_super_admin()) {
            $warehouseIds = array_values(array_map('intval', inventory_warehouse_ids()));

            abort_if($export->warehouse_id && ! in_array((int) $export->warehouse_id, $warehouseIds, true), 403);
        }

        return response()->json([
            'data' => [
                'export' => [
                    'id' => $export->getKey(),
                    'code' => $export->code ?: 'EXP-' . $export->getKey(),
                    'type' => $export->type,
                    'status' => $export->status,
                    'warehouse_id' => $export->warehouse_id,
                    'warehouse_name' => $export->warehouse?->name,
                    'warehouse_code' => $export->warehouse?->code,
                    'partner_type' => $export->partner_type,
                    'partner_code' => $export->partner_code,
                    'partner_name' => $export->partner_name,
                    'partner_phone' => $export->partner_phone,
                    'partner_email' => $export->partner_email,
                    'partner_address' => $export->partner_address,
                    'receiver_name' => $export->receiver_name,
                    'receiver_phone' => $export->receiver_phone,
                    'receiver_address' => $export->receiver_address,
                    'delivery_name' => $export->delivery_name,
                    'delivery_phone' => $export->delivery_phone,
                    'shipping_unit' => $export->shipping_unit,
                    'tracking_code' => $export->tracking_code,
                    'shipping_fee' => (float) ($export->shipping_fee ?? 0),
                    'document_date' => optional($export->document_date)->format('Y-m-d'),
                    'posting_date' => optional($export->posting_date)->format('Y-m-d'),
                    'shipped_at' => optional($export->shipped_at)->format('Y-m-d H:i'),
                    'note' => $export->note,
                ],
                'items' => $export->items
                    ->map(fn (ExportItem $item): array => $this->formatExportItem($item))
                    ->values(),
                'suggested_packing_code' => 'PACK-' . ($export->code ?: $export->getKey()),
            ],
        ]);
    }

    public function edit(int|string $packing, PackingUsecase $usecase)
    {
        $packing = $usecase->loadForEdit($packing);

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $packing->code]));

        return PackingForm::createFromModel($packing)->renderForm();
    }

    protected function formatExportItem(ExportItem $item): array
    {
        $documentQty = (float) ($item->document_qty ?? 0);
        $packedQty = (float) ($item->packed_qty ?? 0);
        $remainingQty = max($documentQty - $packedQty, 0);

        $label = sprintf(
            '%s | %s - %s | còn %.4f %s',
            $item->export?->code ?: 'EXP-' . $item->export_id,
            $item->product_code ?: $item->product_id,
            $item->product_name ?: 'Sản phẩm',
            $remainingQty,
            $item->unit_name ?: ''
        );

        return [
            'id' => $item->getKey(),
            'label' => $label,
            'export_id' => $item->export_id,
            'product_id' => $item->product_id,
            'product_variation_id' => $item->product_variation_id,
            'product_code' => $item->product_code,
            'product_name' => $item->product_name,
            'document_qty' => $documentQty,
            'packed_qty' => $packedQty,
            'remaining_qty' => $remainingQty,
            'unit_id' => $item->unit_id,
            'unit_name' => $item->unit_name,
            'warehouse_location_id' => $item->warehouse_location_id,
            'pallet_id' => $item->pallet_id,
            'batch_id' => $item->batch_id,
            'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
            'stock_balance_id' => $item->stock_balance_id,
            'lot_no' => $item->lot_no,
            'expiry_date' => optional($item->expiry_date)->format('Y-m-d'),
            'note' => $item->note,
        ];
    }

    public function update(int|string $packing, PackingRequest $request, PackingUsecase $usecase)
    {
        $usecase->update($packing, PackingDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.packing.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $packing, PackingUsecase $usecase)
    {
        $usecase->delete($packing);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.packing.index'))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
