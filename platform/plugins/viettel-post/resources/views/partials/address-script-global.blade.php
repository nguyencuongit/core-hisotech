<script>
    (function($) {
       
        const VTAddressHandler = {
            config: {
                selectors: {
                    country: '[name*="country"]',
                    state: '[name*="state"]',
                    city: '[name*="city"]',
                    ward: '[name*="ward"]'
                },
                routes: {
                    provinces: '{{ route('viettel-post.address.provinces') }}',
                    districts: '{{ route('viettel-post.address.districts', ['province_id' => '__ID__']) }}',
                    wards: '{{ route('viettel-post.address.wards', ['district_id' => '__ID__']) }}'
                }
            },

            init: function(container) {
                const self = this;
                

                const countryFields = container.querySelectorAll(self.config.selectors.country);

                countryFields.forEach(countryEl => {
                    if (!countryEl.hasAttribute('data-vt-processed')) {
                        self.processAddressSection(countryEl);
                    }
                });

                const stateFieldsWithDataType = container.querySelectorAll('[data-type="state"]:not([data-vt-processed])');

                stateFieldsWithDataType.forEach(stateEl => {

                    if (!stateEl.hasAttribute('data-vt-processed')) {
                        self.processAddressSectionFromState(stateEl);
                    }
                });
            },

            processAddressSection: function(countryEl) {
                const self = this;
                const nameAttr = countryEl.getAttribute('name');
                const container = countryEl.closest('form, .modal-body, .checkout-form, .address-form-wrapper, body') || document.body;
                
                let prefix = '';
                const match = nameAttr.match(/^([^\[]+)\[country\]/);
                if (match) {
                    prefix = match[1];
                }

                if (countryEl.value !== 'VN') {
                    countryEl.value = 'VN';
                }

                if (countryEl.tagName === 'SELECT' || (countryEl.tagName === 'INPUT' && countryEl.type !== 'hidden')) {
                    countryEl.name = 'unused_' + nameAttr;
                    countryEl.disabled = true;
                    
                    if (!container.querySelector(`input[type="hidden"][name="${nameAttr}"]`)) {
                        const hiddenCountry = document.createElement('input');
                        hiddenCountry.type = 'hidden';
                        hiddenCountry.name = nameAttr;
                        hiddenCountry.value = 'VN';
                        countryEl.parentNode.insertBefore(hiddenCountry, countryEl.nextSibling);
                    }
                }

                countryEl.setAttribute('data-vt-processed', 'true');
                
                const hideOldLabels = (prefix) => {
                    const searchTerms = ['state', 'city', 'province', 'district', 'town', 'bang', 'tỉnh', 'thành phố', 'quận', 'huyện'];
                    const labels = container.querySelectorAll('label');
                    labels.forEach(lbl => {
                        const text = lbl.textContent.toLowerCase();
                        const isMatch = searchTerms.some(term => text.includes(term));
                        if (isMatch && !lbl.hasAttribute('data-vt-label')) {
                            lbl.style.display = 'none';
                            lbl.classList.add('d-none');
                        }
                    });
                };
                hideOldLabels(prefix);

                const getSelector = (type) => {
                    return prefix ? `[name="${prefix}[${type}]"]` : `[name="${type}"]`;
                };

                const stateEl = container.querySelector(getSelector('state'));
                const cityEl = container.querySelector(getSelector('city'));
                
                if (!stateEl || !cityEl) return;
                
                const savedStateValue = stateEl.value;
                const savedCityValue = cityEl.value;

                const finalStateSelect = self.ensureSelect(stateEl, 'Tỉnh/Thành phố');
                const finalCitySelect = self.ensureSelect(cityEl, 'Quận/Huyện');
                
                let existingWardEl = container.querySelector(getSelector('ward'));
                let savedWardValue = existingWardEl ? existingWardEl.value : '';
                
                if (!savedWardValue && window.ViettelPostStoreData && window.ViettelPostStoreData.ward) {
                    savedWardValue = window.ViettelPostStoreData.ward;
                }
                
                let wardEl = existingWardEl;
                if (!wardEl) {
                    wardEl = self.injectWardField(finalCitySelect, prefix);
                }
                const finalWardSelect = self.ensureSelect(wardEl, 'Phường/Xã');
                
                if (savedWardValue) {
                    finalWardSelect.setAttribute('data-value', savedWardValue);
                    finalWardSelect.setAttribute('data-saved-value', savedWardValue);
                }
                
                const createOrGetHiddenId = (fieldName) => {
                    const hiddenName = prefix ? `${prefix}[${fieldName}]` : fieldName;
                    let hidden = container.querySelector(`input[name="${hiddenName}"]`);
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = hiddenName;
                        container.querySelector('form')?.appendChild(hidden) || container.appendChild(hidden);
                    }
                    return hidden;
                };
                
                const stateIdHidden = createOrGetHiddenId('state_id');
                const cityIdHidden = createOrGetHiddenId('city_id');
                const wardIdHidden = createOrGetHiddenId('ward_id');
                
                const updateHiddenId = (selectEl, hiddenInput) => {
                    const selectedOption = selectEl.options[selectEl.selectedIndex];
                    const id = selectedOption ? selectedOption.getAttribute('data-id') : '';
                    hiddenInput.value = id || '';
                };
                
                finalStateSelect.addEventListener('change', () => updateHiddenId(finalStateSelect, stateIdHidden));
                finalCitySelect.addEventListener('change', () => updateHiddenId(finalCitySelect, cityIdHidden));
                finalWardSelect.addEventListener('change', () => updateHiddenId(finalWardSelect, wardIdHidden));

                const toggleVisibility = (selectEl, show) => {
                    let wrapper = selectEl.closest('.form-group');
                    if (wrapper) {
                        if (show) wrapper.classList.remove('d-none');
                        else wrapper.classList.add('d-none');
                    }
                };

                if (!finalStateSelect.value) {
                    finalCitySelect.disabled = true;
                    finalWardSelect.disabled = true;
                    toggleVisibility(finalWardSelect, false);
                } else {
                    if (!finalCitySelect.value) {
                        finalWardSelect.disabled = true;
                        toggleVisibility(finalWardSelect, false);
                    } else {
                        toggleVisibility(finalWardSelect, true);
                    }
                }
                
                finalStateSelect.setAttribute('data-value', savedStateValue);
                finalCitySelect.setAttribute('data-value', savedCityValue);

                self.loadOptions(finalStateSelect, self.config.routes.provinces, 'Tỉnh/Thành phố');

                finalStateSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const id = selectedOption ? selectedOption.getAttribute('data-id') : null;
                    self.resetSelect(finalCitySelect, 'Quận/Huyện');
                    finalWardSelect.removeAttribute('data-saved-value');
                    self.resetSelect(finalWardSelect, 'Phường/Xã');
                    toggleVisibility(finalWardSelect, false);

                    if (id) {
                        self.loadOptions(finalCitySelect, self.config.routes.districts.replace('__ID__', id), 'Quận/Huyện');
                    }
                });

                finalCitySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const id = selectedOption ? selectedOption.getAttribute('data-id') : null;
                    const savedVal = finalWardSelect.getAttribute('data-saved-value');
                    
                    self.resetSelect(finalWardSelect, 'Phường/Xã');
                    
                    if (savedVal) {
                        finalWardSelect.setAttribute('data-value', savedVal);
                    }
                    
                    if (id) {
                        toggleVisibility(finalWardSelect, true);
                        self.loadOptions(finalWardSelect, self.config.routes.wards.replace('__ID__', id), 'Phường/Xã');
                    } else {
                        toggleVisibility(finalWardSelect, false);
                    }
                });

                if (prefix === 'address' || prefix === 'billing_address') {
                    const wardActual = container.querySelector(`input[name="${prefix}[ward]"][type="hidden"]`);
                    if (wardActual) {
                        finalWardSelect.addEventListener('change', function() {
                            wardActual.value = this.value;
                        });
                    }
                }
            },

           
            processAddressSectionFromState: function(stateEl) {
                const self = this;
                const container = stateEl.closest('form') || document.body;
                
                stateEl.setAttribute('data-vt-processed', 'true');
                
                const savedStateValue = stateEl.value;
                
                const cityEl = container.querySelector('[data-type="city"]');
                if (!cityEl) return;
                
                const savedCityValue = cityEl.value;
                cityEl.setAttribute('data-vt-processed', 'true');
                
                let existingWardEl = container.querySelector('[name="ward"]');
                let savedWardValue = '';
                
                if (existingWardEl) {
                    savedWardValue = existingWardEl.value;

                    existingWardEl.remove();
                }
                
                if (!savedWardValue && window.ViettelPostStoreData && window.ViettelPostStoreData.ward) {
                    savedWardValue = window.ViettelPostStoreData.ward;

                }
                

                
                const wardEl = self.injectWardField(cityEl, '');
                
                if (savedWardValue) {
                    wardEl.setAttribute('data-value', savedWardValue);
                    wardEl.setAttribute('data-saved-value', savedWardValue);
                }
                
                const createOrGetHiddenId = (fieldName) => {
                    let hidden = container.querySelector(`input[name="${fieldName}"]`);
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = fieldName;
                        container.querySelector('form')?.appendChild(hidden) || container.appendChild(hidden);
                    }
                    return hidden;
                };
                
                const stateIdHidden = createOrGetHiddenId('state_id');
                const cityIdHidden = createOrGetHiddenId('city_id');
                const wardIdHidden = createOrGetHiddenId('ward_id');
                
                const updateHiddenId = (selectEl, hiddenInput) => {
                    const selectedOption = selectEl.options[selectEl.selectedIndex];
                    const id = selectedOption ? selectedOption.getAttribute('data-id') : '';
                    hiddenInput.value = id || '';
                };
                
                stateEl.addEventListener('change', () => updateHiddenId(stateEl, stateIdHidden));
                cityEl.addEventListener('change', () => updateHiddenId(cityEl, cityIdHidden));
                wardEl.addEventListener('change', () => updateHiddenId(wardEl, wardIdHidden));
                
                stateEl.setAttribute('data-value', savedStateValue);
                cityEl.setAttribute('data-value', savedCityValue);
                
                self.loadOptions(stateEl, self.config.routes.provinces, 'Tỉnh/Thành phố');
                
                stateEl.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const id = selectedOption ? selectedOption.getAttribute('data-id') : null;
                    self.resetSelect(cityEl, 'Quận/Huyện');
                    
                    const wardSelect = container.querySelector('[name="ward"]');
                    if (wardSelect) {
                        wardSelect.removeAttribute('data-saved-value');
                        self.resetSelect(wardSelect, 'Phường/Xã');
                    }
                    
                    if (id) {
                        self.loadOptions(cityEl, self.config.routes.districts.replace('__ID__', id), 'Quận/Huyện');
                    }
                });
                
                cityEl.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const id = selectedOption ? selectedOption.getAttribute('data-id') : null;
                    const wardSelect = container.querySelector('[name="ward"]');
                    if (wardSelect) {
                        const savedVal = wardSelect.getAttribute('data-saved-value');
                        
                        self.resetSelect(wardSelect, 'Phường/Xã');
                        
                        if (savedVal) {
                            wardSelect.setAttribute('data-value', savedVal);
                        }
                        
                        if (id) {
                            const wardWrapper = wardSelect.closest('.form-group, .mb-3, [class*="col-"]');
                            if (wardWrapper) wardWrapper.classList.remove('d-none');
                            self.loadOptions(wardSelect, self.config.routes.wards.replace('__ID__', id), 'Phường/Xã');
                        }
                    }
                });
                
                const stateLabel = container.querySelector('label[for="' + stateEl.id + '"]') || 
                                   stateEl.closest('.form-group, .mb-3')?.querySelector('label');
                if (stateLabel) stateLabel.textContent = 'Tỉnh/Thành phố';
                
                const cityLabel = container.querySelector('label[for="' + cityEl.id + '"]') ||
                                  cityEl.closest('.form-group, .mb-3')?.querySelector('label');
                if (cityLabel) cityLabel.textContent = 'Quận/Huyện';
            },

            ensureSelect: function(el, labelText) {
                const self = this;
                
                if (el.tagName === 'INPUT' && el.type === 'hidden' && (el.name.includes('country'))) {
                    return el;
                }

                const originalValue = el.value;
                const name = el.name;
                const id = el.id || 'vt_' + name.replace(/[\[\]]/g, '_');
                const isAdmin = document.body.classList.contains('admin-sidebar');
                
                let originalLabel = el.form?.querySelector('label[for="' + el.id + '"]') || el.closest('.form-group')?.querySelector('label');
                
                if (el.tagName === 'SELECT') {
                    if (originalLabel) {
                        originalLabel.textContent = labelText;
                        originalLabel.style.display = 'block';
                    }
                    return el;
                }

                const newDiv = document.createElement('div');
                newDiv.className = 'form-group mb-3';
                
                if (!isAdmin) {
                    newDiv.innerHTML = `
                        <label class="form-label required" for="${id}" data-vt-label="true">${labelText}</label>
                        <div class="select--arrow form-input-wrapper">
                            <select class="form-control viettelpost-select" id="${id}" name="${name}" ${el.required ? 'required' : ''}>
                                <option value="">Chọn ${labelText}...</option>
                            </select>
                            <span class="select-icon"><i class="ti ti-chevron-down"></i></span>
                        </div>
                    `;
                } else {
                    newDiv.innerHTML = `
                        <label class="control-label required" for="${id}" data-vt-label="true">${labelText}</label>
                        <div class="ui-select-wrapper">
                            <select class="form-control ui-select" id="${id}" name="${name}" ${el.required ? 'required' : ''}>
                                <option value="">Chọn ${labelText}...</option>
                            </select>
                            <svg class="svg-next-icon svg-next-icon-size-16">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                            </svg>
                        </div>
                    `;
                }

                const containerToReplace = el.closest('.form-group') || el;
                containerToReplace.replaceWith(newDiv);
                
                const select = newDiv.querySelector('select');
                if (originalValue) {
                    select.setAttribute('data-value', originalValue);
                }
                return select;
            },

            injectWardField: function(citySelect, prefix) {
                const newDiv = document.createElement('div');
                newDiv.className = 'form-group mb-3 viettelpost-ward-field d-none';
                
                const wardName = prefix ? `${prefix}[ward]` : 'ward';
                const wardId = citySelect.id.replace('city', 'ward');
                const isAdmin = document.body.classList.contains('admin-sidebar');

                if (!isAdmin) {
                    newDiv.innerHTML = `
                        <label class="form-label required" for="${wardId}" data-vt-label="true">Phường/Xã</label>
                        <div class="select--arrow form-input-wrapper">
                            <select class="form-control" name="${wardName}" id="${wardId}">
                                <option value="">Chọn Phường/Xã...</option>
                            </select>
                            <span class="select-icon"><i class="ti ti-chevron-down"></i></span>
                        </div>
                    `;
                } else {
                    newDiv.innerHTML = `
                        <label class="control-label required" for="${wardId}" data-vt-label="true">Phường/Xã</label>
                        <div class="ui-select-wrapper">
                            <select class="form-control ui-select" name="${wardName}" id="${wardId}">
                                <option value="">Chọn Phường/Xã...</option>
                            </select>
                            <svg class="svg-next-icon svg-next-icon-size-16">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                            </svg>
                        </div>
                    `;
                }
                
                const cityWrapper = citySelect.closest('.form-group');
                cityWrapper.after(newDiv);
                return newDiv.querySelector('select');
            },

            loadOptions: function(selectEl, route, labelText) {
                selectEl.disabled = true;
                const currentVal = selectEl.getAttribute('data-value') || selectEl.value;
                
                selectEl.innerHTML = `<option value="">Đang tải ${labelText}...</option>`;

                fetch(route)
                    .then(res => res.json())
                    .then(data => {
                        selectEl.innerHTML = `<option value="">Chọn ${labelText}...</option>`;
                        data.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.name;  
                            opt.setAttribute('data-id', item.id);  
                            opt.textContent = item.name;
                            const itemNameLower = item.name.toLowerCase();
                            const currentValLower = currentVal ? currentVal.toString().toLowerCase() : '';
                            if (item.id == currentVal || itemNameLower === currentValLower) {
                                opt.selected = true;
                            }
                            selectEl.appendChild(opt);
                        });
                        selectEl.disabled = false;
                        
                        if (selectEl.value && !selectEl.hasAttribute('data-initial-load-done')) {
                            selectEl.setAttribute('data-initial-load-done', 'true');
                            selectEl.dispatchEvent(new Event('change'));
                        }
                    })
                    .catch(err => {
                        console.error('Viettel Post error:', err);
                        selectEl.innerHTML = `<option value="">Lỗi tải dữ liệu</option>`;
                    });
            },

            resetSelect: function(selectEl, labelText) {
                selectEl.innerHTML = `<option value="">Chọn ${labelText}...</option>`;
                selectEl.disabled = true;
                selectEl.removeAttribute('data-initial-load-done');
            }
        };

        const runDetection = () => {
            VTAddressHandler.init(document.body);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runDetection);
        } else {
            runDetection();
        }

        $(document).on('ajaxComplete shown.bs.modal', function(e) {
            runDetection();
        });

    })(window.jQuery);
</script>