<?php

namespace Botble\Inventory\Domains\Supplier\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Inventory\Domains\Supplier\DTO\SupplierApprovalDTO;
use Botble\Inventory\Domains\Supplier\DTO\SupplierDTO;
use Botble\Inventory\Domains\Supplier\DTO\SupplierProductSearchDTO;
use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierApprovalRequest;
use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierProductSearchRequest;
use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierRequest;
use Botble\Inventory\Domains\Supplier\Tables\SupplierTable;
use Botble\Inventory\Domains\Supplier\UseCases\SupplierUsecase;
use Illuminate\Http\JsonResponse;

class SupplierController extends BaseController
{
    public function __construct()
    {
        $this->breadcrumb()->add(trans('plugins/inventory::inventory.name'), route('inventory.suppliers.index'));
    }

    public function index(SupplierTable $table)
    {
        $this->pageTitle(trans('plugins/inventory::inventory.supplier.name'));

        return $table->renderTable();
    }

    public function create()
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.create'), 403);

        $this->pageTitle(trans('plugins/inventory::inventory.supplier.create'));

        return view('plugins/inventory::suppliers.create');
    }

    public function store(SupplierRequest $request, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.create'), 403);

        $supplier = $usecase->create(SupplierDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.suppliers.index'))
            ->setNextUrl(route('inventory.suppliers.edit', $supplier->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function show($supplier, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.show'), 403);

        $supplier = $usecase->loadForShow($supplier);

        $this->pageTitle($supplier->name);

        return view('plugins/inventory::suppliers.show', compact('supplier'));
    }

    public function approval($supplier, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->isSuperUser(), 403);

        $supplier = $usecase->loadForApproval($supplier);

        $this->pageTitle(trans('plugins/inventory::inventory.supplier.approval_page.title'));

        return view('plugins/inventory::suppliers.approval', compact('supplier'));
    }

    public function edit($supplier, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.edit'), 403);

        $supplier = $usecase->loadForEdit($supplier);

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $supplier->name]));

        return view('plugins/inventory::suppliers.edit', compact('supplier'));
    }

    public function update($supplier, SupplierRequest $request, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.edit'), 403);

        $usecase->update($supplier, SupplierDTO::fromRequest($request));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.suppliers.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy($supplier, SupplierUsecase $usecase): mixed
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.delete'), 403);

        $usecase->delete($supplier);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.suppliers.index'))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function submit($supplier, SupplierApprovalRequest $request, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.edit'), 403);

        $usecase->submit($supplier, SupplierApprovalDTO::fromRequest($request));

        return back()->with('success', trans('core/base::notices.update_success_message'));
    }

    public function approve($supplier, SupplierApprovalRequest $request, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->isSuperUser(), 403);

        $usecase->approve($supplier, SupplierApprovalDTO::fromRequest($request));

        return back()->with('success', trans('core/base::notices.update_success_message'));
    }

    public function reject($supplier, SupplierApprovalRequest $request, SupplierUsecase $usecase)
    {
        abort_unless(auth()->user()?->isSuperUser(), 403);

        $usecase->reject($supplier, SupplierApprovalDTO::fromRequest($request));

        return back()->with('success', trans('core/base::notices.update_success_message'));
    }

    public function searchProducts(SupplierProductSearchRequest $request, SupplierUsecase $usecase): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.index'), 403);

        return response()->json([
            'results' => $usecase->searchProducts(SupplierProductSearchDTO::fromRequest($request)),
        ]);
    }
}
