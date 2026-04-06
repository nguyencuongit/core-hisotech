@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')

<body class="bg-light">

<div class="container py-4">

    <div class="row g-4">
        <!-- LEFT -->
        <div class="col-lg-8">

          <!-- PROVIDER -->
          <div class="card p-3 mb-4">
            <h4 class="mb-3 fw-bold"> <i class="fas fa-shipping-fast me-2 text-primary"></i>
                Đơn vị giao hàng</h4>
            <p class="fw-bold text-primary">{{$information->provider}}</p>
          </div>

          <!-- SENDER -->
            <div class="card p-3 mb-4">
            <h4 class="mb-3 fw-bold text-primary">
                <i class="fas fa-user me-2"></i> Thông tin người gửi
            </h4>
            
            <div class="row g-3">

                <!-- NAME + PHONE -->
                <div class="col-12 border-bottom pb-2 mb-2">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted">Họ tên</label>
                            <div class="fw-bold text-primary">{{$information->sender_name}}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted">SĐT</label>
                            <div class="fw-bold text-primary">{{$information->sender_phone}}</div>
                        </div>
                    </div>
                </div>

                <!-- ADDRESS -->
                <div class="col-12 border-bottom pb-2 mb-2">
                    <label class="text-muted">Địa chỉ</label>
                    <div class="fw-bold text-primary">{{$information->sender_address}}</div>
                </div>

                <!-- LOCATION -->
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="text-muted">Tỉnh/Thành</label>
                            <div class="fw-bold text-primary">{{$information->sender_province}}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">Quận/Huyện</label>
                            <div class="fw-bold text-primary">{{$information->sender_district}}</div>
                        </div>
                    </div>
                </div>

            </div>
            </div>


            <!-- RECEIVER -->
            <div class="card p-3 mb-4">
            <h4 class="mb-3 fw-bold text-success">
                <i class="fas fa-user-check me-2"></i> Thông tin người nhận
            </h4>
            
            <div class="row g-3">

                <!-- NAME + PHONE -->
                <div class="col-12 border-bottom pb-2 mb-2">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted">Họ tên</label>
                            <div class="fw-bold text-success">{{$information->receiver_name}}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted">SĐT</label>
                            <div class="fw-bold text-success">{{$information->receiver_phone}}</div>
                        </div>
                    </div>
                </div>

                <!-- ADDRESS -->
                <div class="col-12 border-bottom pb-2 mb-2">
                    <label class="text-muted">Địa chỉ</label>
                    <div class="fw-bold text-success">{{$information->receiver_address}}</div>
                </div>

                <!-- LOCATION -->
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="text-muted">Tỉnh/Thành</label>
                            <div class="fw-bold text-success">{{$information->receiver_province}}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">Quận/Huyện</label>
                            <div class="fw-bold text-success">{{$information->receiver_district}}</div>
                        </div>
                    </div>
                </div>

            </div>
            </div>

                    <!-- SIZE -->
                    <div class="card p-3 mb-4">
            <h4 class="mb-3 fw-bold">
            <i class="fas fa-box me-2"></i> Thông tin kích thước
            </h4>
            <div class="row text-center">

                <div class="col border-end">
                    <div class="text-muted">Weight</div>
                    <div class="fw-bold">{{$information->weight}}</div>
                </div>

                <div class="col border-end">
                    <div class="text-muted">Length</div>
                    <div class="fw-bold">{{$information->length}}</div>
                </div>

                <div class="col border-end">
                    <div class="text-muted">Width</div>
                    <div class="fw-bold">{{$information->width}}</div>
                </div>

                <div class="col">
                    <div class="text-muted">Height</div>
                    <div class="fw-bold">{{$information->height}}</div>
                </div>

            </div>
            </div>

          <!-- PRODUCTS -->
          <div class="card p-3 mb-4">
            <h4 class="mb-3 mb-3 fw-bold"> <i class="fas fa-list me-2"></i> Danh sách sản phẩm</h4>

            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($information->list_items as $items)
                    <tr>
                        <td>1</td>
                        <td>{{$items['name']}}</td>
                        <td>{{$items['qty']}}</td>
                        <td>{{ number_format($items['price'], 0, ',', '.') }} VND</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
          </div>

        </div>

        <!-- RIGHT -->
        <div class="col-lg-4">
        <form action="{{route('logistics.shipping.order.destroy')}}" method="POST">
          <div class="card p-3 mb-3">
            <button class="btn btn-danger w-100 fw-bold">
                <i class="fas fa-ban me-2"></i>
                Huỷ đơn
            </button>
          </div>
          <input type="text" name="code" value="{{$items['code']}}" >
        </form>
        <div class="card p-3 mb-3">
            <label class="text-muted">CODE</label>

            <div class="fw-bold d-flex align-items-center gap-2">
                <span id="code-value" data-code="{{ $information->code }}">
                    *******
                </span>

                <i class="fas fa-eye cursor-pointer" id="toggle-code"></i>
            </div>
        </div>

          <div class="card p-3 mb-3">
            <label class="text-muted">
                
                <i class="fas fa-money-bill-wave me-2"></i>COD amount</label>
            <div class="fw-bold">500,000 VND</div>
          </div>

          <div class="card p-3 mb-3">
            <label class="text-muted"><i class="fas fa-hand-holding-usd me-2"></i>  Phí ship</label>
            <div class="fw-bold text-primary fs-4">
                35,000 VND
            </div>
          </div>

        </div>
    </div>

</div>

</body>
@endsection
@push('footer')
<style>
    .container {
        font-size: calc(1rem + 2px);
    }

    label {
        font-size: calc(0.875rem + 2px);
    }

    h4 {
        font-size: calc(1.5rem + 2px);
    }

    h5 {
        font-size: calc(1.25rem + 2px);
    }

    .fw-bold {
        font-size: calc(1rem + 2px);
    }
</style>
<script>
    const toggleBtn = document.getElementById('toggle-code');
    const codeEl = document.getElementById('code-value');

    let isVisible = false;

    toggleBtn.addEventListener('click', function () {
        const realCode = codeEl.dataset.code;

        if (isVisible) {
            codeEl.innerText = '*******';
            toggleBtn.classList.remove('fa-eye-slash');
            toggleBtn.classList.add('fa-eye');
        } else {
            codeEl.innerText = realCode;
            toggleBtn.classList.remove('fa-eye');
            toggleBtn.classList.add('fa-eye-slash');
        }

        isVisible = !isVisible;
    });
</script>
@endpush