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
        const select = document.getElementById('requested-by-id');
        const wrapper = document.getElementById('requested-by-name-wrapper');
        const input = document.getElementById('requested-by-name');

        function toggleRequestedBy() {
            const selectedText = select.options[select.selectedIndex]?.text || '';

            if (select.value === '0') {
                wrapper.classList.remove('d-none');
                input.value = '';
                input.removeAttribute('readonly');
                input.setAttribute('required', 'required');
                return;
            }

            if (select.value) {
                wrapper.classList.add('d-none');
                input.value = selectedText;
                input.setAttribute('readonly', 'readonly');
                input.removeAttribute('required');
                return;
            }

            wrapper.classList.add('d-none');
            input.value = '';
            input.removeAttribute('readonly');
            input.removeAttribute('required');
        }

        select.addEventListener('change', toggleRequestedBy);
        toggleRequestedBy();
    });

    document.addEventListener('DOMContentLoaded', function () {
        const warehouseSelect = document.getElementById('warehouse-id');
        const requestedSelect = document.getElementById('requested-by-id');
        const requestedNameWrapper = document.getElementById('requested-by-name-wrapper');
        const requestedNameInput = document.getElementById('requested-by-name');

        function resetRequestedBy() {
            requestedSelect.innerHTML = '';

            const emptyOption = new Option('Chọn người yêu cầu', '');
            requestedSelect.appendChild(emptyOption);

            const otherOption = new Option('--- Khác (nhập tay) ---', '0');
            requestedSelect.appendChild(otherOption);

            requestedNameWrapper.classList.add('d-none');
            requestedNameInput.value = '';
            requestedNameInput.removeAttribute('required');
        }

        function toggleRequestedName() {
            const selectedText = requestedSelect.options[requestedSelect.selectedIndex]?.text || '';

            if (requestedSelect.value === '0') {
                requestedNameWrapper.classList.remove('d-none');
                requestedNameInput.value = '';
                requestedNameInput.removeAttribute('readonly');
                requestedNameInput.setAttribute('required', 'required');
                return;
            }

            if (requestedSelect.value) {
                requestedNameWrapper.classList.add('d-none');
                requestedNameInput.value = selectedText;
                requestedNameInput.setAttribute('readonly', 'readonly');
                requestedNameInput.removeAttribute('required');
                return;
            }

            requestedNameWrapper.classList.add('d-none');
            requestedNameInput.value = '';
            requestedNameInput.removeAttribute('readonly');
            requestedNameInput.removeAttribute('required');
        }

        async function loadStaffByWarehouse(warehouseId) {
            resetRequestedBy();

            if (!warehouseId) {
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

                if (!result.data) {
                    return;
                }

                Object.entries(result.data).forEach(([id, name]) => {
                    requestedSelect.appendChild(new Option(name, id));
                });
            } catch (e) {
                console.error(e);
            }
        }

        warehouseSelect.addEventListener('change', function () {
            loadStaffByWarehouse(this.value);
        });

        requestedSelect.addEventListener('change', toggleRequestedName);

        if (warehouseSelect.value) {
            loadStaffByWarehouse(warehouseSelect.value);
        }

        toggleRequestedName();
    });



    //partner-type
    document.addEventListener('DOMContentLoaded', function () {
        const partnerType = document.getElementById('partner-type');
        const partnerId = document.getElementById('partner-id');

        function resetPartner() {
            partnerId.innerHTML = '';
            partnerId.appendChild(new Option('Chọn đối tượng', ''));
        }

        async function loadPartnerByType(type) {
            resetPartner();

            if (!type) {
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

                if (!result.data) {
                    return;
                }

                Object.entries(result.data).forEach(([id, name]) => {
                    partnerId.appendChild(new Option(name, id));
                });
            } catch (e) {
                console.error('Load partner error:', e);
            }
        }

        partnerType.addEventListener('change', function () {
            loadPartnerByType(this.value);
        });

        if (partnerType.value) {
            loadPartnerByType(partnerType.value);
        }
    });
</script>

