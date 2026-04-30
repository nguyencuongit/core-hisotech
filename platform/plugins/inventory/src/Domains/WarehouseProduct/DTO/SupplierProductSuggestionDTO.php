<?php

namespace Botble\Inventory\Domains\WarehouseProduct\DTO;

use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductSupplierProductRequest;

class SupplierProductSuggestionDTO
{
    public function __construct(
        public readonly ?string $supplierId = null,
        public readonly ?int $productId = null,
    ) {
    }

    public static function fromRequest(WarehouseProductSupplierProductRequest $request): self
    {
        $data = $request->validated();

        return new self(
            supplierId: $data['supplier_id'] ?? null,
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
        );
    }
}
