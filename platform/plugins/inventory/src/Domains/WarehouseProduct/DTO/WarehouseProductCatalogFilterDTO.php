<?php

namespace Botble\Inventory\Domains\WarehouseProduct\DTO;

use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductCatalogFilterRequest;

class WarehouseProductCatalogFilterDTO
{
    public function __construct(
        public readonly string $query = '',
        public readonly string $status = 'all',
        public readonly ?int $warehouseId = null,
    ) {
    }

    public static function fromRequest(WarehouseProductCatalogFilterRequest $request): self
    {
        $data = $request->validated();

        return new self(
            query: trim((string) ($data['q'] ?? '')),
            status: (string) ($data['status'] ?? 'all'),
            warehouseId: isset($data['warehouse_id']) ? (int) $data['warehouse_id'] : null,
        );
    }
}
