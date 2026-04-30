<?php

namespace Botble\Inventory\Domains\WarehouseProduct\DTO;

use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductAssignRequest;

class WarehouseProductAssignDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly array $warehouseIds,
    ) {
    }

    public static function fromRequest(WarehouseProductAssignRequest $request): self
    {
        $data = $request->validated();

        return new self(
            productId: (int) $data['product_id'],
            warehouseIds: array_values(array_unique(array_map('intval', $data['warehouse_ids']))),
        );
    }
}
