@php
$status = setting('viettel_post_status', 0);
$username = setting('viettel_post_username', '');
$password = setting('viettel_post_password', '');
$partnerCode = setting('viettel_post_partner_code', '');
$useStoreAddress = setting('viettel_post_use_store_address', 0);
$senderProvinceId = setting('viettel_post_sender_province_id', '');
$senderDistrictId = setting('viettel_post_sender_district_id', '');
@endphp

<x-core::card class="mt-4">
    <x-core::table :striped="false" :hover="false">
        <x-core::table.body>
            <x-core::table.body.cell class="border-end" style="width: 5%">
                <x-core::icon name="ti ti-truck-delivery" />
            </x-core::table.body.cell>
            <x-core::table.body.cell style="width: 20%">
                <strong class="text-danger">Viettel Post</strong>
            </x-core::table.body.cell>
            <x-core::table.body.cell>
                <a href="https://viettelpost.vn" target="_blank" class="fw-semibold">Viettel Post</a>
                <p class="mb-0">{{ trans('plugins/viettel-post::viettel-post.shipping_method_description') }}</p>
            </x-core::table.body.cell>
            <x-core::table.body.row class="bg-white">
                <x-core::table.body.cell colspan="3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div @class(['payment-name-label-group', 'd-none'=> ! $status])>
                                <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                                <label class="ws-nm inline-display method-name-label">Viettel Post</label>
                            </div>
                        </div>

                        <x-core::button data-bs-toggle="collapse" href="#collapse-shipping-method-viettel-post"
                            aria-expanded="false" aria-controls="collapse-shipping-method-viettel-post">
                            @if ($status)
                            {{ trans('core/base::layouts.settings') }}
                            @else
                            {{ trans('core/base::forms.edit') }}
                            @endif
                        </x-core::button>
                    </div>
                </x-core::table.body.cell>
            </x-core::table.body.row>
            <x-core::table.body.row class="collapse" id="collapse-shipping-method-viettel-post">
                <x-core::table.body.cell class="border-left" colspan="3">
                    <x-core::form :url="route('viettel-post.settings.save')">
                        <div class="row">
                            <div class="col-sm-6">
                                <x-core::alert type="warning">
                                    <x-slot:title>
                                        {{ trans('plugins/viettel-post::viettel-post.important_notice') }}
                                    </x-slot:title>

                                    <ul class="ps-3">
                                        <li style="list-style-type: circle;">
                                            <span>{{ trans('plugins/viettel-post::viettel-post.notice_api_address')
                                                }}</span>
                                        </li>
                                        <li style="list-style-type: circle;">
                                            <span>{{ trans('plugins/viettel-post::viettel-post.notice_api_register')
                                                }}</span>
                                        </li>
                                        <li style="list-style-type: circle;">
                                            <span>{{ trans('plugins/viettel-post::viettel-post.notice_credentials')
                                                }}</span>
                                        </li>
                                    </ul>
                                </x-core::alert>

                                <x-core::form.label>
                                    {{ trans('plugins/viettel-post::viettel-post.configuration_guide') }}
                                </x-core::form.label>

                                <div>
                                    <p>{{ trans('plugins/viettel-post::viettel-post.config_steps') }}</p>

                                    <ol>
                                        <li>
                                            <p>
                                                <a href="https://partner.viettelpost.vn" target="_blank">
                                                    {{ trans('plugins/viettel-post::viettel-post.step_register') }}
                                                </a>
                                            </p>
                                        </li>
                                        <li>
                                            <p>{{ trans('plugins/viettel-post::viettel-post.step_enter_credentials') }}
                                            </p>
                                        </li>
                                        <li>
                                            <p>{{ trans('plugins/viettel-post::viettel-post.step_load_address') }}</p>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted">
                                    {{ trans('plugins/viettel-post::viettel-post.provide_credentials') }}
                                    <a href="https://viettelpost.vn" target="_blank">Viettel Post</a>:
                                </p>

                                <x-core::form.text-input name="viettel_post_username"
                                    :label="trans('plugins/viettel-post::viettel-post.username')"
                                    placeholder="email@example.com" :value="$username" />

                                <x-core::form.text-input type="password" name="viettel_post_password"
                                    :label="trans('plugins/viettel-post::viettel-post.password')"
                                    :placeholder="trans('plugins/viettel-post::viettel-post.password_placeholder')"
                                    :value="$password" />


                                <x-core::form-group>
                                    <x-core::form.toggle name="viettel_post_status" :checked="$status"
                                        :label="trans('plugins/viettel-post::viettel-post.enable')" />
                                </x-core::form-group>

                                <hr class="my-3">
                                <h5>{{ trans('plugins/viettel-post::viettel-post.sender_address') }}</h5>
                                <p class="text-muted small">{{
                                    trans('plugins/viettel-post::viettel-post.sender_address_note') }}</p>

                                <x-core::form-group>
                                    <x-core::form.toggle name="viettel_post_use_store_address"
                                        id="viettel_post_use_store_address" :checked="$useStoreAddress"
                                        :label="trans('plugins/viettel-post::viettel-post.use_store_address')" />
                                    <p class="text-muted small mt-1">
                                        {{ trans('plugins/viettel-post::viettel-post.use_store_address_on') }}<br>
                                        {{ trans('plugins/viettel-post::viettel-post.use_store_address_off') }}
                                    </p>
                                </x-core::form-group>

                                <div id="sender_address_wrapper" @if($useStoreAddress) style="display:none" @endif>
                                    <x-core::form-group>
                                        <label class="form-label">{{
                                            trans('plugins/viettel-post::viettel-post.sender_province') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="d-flex gap-2">
                                            <select name="viettel_post_sender_province_id"
                                                id="viettel_post_sender_province_id" class="form-select"
                                                data-saved-value="{{ $senderProvinceId }}">
                                                <option value="">{{
                                                    trans('plugins/viettel-post::viettel-post.select_province') }}
                                                </option>
                                            </select>
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                id="btn_load_provinces"
                                                :title="trans('plugins/viettel-post::viettel-post.load_from_api')">
                                                <x-core::icon name="ti ti-refresh" />
                                            </button>
                                        </div>
                                    </x-core::form-group>

                                    <x-core::form-group>
                                        <label class="form-label">{{
                                            trans('plugins/viettel-post::viettel-post.sender_district') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="viettel_post_sender_district_id"
                                            id="viettel_post_sender_district_id" class="form-select"
                                            data-saved-value="{{ $senderDistrictId }}">
                                            <option value="">{{
                                                trans('plugins/viettel-post::viettel-post.select_district') }}</option>
                                        </select>
                                    </x-core::form-group>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                    var useStoreToggle = document.getElementById('viettel_post_use_store_address');
                                    var senderWrapper = document.getElementById('sender_address_wrapper');
                                    if (useStoreToggle && senderWrapper) {
                                        useStoreToggle.addEventListener('change', function() {
                                            senderWrapper.style.display = this.checked ? 'none' : 'block';
                                        });
                                    }
                                    
                                    var provinceSelect = document.getElementById('viettel_post_sender_province_id');
                                    var districtSelect = document.getElementById('viettel_post_sender_district_id');
                                    var loadBtn = document.getElementById('btn_load_provinces');
                                    var savedProvinceId = provinceSelect.dataset.savedValue;
                                    var savedDistrictId = districtSelect.dataset.savedValue;
                                    
                                    function loadProvinces(callback) {
                                        provinceSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
                                        
                                        fetch('{{ route("viettel-post.get-provinces") }}')
                                            .then(response => response.json())
                                            .then(data => {
                                                provinceSelect.innerHTML = '<option value="">-- Chọn Tỉnh/Thành --</option>';
                                                data.forEach(function(province) {
                                                    var option = document.createElement('option');
                                                    option.value = province.id;
                                                    option.textContent = province.name;
                                                    if (province.id == savedProvinceId) {
                                                        option.selected = true;
                                                    }
                                                    provinceSelect.appendChild(option);
                                                });
                                                if (callback) callback();
                                            })
                                            .catch(function() {
                                                provinceSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
                                            });
                                    }
                                    
                                    function loadDistricts(provinceId, callback) {
                                        districtSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
                                        
                                        if (!provinceId) {
                                            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                                            return;
                                        }
                                        
                                        fetch('{{ route("viettel-post.get-districts") }}?province_id=' + provinceId)
                                            .then(response => response.json())
                                            .then(data => {
                                                districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                                                data.forEach(function(district) {
                                                    var option = document.createElement('option');
                                                    option.value = district.id;
                                                    option.textContent = district.name;
                                                    if (district.id == savedDistrictId) {
                                                        option.selected = true;
                                                    }
                                                    districtSelect.appendChild(option);
                                                });
                                                if (callback) callback();
                                            })
                                            .catch(function() {
                                                districtSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
                                            });
                                    }
                                    
                                    if (loadBtn) {
                                        loadBtn.addEventListener('click', function() {
                                            loadProvinces(function() {
                                                if (savedProvinceId) {
                                                    loadDistricts(savedProvinceId);
                                                }
                                            });
                                        });
                                    }
                                    
                                    if (provinceSelect) {
                                        provinceSelect.addEventListener('change', function() {
                                            savedDistrictId = ''; // Reset when province changes
                                            loadDistricts(this.value);
                                        });
                                    }
                                    
                                    if (savedProvinceId) {
                                        loadProvinces(function() {
                                            loadDistricts(savedProvinceId);
                                        });
                                    }
                                });
                                </script>

                                <div class="text-end">
                                    <x-core::button type="submit" color="primary">
                                        {{ trans('core/base::forms.update') }}
                                    </x-core::button>
                                </div>
                            </div>
                        </div>
                    </x-core::form>
                </x-core::table.body.cell>
            </x-core::table.body.row>
        </x-core::table.body>
    </x-core::table>
</x-core::card>