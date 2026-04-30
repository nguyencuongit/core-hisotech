<?php

namespace Botble\Inventory\Domains\WarehouseProduct\DTO;

use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductPolicyRequest;

class WarehouseProductPolicyDTO
{
    public function __construct(
        public readonly array $payload,
        public readonly ?string $presetCode = null,
    ) {
    }

    public static function fromRequest(WarehouseProductPolicyRequest $request): self
    {
        $data = $request->validated();
        $presetCode = $data['preset_code'] ?? null;
        unset($data['preset_code']);

        return new self(
            payload: $data,
            presetCode: $presetCode ? (string) $presetCode : null,
        );
    }
}
