<?php

namespace Botble\Inventory\Domains\Transfer\DTO;

use Botble\Inventory\Domains\Transfer\Http\Requests\TransferRequest;
use Illuminate\Support\Arr;

class TransferDTO
{
    public function __construct(
        public readonly array $attributes,
        public readonly array $items,
        public readonly string $action,
    ) {
    }

    public static function fromRequest(TransferRequest $request): self
    {
        return self::fromArray($request->validated());
    }

    public static function fromArray(array $data): self
    {
        return new self(
            self::normalizeAttributes($data),
            self::normalizeItems(Arr::get($data, 'items', [])),
            self::nullableString(Arr::get($data, 'workflow_action')) ?: 'save'
        );
    }

    protected static function normalizeAttributes(array $data): array
    {
        return [
            'code' => self::nullableString(Arr::get($data, 'code')),
            'status' => self::nullableString(Arr::get($data, 'status')) ?: 'draft',
            'from_warehouse_id' => self::nullableInteger(Arr::get($data, 'from_warehouse_id')),
            'to_warehouse_id' => self::nullableInteger(Arr::get($data, 'to_warehouse_id')),
            'requested_by' => self::nullableInteger(Arr::get($data, 'requested_by')),
            'approved_by' => self::nullableInteger(Arr::get($data, 'approved_by')),
            'exported_by' => self::nullableInteger(Arr::get($data, 'exported_by')),
            'imported_by' => self::nullableInteger(Arr::get($data, 'imported_by')),
            'transfer_date' => Arr::get($data, 'transfer_date'),
            'reason' => self::nullableString(Arr::get($data, 'reason')),
            'note' => self::nullableString(Arr::get($data, 'note')),
        ];
    }

    protected static function normalizeItems(array $items): array
    {
        $rows = [];

        foreach (array_values($items) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $stockBalanceId = self::nullableString(Arr::get($item, 'stock_balance_id'));
            $productId = self::nullableInteger(Arr::get($item, 'product_id'));
            $requestedQty = self::nullableDecimal(Arr::get($item, 'requested_qty', Arr::get($item, 'qty'))) ?? 0.0;

            if (! $stockBalanceId && ! $productId && $requestedQty <= 0) {
                continue;
            }

            $rows[] = [
                'id' => self::nullableInteger(Arr::get($item, 'id')),
                '_has_received_qty' => array_key_exists('received_qty', $item),
                '_has_damaged_qty' => array_key_exists('damaged_qty', $item),
                'stock_balance_id' => $stockBalanceId,
                'product_id' => $productId,
                'product_variation_id' => self::nullableInteger(Arr::get($item, 'product_variation_id')),
                'product_code' => self::nullableString(Arr::get($item, 'product_code')),
                'product_name' => self::nullableString(Arr::get($item, 'product_name')),
                'requested_qty' => $requestedQty,
                'exported_qty' => self::nullableDecimal(Arr::get($item, 'exported_qty')) ?? 0.0,
                'received_qty' => self::nullableDecimal(Arr::get($item, 'received_qty')) ?? 0.0,
                'damaged_qty' => self::nullableDecimal(Arr::get($item, 'damaged_qty')) ?? 0.0,
                'shortage_qty' => self::nullableDecimal(Arr::get($item, 'shortage_qty')) ?? 0.0,
                'overage_qty' => self::nullableDecimal(Arr::get($item, 'overage_qty')) ?? 0.0,
                'unit_id' => self::nullableInteger(Arr::get($item, 'unit_id')),
                'unit_name' => self::nullableString(Arr::get($item, 'unit_name')),
                'from_location_id' => self::nullableInteger(Arr::get($item, 'from_location_id')),
                'to_location_id' => self::nullableInteger(Arr::get($item, 'to_location_id')),
                'pallet_id' => self::nullableInteger(Arr::get($item, 'pallet_id')),
                'to_pallet_id' => self::nullableInteger(Arr::get($item, 'to_pallet_id')),
                'batch_id' => self::nullableString(Arr::get($item, 'batch_id')),
                'goods_receipt_batch_id' => self::nullableString(Arr::get($item, 'goods_receipt_batch_id')),
                'lot_no' => self::nullableString(Arr::get($item, 'lot_no')),
                'expiry_date' => Arr::get($item, 'expiry_date'),
                'unit_price' => self::nullableDecimal(Arr::get($item, 'unit_price')) ?? 0.0,
                'amount' => self::nullableDecimal(Arr::get($item, 'amount')) ?? 0.0,
                'note' => self::nullableString(Arr::get($item, 'note')),
            ];
        }

        return $rows;
    }

    protected static function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return null;
        }

        return (int) $value;
    }

    protected static function nullableDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    protected static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
