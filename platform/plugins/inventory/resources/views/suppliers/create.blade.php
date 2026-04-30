@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-body supplier-glassline-page">
        <div class="container-fluid supplier-glassline supplier-page-wrap">
            <div class="supplier-page-header">
                <div>
                    <div class="supplier-eyebrow">{{ trans('plugins/inventory::inventory.supplier.name') }}</div>
                    <h1 class="supplier-page-title">{{ trans('plugins/inventory::inventory.supplier.create') }}</h1>
                    <div class="supplier-page-meta">{{ trans('plugins/inventory::inventory.supplier.code_placeholder') }}</div>
                </div>

                <div class="supplier-page-actions">
                    <a class="btn supplier-btn-secondary" href="{{ route('inventory.suppliers.index') }}">
                        {{ trans('core/base::forms.cancel') }}
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('inventory.suppliers.store') }}">
                @csrf
                @include('plugins/inventory::suppliers.partials.form')
            </form>
        </div>
    </div>
@endsection
