<?php 
namespace Botble\Logistics\DTO;

class CancelOrderShippingDTO
{
    public function __construct(
        public ?bool $success,
        public ?string $message,
    ) {}
}