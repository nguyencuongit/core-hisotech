@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">{{ trans('plugins/inventory::inventory.supplier.create') }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.suppliers.store') }}">
                        @csrf
                        @include('plugins/inventory::suppliers.partials.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
