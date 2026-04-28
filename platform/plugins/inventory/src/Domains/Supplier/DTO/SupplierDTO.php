<?php

namespace Botble\Inventory\Domains\Supplier\DTO;

use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierRequest;
use Illuminate\Support\Arr;

class SupplierDTO
{
    public function __construct(
        public readonly array $attributes,
    ) {
    }

    public static function fromRequest(SupplierRequest $request): self
    {
        return new self($request->validated());
    }

    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function only(array $keys): array
    {
        return Arr::only($this->attributes, $keys);
    }
}
