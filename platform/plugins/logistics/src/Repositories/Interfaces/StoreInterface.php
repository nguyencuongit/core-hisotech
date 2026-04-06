<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface StoreInterface extends RepositoryInterface
{
    public function findByCustomerId(int $id);
}
