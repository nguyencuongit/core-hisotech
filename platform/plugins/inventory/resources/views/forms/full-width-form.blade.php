@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @if ($showStart)
        {!! Form::open(Arr::except($formOptions, ['template'])) !!}
    @endif

    @php
        do_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, request(), $form->getModel());
    @endphp

    <div class="row">
        <div class="gap-3 col-md-12">
            @if ($showFields && $form->hasMainFields())
                <x-core::card class="mb-3">
                    <x-core::card.body>
                        <div class="{{ $form->getWrapperClass() }}">
                            {{ $form->getOpenWrapperFormColumns() }}

                            @foreach ($fields as $key => $field)
                                @if ($field->getName() == $form->getBreakFieldPoint())
                                    @break

                                @else
                                    @unset($fields[$key])
                                @endif

                                @continue(in_array($field->getName(), $exclude))

                                {!! $field->render() !!}
                                @if (defined('BASE_FILTER_SLUG_AREA') && $field->getName() == SlugHelper::getColumnNameToGenerateSlug($form->getModel()))
                                    {!! apply_filters(BASE_FILTER_SLUG_AREA, null, $form->getModel()) !!}
                                @endif
                            @endforeach

                            {{ $form->getCloseWrapperFormColumns() }}
                        </div>
                    </x-core::card.body>
                </x-core::card>
            @endif

            @foreach ($form->getMetaBoxes() as $key => $metaBox)
                {!! $form->getMetaBox($key) !!}
            @endforeach

            @php
                do_action(BASE_ACTION_META_BOXES, 'advanced', $form->getModel());
            @endphp
        </div>

        <div class="col-md-12 gap-3 d-flex flex-column-reverse flex-md-column mb-md-0 mb-5">
            {!! $form->getActionButtons() !!}

            @php
                do_action(BASE_ACTION_META_BOXES, 'top', $form->getModel());
            @endphp

            @foreach ($fields as $field)
                @if (!in_array($field->getName(), $exclude))
                    @if (in_array($field->getType(), ['hidden', \Botble\Base\Forms\Fields\HiddenField::class]))
                        {!! $field->render() !!}
                    @else
                        <x-core::card class="meta-boxes">
                            <x-core::card.header>
                                <x-core::card.title>
                                    {!! Form::customLabel($field->getName(), $field->getOption('label'), $field->getOption('label_attr')) !!}
                                </x-core::card.title>
                            </x-core::card.header>

                            @php
                                $bodyAttrs = Arr::get($field->getOptions(), 'card-body-attrs', []);

                                if (!Arr::has($bodyAttrs, 'class')) {
                                    $bodyAttrs['class'] = '';
                                }

                                $bodyAttrs['class'] .= ' card-body';
                            @endphp

                            <div {!! Html::attributes($bodyAttrs) !!}>
                                {!! $field->render([], in_array($field->getType(), ['radio', 'checkbox'])) !!}
                            </div>
                        </x-core::card>
                    @endif
                @endif
            @endforeach

            @php
                do_action(BASE_ACTION_META_BOXES, 'side', $form->getModel());
            @endphp
        </div>
    </div>

    @if ($showEnd)
        {!! Form::close() !!}
    @endif

    @yield('form_end')
@endsection

@if ($form->getValidatorClass())
    @if ($form->isUseInlineJs())
        {!! Assets::scriptToHtml('jquery') !!}
        {!! Assets::scriptToHtml('form-validation') !!}
        {!! $form->renderValidatorJs() !!}
    @else
        @push('footer')
            {!! $form->renderValidatorJs() !!}
        @endpush
    @endif
