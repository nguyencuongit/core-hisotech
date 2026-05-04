<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

final class UserRefEntity
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
