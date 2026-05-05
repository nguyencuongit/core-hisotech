<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

final class SupplierBankEntity
{
    public function __construct(
        public readonly int|string|null $id,
        public readonly int|string|null $supplierId,
        public readonly bool $isDefault,
        public readonly ?string $bankName,
        public readonly ?string $branch,
        public readonly ?string $accountNumber,
        public readonly ?string $accountName,
    ) {
    }
}
