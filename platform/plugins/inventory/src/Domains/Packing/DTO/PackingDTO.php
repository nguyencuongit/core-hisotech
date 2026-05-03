<?php

namespace Botble\Inventory\Domains\Packing\DTO;

use Botble\Inventory\Domains\Packing\Http\Requests\PackingRequest;
use Illuminate\Support\Arr;

class PackingDTO
{
    public function __construct(
        public readonly array $attributes,
        public readonly array $packages,
    ) {
    }

    public static function fromRequest(PackingRequest $request): self
    {
        return self::fromArray($request->validated());
    }

    public static function fromArray(array $data): self
    {
        return new self(
            self::normalizeAttributes($data),
            self::normalizePackages(Arr::get($data, 'packages', []))
        );
    }

    public function toArray(): array
    {
        return array_merge($this->attributes, [
            'packages' => $this->packages,
        ]);
    }

    protected static function normalizeAttributes(array $data): array
    {
        return [
            'code' => self::nullableString(Arr::get($data, 'code')),
            'export_id' => self::nullableInteger(Arr::get($data, 'export_id')),
            'warehouse_id' => self::nullableInteger(Arr::get($data, 'warehouse_id')),
            'status' => self::nullableString(Arr::get($data, 'status')) ?: 'draft',
            'packer_id' => self::nullableInteger(Arr::get($data, 'packer_id')),
            'packed_at' => Arr::get($data, 'packed_at'),
            'started_at' => Arr::get($data, 'started_at'),
            'completed_at' => Arr::get($data, 'completed_at'),
            'cancelled_at' => Arr::get($data, 'cancelled_at'),
            'cancelled_by' => self::nullableInteger(Arr::get($data, 'cancelled_by')),
            'cancelled_reason' => self::nullableString(Arr::get($data, 'cancelled_reason')),
            'total_packages' => self::nullableInteger(Arr::get($data, 'total_packages')),
            'total_items' => self::nullableDecimal(Arr::get($data, 'total_items')),
            'total_weight' => self::nullableDecimal(Arr::get($data, 'total_weight')),
            'total_volume' => self::nullableDecimal(Arr::get($data, 'total_volume')),
            'note' => self::nullableString(Arr::get($data, 'note')),
        ];
    }

    protected static function normalizePackages(array $packages): array
    {
        $rows = [];

        foreach (array_values($packages) as $index => $package) {
            if (! is_array($package)) {
                continue;
            }

            $items = self::normalizeItems(Arr::get($package, 'items', []));

            if ($items === [] && ! self::nullableString(Arr::get($package, 'package_code'))) {
                continue;
            }

            $length = self::nullableDecimal(Arr::get($package, 'length')) ?? 0.0;
            $width = self::nullableDecimal(Arr::get($package, 'width')) ?? 0.0;
            $height = self::nullableDecimal(Arr::get($package, 'height')) ?? 0.0;
            $volume = self::nullableDecimal(Arr::get($package, 'volume'));

            $rows[] = [
                'id' => self::nullableInteger(Arr::get($package, 'id')),
                'package_code' => self::nullableString(Arr::get($package, 'package_code')),
                'package_no' => self::nullableInteger(Arr::get($package, 'package_no')) ?: $index + 1,
                'package_type_id' => self::nullableString(Arr::get($package, 'package_type_id')) ?: self::nullableString(Arr::get($package, 'package_type')),
                'status' => self::nullableString(Arr::get($package, 'status')) ?: 'open',
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'dimension_unit' => self::nullableString(Arr::get($package, 'dimension_unit')) ?: 'cm',
                'volume' => $volume ?? round($length * $width * $height, 4),
                'volume_weight' => self::nullableDecimal(Arr::get($package, 'volume_weight')) ?? 0.0,
                'weight' => self::nullableDecimal(Arr::get($package, 'weight')) ?? 0.0,
                'weight_unit' => self::nullableString(Arr::get($package, 'weight_unit')) ?: 'kg',
                'tracking_code' => self::nullableString(Arr::get($package, 'tracking_code')),
                'shipping_label_url' => self::nullableString(Arr::get($package, 'shipping_label_url')),
                'note' => self::nullableString(Arr::get($package, 'note')),
                'items' => $items,
            ];
        }

        return $rows;
    }

    protected static function normalizeItems(array $items): array
    {
        $rows = [];

        foreach (array_values($items) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $exportItemId = self::nullableInteger(Arr::get($item, 'export_item_id'));
            $productId = self::nullableInteger(Arr::get($item, 'product_id'));
            $packedQty = self::nullableDecimal(Arr::get($item, 'packed_qty')) ?? 0.0;

            if ($packedQty <= 0) {
                continue;
            }

            $rows[] = [
                'id' => self::nullableInteger(Arr::get($item, 'id')),
                'export_item_id' => $exportItemId,
                'product_id' => $productId,
                'product_variation_id' => self::nullableInteger(Arr::get($item, 'product_variation_id')),
                'product_code' => self::nullableString(Arr::get($item, 'product_code')),
                'product_name' => self::nullableString(Arr::get($item, 'product_name')),
                'packed_qty' => $packedQty,
                'unit_id' => self::nullableInteger(Arr::get($item, 'unit_id')),
                'unit_name' => self::nullableString(Arr::get($item, 'unit_name')),
                'warehouse_location_id' => self::nullableInteger(Arr::get($item, 'warehouse_location_id')),
                'pallet_id' => self::nullableInteger(Arr::get($item, 'pallet_id')),
                'batch_id' => self::nullableString(Arr::get($item, 'batch_id')),
                'goods_receipt_batch_id' => self::nullableString(Arr::get($item, 'goods_receipt_batch_id')),
                'stock_balance_id' => self::nullableString(Arr::get($item, 'stock_balance_id')),
                'storage_item_id' => self::nullableString(Arr::get($item, 'storage_item_id')),
                'lot_no' => self::nullableString(Arr::get($item, 'lot_no')),
                'expiry_date' => Arr::get($item, 'expiry_date'),
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

        return (float) str_replace(',', '', (string) $value);
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
