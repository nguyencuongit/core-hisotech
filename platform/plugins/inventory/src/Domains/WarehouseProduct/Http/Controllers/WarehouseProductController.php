<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\WarehouseProduct\DTO\SupplierProductSuggestionDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductSearchDTO;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductRequest;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductSearchRequest;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductSupplierProductRequest;
use Botble\Inventory\Domains\WarehouseProduct\UseCases\WarehouseProductUsecase;
use Illuminate\Http\JsonResponse;

class WarehouseProductController extends BaseController
{
    public function store($warehouse, WarehouseProductRequest $request, WarehouseProductUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $usecase->create((int) $warehouse, WarehouseProductDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function update($warehouse, $warehouseProduct, WarehouseProductRequest $request, WarehouseProductUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $usecase->update((int) $warehouse, (int) $warehouseProduct, WarehouseProductDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy($warehouse, $warehouseProduct, WarehouseProductUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $usecase->delete((int) $warehouse, (int) $warehouseProduct);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.warehouse.show', $warehouse))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function searchProducts($warehouse, WarehouseProductSearchRequest $request, WarehouseProductUsecase $usecase): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.index'), 403);

        return response()->json([
            'results' => $usecase->searchProducts((int) $warehouse, WarehouseProductSearchDTO::fromRequest($request)),
        ]);
    }

    public function supplierProduct(WarehouseProductSupplierProductRequest $request, WarehouseProductUsecase $usecase): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.index'), 403);

        return response()->json([
            'data' => $usecase->supplierProductSuggestion(SupplierProductSuggestionDTO::fromRequest($request)),
        ]);
    }
}
