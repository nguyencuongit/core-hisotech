<?php
namespace Botble\ViettelPost\Services;

class ViettelPostShippingService
{
    public function __construct(protected ViettelPostApiService $apiService)
    {
    }

    public function calculateFee($data): float
    {
        return $this->apiService->calculateFee($data);
    }

    public function getServices(): array
    {
        return $this->apiService->getServices();
    }
}