@endif


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const type = document.getElementById('document-type');
        const wrapper = document.getElementById('type-note-wrapper');

        if (! type || ! wrapper) {
            return;
        }

        function toggle() {
            if (type.value === 'manual') {
                wrapper.classList.remove('d-none');
            } else {
                wrapper.classList.add('d-none');
            }
        }

        type.addEventListener('change', toggle);
        toggle();
    });

    document.addEventListener('DOMContentLoaded', function () {
        const warehouseSelect = document.getElementById('warehouse-id');
        const requestedSelect = document.getElementById('requested-by-id');
        const requestedNameWrapper = document.getElementById('requested-by-name-wrapper');
        const requestedNameInput = document.getElementById('requested-by-name');

        if (! requestedSelect || ! requestedNameWrapper || ! requestedNameInput) {
            return;
        }

        const initialRequestedValue = String(requestedSelect.value ?? '');
        const initialRequestedLabel = requestedSelect.options[requestedSelect.selectedIndex]?.text || '';
        let manualRequestedName = initialRequestedValue === '0' ? requestedNameInput.value : '';

        function resetRequestedBy(selectedValue = '') {
            requestedSelect.innerHTML = '';

            const emptyOption = new Option('Chọn người yêu cầu', '');
            requestedSelect.appendChild(emptyOption);

            const otherOption = new Option('--- Khác (nhập tay) ---', '0');
            requestedSelect.appendChild(otherOption);

            requestedSelect.value = selectedValue;

        }

        function syncRequestedNameVisibility(preserveManualName = false) {
            if (requestedSelect.value === '0') {
                requestedNameWrapper.classList.remove('d-none');

                if (requestedNameInput.hasAttribute('readonly')) {
                    requestedNameInput.value = preserveManualName ? manualRequestedName : '';
                }

                requestedNameInput.removeAttribute('readonly');
                requestedNameInput.setAttribute('required', 'required');

                return;
            }

            if (requestedSelect.value) {
                requestedNameWrapper.classList.add('d-none');
                requestedNameInput.value = requestedSelect.options[requestedSelect.selectedIndex]?.text || '';
                requestedNameInput.setAttribute('readonly', 'readonly');
                requestedNameInput.removeAttribute('required');

                return;
            }

            requestedNameWrapper.classList.add('d-none');
            requestedNameInput.value = '';
            requestedNameInput.removeAttribute('readonly');
            requestedNameInput.removeAttribute('required');
        }

        async function loadStaffByWarehouse(warehouseId, selectedValue = '') {
            resetRequestedBy(selectedValue);

            if (! warehouseId) {
                syncRequestedNameVisibility(selectedValue === '0');

                return;
            }

            try {
                const response = await fetch(`/ajax/warehouses/${warehouseId}/staff`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json();

                if (! result.data) {
                    return;
                }

                let hasSelectedOption = selectedValue === '' || selectedValue === '0';

                Object.entries(result.data).forEach(([id, name]) => {
                    const isSelected = String(id) === selectedValue;

                    if (isSelected) {
                        hasSelectedOption = true;
                    }

                    requestedSelect.appendChild(new Option(name, id, false, isSelected));
                });

                if (selectedValue && selectedValue !== '0' && ! hasSelectedOption && initialRequestedLabel) {
                    requestedSelect.appendChild(new Option(initialRequestedLabel, selectedValue, false, true));
                }

                requestedSelect.value = selectedValue;
            } catch (e) {
                console.error(e);
            } finally {
                syncRequestedNameVisibility(selectedValue === '0');
            }
        }

        requestedNameInput.addEventListener('input', function () {
            if (requestedSelect.value === '0') {
                manualRequestedName = this.value;
            }
        });

        requestedSelect.addEventListener('change', function () {
            syncRequestedNameVisibility(!! manualRequestedName);
        });

        if (warehouseSelect) {
            warehouseSelect.addEventListener('change', function () {
                loadStaffByWarehouse(this.value);
            });

            if (warehouseSelect.value) {
                loadStaffByWarehouse(warehouseSelect.value, initialRequestedValue);

                return;
            }
        }

        syncRequestedNameVisibility(initialRequestedValue === '0');
    });



    //partner-type
    document.addEventListener('DOMContentLoaded', function () {
        const partnerType = document.getElementById('partner-type');
        const partnerId = document.getElementById('partner-id');

        if (! partnerType || ! partnerId) {
            return;
        }

        const initialPartnerId = String(partnerId.value ?? '');
        const initialPartnerLabel = partnerId.options[partnerId.selectedIndex]?.text || '';

        function resetPartner(selectedValue = '') {
            partnerId.innerHTML = '';
            partnerId.value = selectedValue;
            partnerId.appendChild(new Option('Chọn đối tượng', ''));
        }

        async function loadPartnerByType(type, selectedValue = '') {
            resetPartner(selectedValue);

            if (! type) {
                return;
            }

            try {
                const response = await fetch(`/ajax/partner-type/${type}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json();

                if (! result.data) {
                    return;
                }

                let hasSelectedOption = selectedValue === '';

                Object.entries(result.data).forEach(([id, name]) => {
                    const isSelected = String(id) === selectedValue;

                    if (isSelected) {
                        hasSelectedOption = true;
                    }

                    partnerId.appendChild(new Option(name, id, false, isSelected));
                });

                if (selectedValue && ! hasSelectedOption && initialPartnerLabel) {
                    partnerId.appendChild(new Option(initialPartnerLabel, selectedValue, false, true));
                }

                partnerId.value = selectedValue;
            } catch (e) {
                console.error('Load partner error:', e);
            }
        }

        partnerType.addEventListener('change', function () {
            loadPartnerByType(this.value);
        });

        if (partnerType.value) {
            loadPartnerByType(partnerType.value, initialPartnerId);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const provinceSelect = document.getElementById('province-id');
        const wardSelect = document.getElementById('ward-id');

        if (! provinceSelect || ! wardSelect) {
            return;
        }

        const initialWardId = String(wardSelect.value ?? '');
        const initialWardLabel = wardSelect.options[wardSelect.selectedIndex]?.text || '';

        function resetWard(selectedValue = '') {
            wardSelect.innerHTML = '';
            wardSelect.appendChild(new Option('Chon quan / huyen', '', false, selectedValue === ''));
            wardSelect.value = selectedValue;
        }

        async function loadWardsByProvince(provinceId, selectedValue = '') {
            resetWard(selectedValue);

            if (! provinceId) {
                return;
            }

            try {
                const response = await fetch(`/ajax/states/${provinceId}/cities`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json();

                if (! result.data) {
                    return;
                }

                let hasSelectedOption = selectedValue === '';

                Object.entries(result.data).forEach(([id, name]) => {
                    const isSelected = String(id) === selectedValue;

                    if (isSelected) {
                        hasSelectedOption = true;
                    }

                    wardSelect.appendChild(new Option(name, id, false, isSelected));
                });

                if (selectedValue && ! hasSelectedOption && initialWardLabel) {
                    wardSelect.appendChild(new Option(initialWardLabel, selectedValue, false, true));
                }

                wardSelect.value = selectedValue;
            } catch (e) {
                console.error('Load wards error:', e);
            }
        }

        provinceSelect.addEventListener('change', function () {
            loadWardsByProvince(this.value);
        });

        if (provinceSelect.value) {
            loadWardsByProvince(provinceSelect.value, initialWardId);
        }
    });
</script>

