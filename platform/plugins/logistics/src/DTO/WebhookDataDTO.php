<?php 
namespace Botble\Logistics\DTO;

class WebhookDataDTO
{
    public function __construct(
        public string $orderCode,
        public string $status,
        public string $statusName,
        public string $localionCurrenty,     
    ) {}
}
