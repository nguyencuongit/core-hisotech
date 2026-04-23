<?php

namespace Botble\Inventory\Domains\Warehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Warehouse\Usecase\WarehouseProductCatalogUsecase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function assign(Request $request, WarehouseProductCatalogUsecase $usecase)
    {
        abort_unless($this->canManageWarehouseProducts(), 403);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:ec_products,id'],
            'warehouse_ids' => ['required', 'array', 'min:1'],
            'warehouse_ids.*' => ['required', 'integer', 'distinct', 'exists:inv_warehouses,id'],
        ]);

        $this->assertCanManageWarehouseIds($validated['warehouse_ids']);

        $usecase->assignProductToWarehouses((int) $validated['product_id'], $validated['warehouse_ids']);

        return back()->with('success', trans('plugins/inventory::inventory.warehouse_product.added_to_warehouse'));
    }

    public function toggle(Request $request, WarehouseProductCatalogUsecase $usecase)
    {
        abort_unless($this->canManageWarehouseProducts(), 403);

        if (! $request->filled('product_id')) {
            $validated = $request->validate([
                'warehouse_id' => ['required', 'integer', 'exists:inv_warehouses,id'],
                'add_product_ids' => ['nullable', 'array'],
                'add_product_ids.*' => ['required', 'integer', 'distinct', 'exists:ec_products,id'],
                'remove_product_ids' => ['nullable', 'array'],
                'remove_product_ids.*' => ['required', 'integer', 'distinct', 'exists:ec_products,id'],
            ]);

            $addProductIds = $validated['add_product_ids'] ?? [];
            $removeProductIds = $validated['remove_product_ids'] ?? [];

            if ($addProductIds === [] && $removeProductIds === []) {
                throw ValidationException::withMessages([
                    'product_id' => trans('plugins/inventory::inventory.warehouse_product.validation.no_products_selected'),
                ]);
            }

            $this->assertCanManageWarehouseIds([(int) $validated['warehouse_id']]);

            $usecase->applyProductChangesForWarehouse((int) $validated['warehouse_id'], $addProductIds, $removeProductIds);

            return back()->with('success', trans('plugins/inventory::inventory.warehouse_product.updated_warehouse_products'));
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:ec_products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:inv_warehouses,id'],
        ]);

        $this->assertCanManageWarehouseIds([(int) $validated['warehouse_id']]);

        $result = $usecase->toggleProductInWarehouse((int) $validated['product_id'], (int) $validated['warehouse_id']);

        return back()->with(
            'success',
            trans('plugins/inventory::inventory.warehouse_product.' . ($result === 'added' ? 'added_to_warehouse' : 'removed_from_warehouse'))
        );
    }

    protected function canManageWarehouseProducts(): bool
    {
        return (bool) auth()->user()?->hasPermission('warehouse.products.manage');
    }

    protected function assertCanManageWarehouseIds(array $warehouseIds): void
    {
        if (inventory_is_super_admin()) {
            return;
        }

        $allowedWarehouseIds = array_values(array_filter(array_map('intval', inventory_warehouse_ids())));

        foreach (array_unique(array_map('intval', $warehouseIds)) as $warehouseId) {
            abort_unless($warehouseId > 0 && in_array($warehouseId, $allowedWarehouseIds, true), 403);
        }
    }
}
