<?php

namespace Botble\Logistics\Repositories\Eloquent;

use Botble\Logistics\Repositories\Interfaces\ShippingOrderInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Botble\Logistics\Enums\ShippingStatus;
use Botble\Logistics\DTO\WebhookDataDTO;


class ShippingOrderRepository extends RepositoriesAbstract implements ShippingOrderInterface 
{
    public function findByOrderId($id){
        return $this->model
            ->where('order_id', $id)
            ->where('status', '!=', 'cancel')
            ->latest() 
            ->first();
    }
    public function existsOrderId($id): bool
    {
        return $this->model
        ->where('order_id', $id)
        ->where('status', '!=', 'cancel')
        ->exists();
    }


    public function findByOrderCode($code){
        return $this->model
            ->where('code', $id)
            ->first();
    }
    public function existsOrderCode($code): bool
    {
        return $this->model
        ->where('code', $code)
        ->exists();
    }

    public function updateStatus(string $code,ShippingStatus $status): bool
    {
        return $this->model
            ->where('code',$code)
            ->update([
            'status' => $status
        ]);
    }

    public function updateStatusWebhook(WebhookDataDTO $data_webhook)
    {
        $query = $this->model->where('code', $data_webhook->orderCode);

        $query->update([
            'status' => $data_webhook->status,
            'status_name' => $data_webhook->statusName,
            'localion_currenty' => $data_webhook->localionCurrenty,
        ]);
        return $query->first();
    }
}
