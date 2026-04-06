window.LocationHelper = {
    cache: {},
    async getDistricts(provinceId) {
        if (this.cache[provinceId]) {
            return this.cache[provinceId];
        }

        let res = await fetch(`/ajax/districts?province_id=${provinceId}`);
        let data = await res.json();

        this.cache[provinceId] = data;

        return data;
    },
    async getWards(districtId, code = '') {
        let cacheKey = `ward_${districtId}_${code}`;

        if (this.cache[cacheKey]) {
            return this.cache[cacheKey];
        }

        let params = new URLSearchParams({
            district_id: districtId,
            code: code
        });

        let res = await fetch(`/ajax/ward?${params.toString()}`);
        let data = await res.json();

        this.cache[cacheKey] = data;

        return data;
    },
    renderOptions(select, data) {
        select.innerHTML = '<option value="">-- Chọn --</option>';

        Object.entries(data).forEach(([id, name]) => {
            let option = document.createElement('option');
            option.value = id;
            option.textContent = name;
            select.appendChild(option);
        });
    }
};


// 🔥 AUTO BIND GLOBAL (quan trọng)
document.addEventListener('change', async function (e) {
    if (e.target.classList.contains('js-province')) {

        let provinceId = e.target.value;
        let districtSelect = document.querySelector(e.target.dataset.target);

        if (!districtSelect) return;

        let districts = await LocationHelper.getDistricts(provinceId);

        LocationHelper.renderOptions(districtSelect, districts);

        districtSelect.value = '';

        // 🔥 reset ward
        let wardSelect = document.querySelector(districtSelect.dataset.target);
        if (wardSelect) {
            wardSelect.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
        }
    }
});

document.addEventListener('change', async function (e) {
    if (e.target.classList.contains('js-district')) {

        let districtId = e.target.value;
        let target = document.querySelector(e.target.dataset.target);
        if (!target) return;

        let provider = document.querySelector('.js-provider')?.value || '';

        let wards = await LocationHelper.getWards(districtId, provider);

        LocationHelper.renderOptions(target, wards);
    }
});

// 🔥 AUTO LOAD KHI EDIT (cái bạn đang thiếu)
document.addEventListener('DOMContentLoaded', async function () {

    let province = document.querySelector('.js-province');
    if (!province) return;

    let provinceId = province.dataset.selected;
    if (!provinceId) return;

    let districtSelect = document.querySelector(province.dataset.target);
    if (!districtSelect) return;

    // 👉 load district
    let districts = await LocationHelper.getDistricts(provinceId);
    LocationHelper.renderOptions(districtSelect, districts);

    let selectedDistrict = districtSelect.dataset.district;

    if (selectedDistrict) {
        districtSelect.value = selectedDistrict;

        // 👉 load ward tiếp
        let wardSelect = document.querySelector(districtSelect.dataset.target);

        if (wardSelect) {
            let wards = await LocationHelper.getWards(selectedDistrict);
            LocationHelper.renderOptions(wardSelect, wards);

            let selectedWard = wardSelect.dataset.ward;

            if (selectedWard) {
                wardSelect.value = selectedWard;
            }
        }
    }
});