<?php

namespace Botble\Logistics\Exceptions;

use Exception;

class ShippingException extends Exception
{
    public function __construct(
        string $message = "Không thể tạo đơn giao hàng",
        public ?string $rawMessage = null,
        public ?string $provider = null,
    ) {
        parent::__construct($message);
    }

    public static function fromProvider(string $provider, string $message): self
    {
        return new self("[$provider] $message");
    }
}