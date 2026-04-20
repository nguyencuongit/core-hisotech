<?php

namespace Botble\Logistics\DTO;

class DistrictDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}