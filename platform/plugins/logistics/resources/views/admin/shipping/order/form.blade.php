@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<body class="bg-light">

<div class="container py-4">
  <form action="{{route('logistics.shipping.order.store')}}" method="POST">
    <input type="hidden" name="order_id" id="order-key">
    @csrf
    <div class="row g-4">
        <!-- LEFT FORM -->
        <div class="col-lg-8">

          <div class="card p-3 mb-4">
            <h3 class="mb-3">Đơn vị giao hàng</h3>
            <select class="form-select js-provider" name="provider">
                    <option value="">-- Chọn đơn vị vận chuyển --</option>
                    @foreach ($shippingProviders as $value)
                        <option value="{{ $value['code'] }}"  {{ $value['code'] == $shippingUnit ? 'selected' : '' }}>
                            {{ $value['name'] }}
                        </option>
                    @endforeach

            </select>
            
          </div>

          <div id="shipping-info" style="{{ $shippingUnit ? 'display:block;' : 'display:none;' }}">
            <div class="card p-3 mb-4">
              <h3 class="mb-3">Thông tin người gửi</h3>
              
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Họ tên *</label>
                  <input type="text" class="form-control" name='from_name' value="{{$inf_from->name}}">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Số điện thoại *</label>
                  <input type="text" class="form-control" name='from_phone' value="{{$inf_from->phone}}">
                </div>

                <div class="col-12">
                  <label class="form-label">Địa chỉ *</label>
                  <input type="text" class="form-control" name='from_address' value="{{$inf_from->address}}">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Tỉnh/Thành</label>
                  <select class="form-select js-province " name="from_province" data-target="#district" data-selected="{{ $inf_from->state_id }}" >
                      <option value="">-- Chọn tỉnh/thành --</option>
                      @foreach ($states as $state)
                          <option value="{{ $state->id }}" {{ $state->id == $inf_from->state_id ? 'selected' : '' }} >
                              {{ $state->name }}
                          </option>
                      @endforeach

                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label ">Phường/Xã</label>
                  <select class="form-select js-district" id="district" data-target="#ward" name='from_district' data-district="{{$inf_from->city_id}}">
                    <option value="">-- Chọn Phường/Xã --</option>
                  </select>
                </div>
                
              </div>
            </div>

            <div class="card p-3 mb-4">
              <h3 class="mb-3">Thông tin người nhận</h3>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Họ tên *</label>
                  <input type="text" class="form-control" name='to_name' value="{{$inf_to->name}}">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Số điện thoại *</label>
                  <input type="text" class="form-control" name='to_phone' value="{{$inf_to->phone}}">
                </div>

                <div class="col-12">
                  <label class="form-label">Địa chỉ *</label>
                  <input type="text" class="form-control" name='to_address' value="{{$inf_to->address}}">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Tỉnh/Thành</label>
                  <select class="form-select js-province" data-target="#district1" name="to_province" data-selected="{{ $inf_to->state_id }}" value="{{$inf_to->state_id}}">
                      <option value="">-- Chọn tỉnh/thành --</option>
                      @foreach ($states as $state)
                          <option value="{{ $state->id }}" {{ $state->id == $inf_to->state_id ? 'selected' : '' }}>
                              {{ $state->name }}
                          </option>
                      @endforeach
                  </select>
              </div>

              <div class="col-md-4">
                  <label class="form-label">Phường/Xã</label>
                  <select class="form-select js-district" id="district1" name='to_district' data-target="#ward1" value="{{$inf_to->city_id}}" data-district="{{$inf_to->city_id}}">
                    <option value="">-- Chọn Phường/Xã --</option>
                  </select>
              </div>

                

              </div>
            </div>
          
            <div class="card p-3 mb-4">
              <h3 class="mb-3">Thông tin Kích thước</h3>

              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label">Weight</label>
                  <input type="text" class="form-control" name='weight'>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Length</label>
                  <input type="text" class="form-control" name='length'>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Width</label>
                  <input type="text" class="form-control" name='width'>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Height</label>
                  <input type="text" class="form-control" name='height'>
                </div>
              </div>
            </div>

            <div class="card p-3 mb-4">
              <h3 class="mb-3">Danh sách sản phẩm</h3>

              <div class="table-responsive">
                  <table class="table table-bordered align-middle text-center">
                      <thead class="table-light">
                          <tr>
                              <th>ID</th>
                              <th>Sản phẩm</th>
                              <th>Số lượng</th>
                              <th>Giá</th>
                              <th>Height</th>
                              <th>Length</th>
                              <th>Width</th>
                              <th>Weight</th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach ($products as $index => $item)
                              <tr>
                                  <td>{{ $index + 1 }}</td>
                                  <td class="d-flex align-items-center gap-2">
                                      <!-- <img src="{{ asset($item['image']) }}" 
                                          alt="ảnh" 
                                          width="50" height="50"
                                          style="object-fit: cover; border-radius: 6px;"> -->
                                      <span>{{ $item['name'] }}</span>
                                      <input type="hidden" name="products[{{ $index }}][name]" value="{{ $item['name'] }}">
                                  </td>

                                  <td>
                                    {{ $item['qty'] }}
                                    <input type="hidden" name="products[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                                  </td>

                                  <td>
                                    {{ number_format($item['price'], 0, ',', '.') }} VND
                                    <input type="hidden" name="products[{{ $index }}][price]" value="{{ $item['price'] }}">
                                  </td>

                                  <td>
                                    {{ $item['height'] }}
                                    <input type="hidden" name="products[{{ $index }}][height]" value="{{ $item['height'] }}">
                                  </td>
                                  <td>
                                    {{ $item['length'] }}
                                  <input type="hidden" name="products[{{ $index }}][length]" value="{{ $item['length'] }}">
                                  </td>
                                  <td>
                                    {{ $item['width'] }}
                                  <input type="hidden" name="products[{{ $index }}][width]" value="{{ $item['width'] }}">
                                  </td>
                                  <td>
                                    {{ $item['weight'] }}
                                  <input type="hidden" name="products[{{ $index }}][weight]" value="{{ $item['weight'] }}">
                                  </td>
                              </tr>
                          @endforeach
                      </tbody>
                  </table>
              </div>
              
            </div>
          </div>
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="col-lg-4">

          <div class="card p-3 mb-3">
            <div class="d-grid gap-2">
              <button class="btn btn-primary">Tạo đơn vận chuyển</button>
            </div>
          </div>

          <div class="card p-3 mb-3">
            <label class="form-label">COD amount</label>
            <input type="number" class="form-control" name="cod_amount" value="0">
          </div>

          <div class="card p-3 mb-3">
            <label class="form-label mb-2">Phí ship tạm tính</label>

            <div class="d-flex justify-content-between align-items-center">
              <div id="shipping-fee-value" class="fw-bold text-primary fs-4">
                0 VND
              </div>

              <p class="btn btn-sm btn-outline-primary shipping-fee">
                Tính lại
              </p>
            </div>
          </div>
        </div>
    </div>
  </form>
</div>
</body>
<script>
  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('js-provider')) {

        let value = e.target.value;
        let box = document.getElementById('shipping-info');

        if (value) {
            box.style.display = 'block';
            box.style.opacity = 0;
            setTimeout(() => box.style.opacity = 1, 10);
        } else {
            box.style.opacity = 0;
            setTimeout(() => box.style.display = 'none', 200);
        }
    }
});

const params = new URLSearchParams(window.location.search);
const key = Array.from(params.keys())[0];
document.getElementById('order-key').value = key;
</script>
@endsection

@push('footer')

@endpush