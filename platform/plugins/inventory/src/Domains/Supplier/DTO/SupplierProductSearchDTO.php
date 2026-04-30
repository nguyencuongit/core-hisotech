<?php

namespace Botble\Inventory\Domains\Supplier\DTO;

use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierProductSearchRequest;

class SupplierProductSearchDTO
{
    public function __construct(
        public readonly string $query = '',
    ) {
    }

    public static function fromRequest(SupplierProductSearchRequest $request): self
    {
        $data = $request->validated();

        return new self(
            query: trim((string) ($data['q'] ?? '')),
        );
    }
}
