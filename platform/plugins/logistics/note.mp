form auto lấy quận huyện ở mọi nơi global
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
                        <label class="form-label">Quận / Huyện</label>
                        <select class="form-control" id="district" name="city_id" data-district="{{$address_admin['logistics_admin_city_id'] ?? ''}}">
                            <option value="">Chọn quận</option>
                        </select>
                    </div>


-> chú ý: tỉnh/thành bắt buộc phải có class js-province và data-target="#name". quận huyện: bắt buộc phải có id="name".