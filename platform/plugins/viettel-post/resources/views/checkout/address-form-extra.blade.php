@php
$sessionCheckoutData = $sessionCheckoutData ?? [];
$wardValue = old('address.ward', Arr::get($sessionCheckoutData, 'ward', ''));
@endphp

<input type="hidden" id="address_ward_actual" value="{{ $wardValue }}">

<div class="form-group mb-3 viettelpost-ward-field d-none" id="viettelpost_ward_wrapper">
    <div class="select--arrow form-input-wrapper">
        <select class="form-control" id="address_ward" name="address[ward]" autocomplete="ward"
            data-value="{{ $wardValue }}">
            <option value="">{{ __('Chọn Phường/Xã...') }}</option>
        </select>

        <x-core::icon name="ti ti-chevron-down" />
        <label for="address_ward">{{ __('Phường/Xã') }}</label>
    </div>
</div>