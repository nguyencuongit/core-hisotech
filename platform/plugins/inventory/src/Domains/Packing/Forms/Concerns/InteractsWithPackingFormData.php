<?php

namespace Botble\Inventory\Domains\Packing\Forms\Concerns;

use Botble\Inventory\Domains\Packing\Models\PackingList;
use Botble\Inventory\Domains\Transactions\Models\ExportItem;

trait InteractsWithPackingFormData
{
    protected function formatExportItemForClient(ExportItem $item): array
    {
        $documentQty = (float) ($item->document_qty ?? 0);
        $packedQty = (float) ($item->packed_qty ?? 0);
        $remainingQty = max($documentQty - $packedQty, 0);

        return [
            'id' => $item->getKey(),
            'label' => sprintf(
                '%s | %s - %s | còn %.4f %s',
                $item->export?->code ?: 'EXP-' . $item->export_id,
                $item->product_code ?: $item->product_id,
                $item->product_name ?: 'Sản phẩm',
                $remainingQty,
                $item->unit_name ?: ''
            ),
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

    protected function getPackageValues(): array
    {
        $request = $this->getRequest();

        if ($request && is_array($request->input('packages'))) {
            return $request->input('packages');
        }

        $model = $this->getModel();

        if (! $model instanceof PackingList || ! $model->exists) {
            return [];
        }

        $model->loadMissing(['packages.items', 'packages.legacyItems']);

        return $model->packages
            ->map(function ($package) {
                return [
                    'id' => $package->getKey(),
                    'package_code' => $package->package_code,
                    'package_no' => $package->package_no,
                    'package_type_id' => $package->package_type_id,
                    'status' => $package->status,
                    'length' => $package->length,
                    'width' => $package->width,
                    'height' => $package->height,
                    'dimension_unit' => $package->dimension_unit,
                    'volume' => $package->volume,
                    'volume_weight' => $package->volume_weight,
                    'weight' => $package->weight,
                    'weight_unit' => $package->weight_unit,
                    'tracking_code' => $package->tracking_code,
                    'shipping_label_url' => $package->shipping_label_url,
                    'note' => $package->note,
                    'items' => ($package->items->isNotEmpty() ? $package->items : $package->legacyItems)->map(function ($item) {
                        return [
                            'id' => $item->getKey(),
                            'export_item_id' => $item->export_item_id,
                            'product_id' => $item->product_id,
                            'product_variation_id' => $item->product_variation_id,
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name,
                            'packed_qty' => $item->packed_qty,
                            'unit_id' => $item->unit_id,
                            'unit_name' => $item->unit_name,
                            'warehouse_location_id' => $item->warehouse_location_id,
                            'pallet_id' => $item->pallet_id,
                            'batch_id' => $item->batch_id,
                            'goods_receipt_batch_id' => $item->goods_receipt_batch_id,
                            'stock_balance_id' => $item->stock_balance_id,
                            'storage_item_id' => $item->storage_item_id,
                            'lot_no' => $item->lot_no,
                            'expiry_date' => $item->expiry_date,
                            'note' => $item->note,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
