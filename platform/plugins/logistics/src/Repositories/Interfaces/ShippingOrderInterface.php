<?php

namespace Botble\Logistics\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Botble\Logistics\Enums\ShippingStatus;
use Botble\Logistics\DTO\WebhookDataDTO;

interface ShippingOrderInterface extends RepositoryInterface 
{
    public function findByOrderId($id);
    public function existsOrderId(string $id): bool;


    public function findByOrderCode(string $code);
    public function existsOrderCode(string $id): bool;

    public function updateStatus(string $code, ShippingStatus $status): bool;
    public function updateStatusWebhook(WebhookDataDTO $data_webhook);
}
