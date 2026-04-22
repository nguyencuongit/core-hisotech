<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Usecase\WarehouseProductCatalogUsecase;
use Illuminate\Http\Request;

class WarehouseProductCatalogController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/inventory::inventory.warehouse_product.name'), route('inventory.warehouse-products.index'));
    }

    public function index(Request $request, WarehouseProductCatalogUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('warehouse.index'), 403);

        $this->pageTitle(trans('plugins/inventory::inventory.warehouse_product.name'));

        return view('plugins/inventory::warehouse-products.index', $usecase->list($request->all()));
    }
}
