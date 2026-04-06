<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface OrderInterface extends RepositoryInterface 
{
    public function find(int $id);
}
