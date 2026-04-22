<?php

namespace Botble\Inventory\Domains\Supplier\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Inventory\Domains\Supplier\Http\Requests\SupplierRequest;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Services\SupplierService;
use Botble\Inventory\Domains\Supplier\Tables\SupplierTable;
use Botble\Inventory\Enums\SupplierStatusEnum;
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

    public function store(SupplierRequest $request, SupplierService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.create'), 403);

        $supplier = $service->create(array_merge($request->validated(), [
            'status' => $request->input('action') === 'submit' ? SupplierStatusEnum::PENDING_APPROVAL->value : $request->input('status', SupplierStatusEnum::DRAFT->value),
        ]));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.suppliers.index'))
            ->setNextUrl(route('inventory.suppliers.edit', $supplier->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function show(Supplier $supplier)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.show'), 403);

        $supplier->load(['contacts', 'addresses', 'banks', 'supplierProducts.product', 'approvals.actor', 'creator', 'submitter', 'approver']);

        $this->pageTitle($supplier->name);

        return view('plugins/inventory::suppliers.show', compact('supplier'));
    }

    public function approval(Supplier $supplier)
    {
        abort_unless(auth()->user()?->isSuperUser(), 403);

        $supplier->load(['contacts', 'addresses', 'banks', 'supplierProducts.product', 'approvals.actor', 'creator', 'submitter', 'approver']);

        $this->pageTitle(trans('plugins/inventory::inventory.supplier.approval_page.title'));

        return view('plugins/inventory::suppliers.approval', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.edit'), 403);

        $supplier->load(['contacts', 'addresses', 'banks', 'supplierProducts.product', 'creator', 'submitter', 'approver']);

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $supplier->name]));

        return view('plugins/inventory::suppliers.edit', compact('supplier'));
    }

    public function update(Supplier $supplier, SupplierRequest $request, SupplierService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.edit'), 403);

        $service->update($supplier, $request->validated());

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('inventory.suppliers.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Supplier $supplier)
    {
        return DeleteResourceAction::make($supplier);
    }

    public function submit(Supplier $supplier, SupplierService $service)
    {
        abort_unless(auth()->user()?->hasPermission('inventory.suppliers.edit'), 403);

        $service->submitForApproval($supplier, request('note'));

        return back()->with('success', trans('core/base::notices.update_success_message'));
    }

    public function approve(Supplier $supplier, SupplierService $service)
    {
        abort_unless(auth()->user()?->isSuperUser(), 403);

        $service->approve($supplier, request('note'));

        return back()->with('success', trans('core/base::notices.update_success_message'));
    }

    public function reject(Supplier $supplier, SupplierService $service)
    {
        abort_unless(auth()->user()?->isSuperUser(), 403);

        $service->reject($supplier, request('note'));

        return back()->with('success', trans('core/base::notices.update_success_message'));
    }

    public function searchProducts(): JsonResponse
    {
        $query = trim((string) request('q'));

        $products = Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'name', 'sku', 'image', 'price'])
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%')
                        ->orWhere('id', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        $results = $products->map(function ($product) {
            $image = null;
            $name = trim((string) $product->name);
            $sku = trim((string) $product->sku);
            $text = $name ?: $sku ?: (string) $product->id;

            if ($name !== '' && $sku !== '') {
                $text .= ' (' . $sku . ')';
            }

            if (! empty($product->image)) {
                try {
                    $image = rv_media()->getImageUrl($product->image, 'thumb');
                } catch (\Throwable) {
                    $image = null;
                }
            }

            return [
                'id' => $product->id,
                'text' => $text,
                'sku' => $product->sku,
                'price' => $product->price,
                'image' => $image,
            ];
        })->values();

        return response()->json(['results' => $results]);
    }
}
