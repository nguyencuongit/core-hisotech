@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php($canApproveSupplier = auth()->user()?->isSuperUser() === true)

    <div class="page-body">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">{{ trans('plugins/inventory::inventory.supplier.edit') }}</h3>
                    <div>{!! $supplier->status?->toHtml() !!}</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')
                        @include('plugins/inventory::suppliers.partials.form', ['supplier' => $supplier])
                    </form>

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        @if(in_array($supplier->status?->value, ['draft', 'rejected', 'inactive'], true))
                            <form method="POST" action="{{ route('inventory.suppliers.submit', $supplier) }}">
                                @csrf
                                <button class="btn btn-warning" type="submit">{{ trans('plugins/inventory::inventory.supplier.approval.submit') }}</button>
                            </form>
                        @endif

                        @if($canApproveSupplier && $supplier->status?->value === 'pending_approval')
                            <a class="btn btn-success" href="{{ route('inventory.suppliers.approval', $supplier) }}">
                                {{ trans('plugins/inventory::inventory.supplier.approval_page.title') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
