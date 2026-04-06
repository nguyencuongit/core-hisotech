<script>
    window.ViettelPostStoreData = {
        ward: '{{ $store->ward ?? '' }}'
    };
</script>
@if ($store && $store->id)
<div class="viettelpost-inventory-section"
    style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
    <h5 style="margin-bottom: 20px; color: #495057; font-weight: 600;">
        <i class="fas fa-warehouse"></i> {{ trans('plugins/viettel-post::viettel-post.inventory_management') }}
    </h5>

    @if ($store->viettelpost_groupaddress_id)
    <div class="alert alert-success" style="margin-bottom: 15px;">
        <strong><i class="fas fa-check-circle"></i> {{ trans('plugins/viettel-post::viettel-post.inventory_linked')
            }}</strong>
        {{ $store->viettelpost_inventory_name }}
        <br>
        <small style="color: #155724;">GROUPADDRESS_ID: {{ $store->viettelpost_groupaddress_id }}</small>
    </div>
    <button type="button" class="btn btn-sm btn-warning" id="btn-change-inventory">
        <i class="fas fa-exchange-alt"></i> {{ trans('plugins/viettel-post::viettel-post.change_inventory') }}
    </button>
    @else
    <div class="alert alert-warning" style="margin-bottom: 15px;">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>{{ trans('plugins/viettel-post::viettel-post.no_inventory') }}</strong>
        <br>
        <small>{{ trans('plugins/viettel-post::viettel-post.no_inventory_note') }}</small>
    </div>
    <button type="button" class="btn btn-primary btn-sm" id="btn-register-inventory">
        <i class="fas fa-plus-circle"></i> {{ trans('plugins/viettel-post::viettel-post.register_new_inventory') }}
    </button>
    @endif
</div>

<div class="modal fade" id="register-inventory-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('plugins/viettel-post::viettel-post.register_inventory_modal') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="register-inventory-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/viettel-post::viettel-post.inventory_name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $store->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/viettel-post::viettel-post.inventory_phone') }}
                            <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ $store->phone }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/viettel-post::viettel-post.inventory_address') }}
                            <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" value="{{ $store->address }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/viettel-post::viettel-post.sender_province') }}
                            <span class="text-danger">*</span></label>
                        <select name="inv_province" id="inv_province" class="form-control" required>
                            <option value="">{{ trans('plugins/viettel-post::viettel-post.loading') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/viettel-post::viettel-post.sender_district') }}
                            <span class="text-danger">*</span></label>
                        <select name="inv_district" id="inv_district" class="form-control" required>
                            <option value="">{{ trans('plugins/viettel-post::viettel-post.select_province_first') }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/viettel-post::viettel-post.sender_ward') }} <span
                                class="text-danger">*</span></label>
                        <select name="inv_ward" id="inv_ward" class="form-control" required>
                            <option value="">{{ trans('plugins/viettel-post::viettel-post.select_district_first') }}
                            </option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{
                    trans('plugins/viettel-post::viettel-post.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-register">
                    <i class="fas fa-save"></i> {{ trans('plugins/viettel-post::viettel-post.register') }}
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
            const storeId = {{ $store->id }};
            const apiBase = '/api/viettel-post/address';

            function loadProvinces() {
                const $province = $('#inv_province');
                $province.html('<option value="">Đang tải...</option>');
                
                $.get(apiBase + '/provinces', function(data) {
                    $province.html('<option value="">Chọn Tỉnh/Thành phố...</option>');
                    data.forEach(function(p) {
                        $province.append(`<option value="${p.id}">${p.name}</option>`);
                    });
                });
            }

            $('#inv_province').on('change', function() {
                const pid = $(this).val();
                const $district = $('#inv_district');
                const $ward = $('#inv_ward');
                
                $district.html('<option value="">Đang tải...</option>');
                $ward.html('<option value="">Chọn Quận trước...</option>');
                
                if (pid) {
                    $.get(apiBase + '/districts/' + pid, function(data) {
                        $district.html('<option value="">Chọn Quận/Huyện...</option>');
                        data.forEach(function(d) {
                            $district.append(`<option value="${d.id}">${d.name}</option>`);
                        });
                    });
                }
            });

            $('#inv_district').on('change', function() {
                const did = $(this).val();
                const $ward = $('#inv_ward');
                
                $ward.html('<option value="">Đang tải...</option>');
                
                if (did) {
                    $.get(apiBase + '/wards/' + did, function(data) {
                        $ward.html('<option value="">Chọn Phường/Xã...</option>');
                        data.forEach(function(w) {
                            $ward.append(`<option value="${w.id}">${w.name}</option>`);
                        });
                    });
                }
            });

            $('#btn-register-inventory, #btn-change-inventory').click(function() {
                loadProvinces();
                $('#register-inventory-modal').modal('show');
            });

            $('#btn-confirm-register').click(function() {
                const $btn = $(this);

                const name = $('input[name="name"]', '#register-inventory-form').val();
                const phone = $('input[name="phone"]', '#register-inventory-form').val();
                const address = $('input[name="address"]', '#register-inventory-form').val();

                const provinceId = $('#inv_province').val();
                const districtId = $('#inv_district').val();
                const wardId = $('#inv_ward').val();

                console.log('Inventory Register - Address values:', { provinceId, districtId, wardId });

                if (!name || !phone || !address) {
                    Botble.showError('Vui lòng điền đầy đủ Tên, Số điện thoại và Địa chỉ');
                    return;
                }

                if (!provinceId || !districtId || !wardId) {
                    Botble.showError('Vui lòng chọn đầy đủ Tỉnh/Quận/Phường');
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

                $.ajax({
                    url: `/admin/viettelpost/inventory/register/${storeId}`,
                    method: 'POST',
                    data: {
                        name: name,
                        phone: phone,
                        address: address,
                        province_id: provinceId,
                        district_id: districtId,
                        ward_id: wardId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (!response.error) {
                            Botble.showSuccess(response.message);
                            $('#register-inventory-modal').modal('hide');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Botble.showError(response.message);
                        }
                    },
                    error: function(xhr) {
                        Botble.showError(xhr.responseJSON?.message || 'Có lỗi xảy ra khi đăng ký kho hàng');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Đăng ký');
                    }
                });
            });
        });
</script>
@else
<div class="alert alert-info" style="margin-top: 30px;">
    <i class="fas fa-info-circle"></i>
    <strong>ViettelPost - Quản Lý Kho Hàng</strong>
    <br>
    <small>Sau khi tạo Store, bạn có thể quay lại trang chỉnh sửa để đăng ký kho hàng ViettelPost cho cửa hàng
        này.</small>
</div>
@endif