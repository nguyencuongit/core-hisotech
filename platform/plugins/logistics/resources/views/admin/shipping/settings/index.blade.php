@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<style>
    .body-shipping-setting{
        border: 1px solid #d8d8d8;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .body-shipping-setting .sidebar { min-width: 240px; background: white; min-height: 100vh; border-right: 1px solid #dee2e6; border-radius: 20px;}
    .nav-link { color: #495057; padding: 0.8rem 1.5rem; border-right: 3px solid transparent; }
    .nav-link.active { background-color: #e7f1ff; color: #0d6efd; border-right-color: #0d6efd; font-weight: 500; }
    .nav-link:hover { background-color: #f1f3f5; }
    .main-card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
</style>
<div class="d-flex body-shipping-setting">
    <div class="sidebar d-none d-md-block">
        <div class="py-4 px-3">
            <h5 class="fw-bold">Phương thức giao hàng</h5>
        </div>
        <nav class="nav flex-column">
            @if($shippingProvider)
                @foreach($shippingProvider as $index => $item)
                <a class="nav-link provider-item {{ $index === 0 ? 'active' : '' }}" href="#"  data-id="{{ $item->id }}"><i class="bi bi-house-door me-2"></i> {{$item->name}}</a>
                @endforeach
            @endif
            <a class="nav-link provider-item" href="#" data-id="admin-address" ><i class="bi bi-house-door me-2"></i>Địa chỉ kho Admin</a>

        </nav>
    </div>

    <div class="flex-grow-1 p-4">
        @foreach($shippingProvider as $index => $item)
        <form action="{{ route('logistics.providers.update', $item->id) }}" method="post" class="provider-form {{ $index === 0 ? '' : 'd-none' }}" data-id="{{ $item->id }}" >
            @csrf

            <div class="d-flex justify-content-between align-items-center mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item active text-secondary"></li>
                    </ol>
                </nav>
                <div>
                    <button class="btn btn-primary px-4">Lưu thay đổi</button>
                </div>
                
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('logistics.provicen', $item->code) }}" class="btn btn-primary px-4">ID Tỉnh/TP</a>
                    <a href="{{ route('logistics.district', $item->code) }}" class="btn btn-primary px-4">ID Quận/Huyện</a>
                </div>
                <div>
                    <button type="button" class="btn btn-success">
                        kích hoạt
                    </button>
                    <button type="button" class="btn btn-success add-config">
                        + Thêm config
                    </button>
                </div>
                
            </div>

            <div class="card main-card p-4">
                <div id="config-wrapper">
                </div>
                @foreach($item->information as $key => $value)
                    <div id="config-template" class="">
                        <div class="mb-3 config-item border p-3 rounded">
                            <div class="mb-2">
                                <label class="form-label fw-semibold">Key</label>
                                <input type="text" name="data[{{ $loop->index }}][key]" class="form-control" placeholder="VD: token" value="{{$key}}">
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-semibold">Value</label>
                                <input type="text" name="data[{{ $loop->index }}][value]" class="form-control" value="{{$value}}">
                            </div>

                            <button type="button" class="btn btn-danger btn-sm remove-config">
                                Xóa
                            </button>
                        </div>
                    </div>
                @endforeach
                
            </div>
        </form>
        @endforeach

        
        <form action="{{ route('logistics.address.admin') }}" method="post" class="provider-form d-none" data-id="admin-address">
             @csrf
            <div class="card main-card p-4">
                <h3 class="mb-3">Địa chỉ kho Admin</h3>

                <!-- Tên + SĐT -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên</label>
                        <input type="text" class="form-control" name="name" value = "{{$address_admin['logistics_admin_name'] ?? ''}}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">SĐT</label>
                        <input type="text" class="form-control" name="phone" value = "{{$address_admin['logistics_admin_phone']  ?? ''}}">
                    </div>
                </div>

                <!-- Địa chỉ -->
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" class="form-control" name="address" value = "{{$address_admin['logistics_admin_address'] ?? ''}}">
                </div>

                <!-- Tỉnh / Quận / Xã -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label ">Tỉnh / Thành phố</label>
                        <select class="form-control js-province" data-target="#district" name="state_id" data-selected="{{$address_admin['logistics_admin_state_id'] ?? ''}}" >
                            <option value="">Chọn tỉnh</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" {{ $state->id == $address_admin['logistics_admin_state_id'] ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Phường/Xã</label>
                        <select class="form-control" id="district" name="city_id" data-district="{{$address_admin['logistics_admin_city_id'] ?? ''}}">
                            <option value="">--Chọn--</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.querySelectorAll('.add-config');
    if (addBtn) {
        addBtn.forEach(btn => {
            btn.addEventListener('click', function () {
                let form = this.closest('form');
                let wrapper = form.querySelector('#config-wrapper');
                let index = form.querySelectorAll('.config-item').length;
                let template = form.querySelector('#config-template .config-item').cloneNode(true);
                template.querySelectorAll('input').forEach(input => {
                    if (input.name.includes('[key]')) {
                        input.name = `data[${index}][key]`;
                        input.value = '';
                    }
                    if (input.name.includes('[value]')) {
                        input.name = `data[${index}][value]`;
                        input.value = '';
                    }
                });
                wrapper.appendChild(template);
            });
        });
    }
    // remove
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-config')) {
            e.target.closest('.config-item').remove();
        }
    });
});

document.querySelectorAll('.provider-item, .provider-address').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();

        let id = this.dataset.id;

        // remove active
        document.querySelectorAll('.provider-item, .provider-address').forEach(el => {
            el.classList.remove('active');
        });

        this.classList.add('active');

        // ẩn tất cả form
        document.querySelectorAll('.provider-form').forEach(form => {
            form.classList.add('d-none');
        });

        // show form đúng id
        let target = document.querySelector('.provider-form[data-id="'+id+'"]');
        if (target) {
            target.classList.remove('d-none');
        }
    });
});
</script>
@endsection