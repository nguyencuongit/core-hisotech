<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

use Botble\Inventory\Enums\SupplierAddressTypeEnum;

final class SupplierAddressEntity
{
    public function __construct(
        public readonly int|string|null $id,
        public readonly int|string|null $supplierId,
        public readonly ?SupplierAddressTypeEnum $type,
        public readonly bool $isDefault,
        public readonly ?string $address,
        public readonly int|string|null $wardId,
        public readonly int|string|null $districtId,
        public readonly int|string|null $provinceId,
        public readonly int|string|null $countryId,
    ) {
    }
}
