<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ShippingProviderInterface extends RepositoryInterface
{
    public function findCode(string $code);

    public function findByIsActive($action);
}
