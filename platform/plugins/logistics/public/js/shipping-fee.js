let isSubmitted = false;

window.shippingFeeHelper = {
    cache: {},

    async shippingFee(payload) {
        const flat = flattenData(payload);
        const key = JSON.stringify(flat);

        if (this.cache[key]) {
            return this.cache[key];
        }

        const query = new URLSearchParams(flat).toString();

        let res = await fetch(`/ajax/shipping-fee?${query}`);
        let result = await res.json();

        this.cache[key] = result;

        return result;
    },
};

document.addEventListener('click', async function (e) {
    if (e.target.classList.contains('shipping-fee')) {
        await handleShippingClick(e.target);
    }
});

function flattenData(data, prefix = '') {
    let result = {};

    for (let key in data) {
        let value = data[key];
        let newKey = prefix ? `${prefix}_${key}` : key;

        if (typeof value === 'object' && value !== null) {
            Object.assign(result, flattenData(value, newKey));
        } else {
            result[newKey] = value;
        }
    }

    return result;
}

async function handleShippingClick(btn) {
    isSubmitted = true;

    let data = getShippingData();

    if (!validateShippingForm()) {
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Đang tính...';

    let res = await shippingFeeHelper.shippingFee(data);

    document.getElementById('shipping-fee-value').innerText = formatVND(res.fee);

    btn.disabled = false;
    btn.innerText = 'Tính lại';
}

function getShippingData() {
    const get = (selector) => document.querySelector(selector)?.value || '';

    return {
        provider: get('[name="provider"]'),

        from: {
            province_id: get('[name="from_province"]'),
            district_id: get('[name="from_district"]'),
        },

        to: {
            province_id: get('[name="to_province"]'),
            district_id: get('[name="to_district"]'),
        },

        size: {
            weight: get('[name="weight"]'),
            length: get('[name="length"]'),
            width: get('[name="width"]'),
            height: get('[name="height"]'),
        }
    };
}

function formatVND(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}



// check nút tính phí ship 
function validateShippingForm() {
    let isValid = true;

    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        el.removeAttribute('title');
    });

    const requiredFields = [
        '[name="from_province"]',
        '[name="from_district"]',

        '[name="to_province"]',
        '[name="to_district"]',

        '[name="weight"]',
        '[name="length"]',
        '[name="width"]',
        '[name="height"]',
    ];

    requiredFields.forEach(selector => {
        let el = document.querySelector(selector);

        if (!el || !el.value) {
            if (el && isSubmitted) {
                el.classList.add('is-invalid');
                el.title = 'Vui lòng nhập thông tin';
            }
            isValid = false;
        }
    });

    return isValid;
}

document.addEventListener('input', function (e) {
    let el = e.target;

    if (!isSubmitted) return;

    if (el.classList.contains('is-invalid')) {
        if (el.value) {
            el.classList.remove('is-invalid');
            el.removeAttribute('title');
        }
    }
});