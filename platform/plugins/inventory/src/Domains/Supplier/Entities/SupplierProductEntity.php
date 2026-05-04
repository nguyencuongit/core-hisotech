<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

use Carbon\CarbonImmutable;

final class SupplierProductEntity
{
    public function __construct(
        public readonly int|string|null $id,
        public readonly int|string|null $supplierId,
        public readonly int|string|null $productId,
        public readonly ?string $supplierSku,
        public readonly ?float $purchasePrice,
        public readonly ?int $moq,
        public readonly ?int $leadTimeDays,
        public readonly ?string $productName,
        public readonly ?string $productSku,
        public readonly ?CarbonImmutable $createdAt,
        public readonly ?CarbonImmutable $updatedAt,
    ) {
    }
}
