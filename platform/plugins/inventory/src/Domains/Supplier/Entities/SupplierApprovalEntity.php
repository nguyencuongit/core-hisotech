<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

use Carbon\CarbonImmutable;

final class SupplierApprovalEntity
{
    public function __construct(
        public readonly int|string|null $id,
        public readonly int|string|null $supplierId,
        public readonly ?string $action,
        public readonly ?string $fromStatus,
        public readonly ?string $toStatus,
        public readonly ?string $note,
        public readonly int|string|null $actedBy,
        public readonly ?string $actorName,
        public readonly ?CarbonImmutable $actedAt,
        public readonly array $meta,
    ) {
    }
}
