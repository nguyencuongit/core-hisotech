@php
$address = $address ?? null;
@endphp

<input type="hidden" id="address_ward_actual" name="ward" value="{{ old('ward', $address ? $address->ward : '') }}">

<div class="form-group mb-3 viettelpost-ward-field d-none" id="viettelpost_ward_wrapper">
    <label for="address_ward" class="control-label required">{{ __('Phường/Xã') }}</label>
    <div class="ui-select-wrapper form-input-wrapper">
        <select class="form-control ui-select" id="address_ward">
            <option value="">{{ __('Chọn Phường/Xã...') }}</option>
        </select>
        <svg class="svg-next-icon svg-next-icon-size-16">
            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
        </svg>
    </div>
</div>