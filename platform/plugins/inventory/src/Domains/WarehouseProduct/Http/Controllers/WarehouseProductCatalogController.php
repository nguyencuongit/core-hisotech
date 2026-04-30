<?php

namespace Botble\Inventory\Domains\WarehouseProduct\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductAssignDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductCatalogFilterDTO;
use Botble\Inventory\Domains\WarehouseProduct\DTO\WarehouseProductToggleDTO;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductAssignRequest;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductCatalogFilterRequest;
use Botble\Inventory\Domains\WarehouseProduct\Http\Requests\WarehouseProductToggleRequest;
use Botble\Inventory\Domains\WarehouseProduct\UseCases\WarehouseProductCatalogUsecase;

class WarehouseProductCatalogController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/inventory::inventory.warehouse_product.name'), route('inventory.warehouse-products.index'));
    }

    public function index(WarehouseProductCatalogFilterRequest $request, WarehouseProductCatalogUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.index'), 403);

        $this->pageTitle(trans('plugins/inventory::inventory.warehouse_product.name'));

        return view('plugins/inventory::warehouse-products.index', $usecase->list(WarehouseProductCatalogFilterDTO::fromRequest($request)));
    }

    public function assign(WarehouseProductAssignRequest $request, WarehouseProductCatalogUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $usecase->assignProductToWarehouses(WarehouseProductAssignDTO::fromRequest($request));

        return back()->with('success', trans('plugins/inventory::inventory.warehouse_product.added_to_warehouse'));
    }

    public function toggle(WarehouseProductToggleRequest $request, WarehouseProductCatalogUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.products.manage'), 403);

        $result = $usecase->toggle(WarehouseProductToggleDTO::fromRequest($request));

        if ($result === null) {
            return back()->with('success', trans('plugins/inventory::inventory.warehouse_product.updated_warehouse_products'));
        }

        return back()->with(
            'success',
            trans('plugins/inventory::inventory.warehouse_product.' . ($result === 'added' ? 'added_to_warehouse' : 'removed_from_warehouse'))
        );
    }
}
