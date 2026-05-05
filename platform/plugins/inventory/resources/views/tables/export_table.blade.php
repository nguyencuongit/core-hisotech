@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $currentStatus = \Botble\Inventory\Enums\DocumentStatusEnum::tryFrom(strtolower((string) request('status')));
    $statuses = \Botble\Inventory\Enums\DocumentStatusEnum::cases();
    $baseQuery = request()->except(['status', 'page']);
@endphp

@section('content')
    <div class="table-wrapper">
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link @if(! $currentStatus) active @endif"
                                href="{{ route('inventory.transactions-export.index', $baseQuery) }}">
                                {{ trans('core/base::tables.all') }}
                            </a>
                        </li>
                        @foreach($statuses as $status)
                            <li class="nav-item">
                                <a class="nav-link @if($currentStatus === $status) active @endif"
                                    href="{{ route('inventory.transactions-export.index', array_merge($baseQuery, ['status' => $status->value])) }}">
                                    {{ $status->label() }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                @include('core/table::base-table')
            </div>
        </div>
    </div>
@endsection

@push('footer')

@endpush
