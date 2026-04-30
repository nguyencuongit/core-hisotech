<?php

namespace Botble\Inventory\Domains\WarehouseProduct\DTO;

use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductSearchRequest;

class WarehouseProductSearchDTO
{
    public function __construct(
        public readonly string $query = '',
    ) {
    }

    public static function fromRequest(WarehouseProductSearchRequest $request): self
    {
        return new self(
            query: trim((string) ($request->validated('q') ?? '')),
        );
    }
}
