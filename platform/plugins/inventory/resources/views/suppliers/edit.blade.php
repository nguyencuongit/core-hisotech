@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $canApproveSupplier = auth()->user()?->isSuperUser() === true;
        $supplierStatusValue = $supplier->status?->value;
    @endphp

    <div class="page-body supplier-glassline-page">
        <div class="container-fluid supplier-glassline supplier-page-wrap">
            <div class="supplier-page-header">
                <div>
                    <div class="supplier-eyebrow">{{ trans('plugins/inventory::inventory.supplier.edit') }}</div>
                    <h1 class="supplier-page-title">{{ $supplier->name }}</h1>
                    <div class="supplier-page-meta d-flex flex-wrap align-items-center gap-2">
                        <span>{{ $supplier->code }}</span>
                        <span>/</span>
                        <span>{{ $supplier->type?->label() }}</span>
                        <span>/</span>
                        <span>{!! $supplier->status?->toHtml() !!}</span>
                    </div>
                </div>

                <div class="supplier-page-actions">
                    <a class="btn supplier-btn-secondary" href="{{ route('inventory.suppliers.index') }}">
                        {{ trans('core/base::forms.cancel') }}
                    </a>

                    @if(in_array($supplierStatusValue, ['draft', 'rejected', 'inactive'], true))
                        <form method="POST" action="{{ route('inventory.suppliers.submit', $supplier->id) }}">
                            @csrf
                            <button class="btn supplier-btn-secondary" type="submit">
                                {{ trans('plugins/inventory::inventory.supplier.approval.submit') }}
                            </button>
                        </form>
                    @endif

                    @if($canApproveSupplier && $supplierStatusValue === 'pending_approval')
                        <a class="btn supplier-btn-secondary" href="{{ route('inventory.suppliers.approval', $supplier->id) }}">
                            {{ trans('plugins/inventory::inventory.supplier.approval_page.title') }}
                        </a>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('inventory.suppliers.update', $supplier->id) }}">
                @csrf
                @method('PUT')
                @include('plugins/inventory::suppliers.partials.form', ['supplier' => $supplier])
            </form>
        </div>
    </div>
@endsection
