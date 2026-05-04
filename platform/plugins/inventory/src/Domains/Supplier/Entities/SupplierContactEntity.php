<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

final class SupplierContactEntity
{
    public function __construct(
        public readonly int|string|null $id,
        public readonly int|string|null $supplierId,
        public readonly bool $isPrimary,
        public readonly ?string $name,
        public readonly ?string $position,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $identityNumber,
        public readonly array $socialContact,
    ) {
    }
}
