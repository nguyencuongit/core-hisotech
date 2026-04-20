<?php
namespace Botble\Logistics\Services\Factories;

use Botble\Logistics\Services\Contracts\ShippingServiceInterface;
use Botble\Logistics\Services\Drivers\GHNDriver;
use Botble\Logistics\Services\Drivers\ViettelPostDriver;

class ShippingFactory
{
    public static function make(string $driver): ShippingServiceInterface
    {
        return match ($driver) {
            'ghn' => app(GHNDriver::class),
            'viettelpost' => app(ViettelPostDriver::class),

            default => throw new \InvalidArgumentException("Driver [$driver] not supported"),
        };
    }
}