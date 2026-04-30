<?php

namespace Botble\Inventory\Domains\WarehouseProduct\DTO;

use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductToggleRequest;

class WarehouseProductToggleDTO
{
    public function __construct(
        public readonly int $warehouseId,
        public readonly ?int $productId = null,
        public readonly array $addProductIds = [],
        public readonly array $removeProductIds = [],
    ) {
    }

    public static function fromRequest(WarehouseProductToggleRequest $request): self
    {
        $data = $request->validated();

        return new self(
            warehouseId: (int) $data['warehouse_id'],
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            addProductIds: self::normalizeIds($data['add_product_ids'] ?? []),
            removeProductIds: self::normalizeIds($data['remove_product_ids'] ?? []),
        );
    }

    public function isBulk(): bool
    {
        return $this->productId === null;
    }

    protected static function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id): bool => $id > 0)));
    }
}
