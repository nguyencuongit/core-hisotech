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

    .config-item:nth-child(odd) {
        background-color: #ffffff;
    }

    .config-item:nth-child(even) {
        background-color: #f8f9fa; /* xám nhẹ Bootstrap */
    }
    .config-item {
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .config-item:nth-child(even) {
        background-color: #f8f9fa;
    }

    .config-item:hover {
        background-color: #eef2f7;
    }
    .config-item {
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.11) !important;
    }
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
            <!-- <a class="nav-link provider-item" href="#" data-id="admin-address" ><i class="bi bi-house-door me-2"></i>Địa chỉ kho Admin</a> -->

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
                    <input type="hidden" id="active_value" name="active" value="{{$item->is_active}}">

                    <button type="button" id="toggleActive" class="btn {{$item->is_active == 1 ?'btn-danger':'btn-success'}} ">
                        {{$item->is_active == 1 ? "Huỷ kích hoạt" : "Kích hoạt"}}
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
                    <div id="config-template">
                        <div class=" card config-item border-0 mb-3">
                            <div class="card-body">

                                <div class="row g-3 align-items-center">
                                    <!-- Key -->
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold text-muted small mb-1">
                                            Key
                                        </label>
                                        <input 
                                            type="text" 
                                            name="data[{{ $loop->index }}][key]" 
                                            class="form-control" 
                                            placeholder="VD: token"
                                            value="{{ $key }}"
                                        >
                                    </div>

                                    <!-- Value -->
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold text-muted small mb-1">
                                            Value
                                        </label>
                                        <input 
                                            type="text" 
                                            name="data[{{ $loop->index }}][value]" 
                                            class="form-control" 
                                            value="{{ $value }}"
                                        >
                                    </div>

                                    <!-- Action -->
                                    <div class="col-md-2 text-end">
                                        <label class="d-block invisible">Action</label>
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-danger btn-sm remove-config p-2"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0A.5.5 0 0 1 8.5 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2H5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1h2.5a1 1 0 0 1 1 1zM6 2a.5.5 0 0 0-.5.5V3h5v-.5A.5.5 0 0 0 10 2H6zm-2 2v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4H4z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
                
            </div>
        </form>
        @endforeach

        
        
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
        document.querySelectorAll('.provider-item, .provider-address').forEach(el => {
            el.classList.remove('active');
        });

        this.classList.add('active');

        document.querySelectorAll('.provider-form').forEach(form => {
            form.classList.add('d-none');
        });
        let target = document.querySelector('.provider-form[data-id="'+id+'"]');
        if (target) {
            target.classList.remove('d-none');
        }
    });
});


// nút kích hoạt 
const btn = document.getElementById('toggleActive');
const input = document.getElementById('active_value');

btn.addEventListener('click', function () {
    if (input.value == 0) {
        input.value = 1;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-danger');
        btn.innerText = 'Huỷ kích hoạt';
    } else {
        input.value = 0;
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-success');
        btn.innerText = 'Kích hoạt';
    }
});
</script>
@endsection