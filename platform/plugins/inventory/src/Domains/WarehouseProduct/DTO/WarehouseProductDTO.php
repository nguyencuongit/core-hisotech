<?php

namespace Botble\Inventory\Domains\WarehouseProduct\DTO;

use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductRequest;

class WarehouseProductDTO
{
    public function __construct(
        public readonly ?int $product_id = null,
        public readonly ?int $product_variation_id = null,
        public readonly ?string $supplier_id = null,
        public readonly ?string $supplier_product_id = null,
        public readonly bool $is_active = true,
        public readonly ?string $note = null,
        public readonly array $present = [],
    ) {
    }

    public static function fromRequest(WarehouseProductRequest $request): self
    {
        $data = $request->validated();

        return new self(
            product_id: array_key_exists('product_id', $data) && $data['product_id'] !== null ? (int) $data['product_id'] : null,
            product_variation_id: array_key_exists('product_variation_id', $data) && $data['product_variation_id'] !== null ? (int) $data['product_variation_id'] : null,
            supplier_id: $data['supplier_id'] ?? null,
            supplier_product_id: $data['supplier_product_id'] ?? null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : true,
            note: $data['note'] ?? null,
            present: array_keys($data),
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->present, true);
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'product_variation_id' => $this->product_variation_id,
            'supplier_id' => $this->supplier_id,
            'supplier_product_id' => $this->supplier_product_id,
            'is_active' => $this->is_active,
            'note' => $this->note,
        ];
    }
}
