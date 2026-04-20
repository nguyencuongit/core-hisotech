<?php

namespace Botble\Logistics\DTO;

class ProvinceDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}