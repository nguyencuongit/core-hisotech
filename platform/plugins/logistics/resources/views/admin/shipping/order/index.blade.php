@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="table-wrapper">
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link @if(!request()->has('status')) active @endif"
                                href="{{ route('logistics.shipping.order.index') }}">
                                {{ trans('core/base::tables.all') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request('status') == 'DRAFT') active @endif"
                                href="{{ route('logistics.shipping.order.index', ['status' => 'DRAFT']) }}">
                                Chưa tạo đơn
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request('status') == 'CREATED') active @endif"
                                href="{{ route('logistics.shipping.order.index', ['status' => 'CREATED']) }}">
                                Đã tạo đơn
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request('status') == 'SHIPPING') active @endif"
                                href="{{ route('logistics.shipping.order.index', ['status' => 'SHIPPING']) }}">
                                Đang vận chuyển
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request('status') == 'DELIVERED') active @endif"
                                href="{{ route('logistics.shipping.order.index', ['status' => 'DELIVERED']) }}">
                                Đã giao thành công
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request('status') == 'CANCELLED') active @endif"
                                href="{{ route('logistics.shipping.order.index', ['status' => 'CANCELLED']) }}">
                                Đã hủy
                            </a>
                        </li>
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
    <script>
        $(document).ready(function () {
            $(document).on('click', '.nav-tabs.card-header-tabs .nav-link', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');

                // 1. Update UI Active State
                $(this).closest('.nav-tabs').find('.nav-link').removeClass('active');
                $(this).addClass('active');

                // 2. Update Browser URL (No reload)
                window.history.pushState(null, '', url);

                // 3. Reload DataTable via Ajax
                const table = $('.dataTable').DataTable();
                if (table) {
                    table.ajax.url(url).load();
                }
            });
        });
    </script>
@endpush