<x-core::card class="analytic-card">
    <x-core::card.body class="p-3">
        <div class="row align-items-center">
            
            {{-- LEFT --}}
            <div class="col-md-5">
                <div class="d-flex align-items-center h-100">
                    <div class="me-3">
                        <x-core::icon
                            class="text-white bg-primary rounded p-1"
                            name="ti ti-package"
                            size="md"
                        />
                    </div>

                    <div>
                        <p class="text-secondary mb-0 fs-4">
                            {{ trans('plugins/logistics::logistics.reports.provider') }}
                        </p>

                        <h3 class="mb-0 fs-1">
                            {{ $revenue }}
                        </h3>
                    </div>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="col-md-7">
                @if (!empty($names))
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($names as $name)
                            <span class="px-2 py-1 rounded border bg-light small">
                                {{ $name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </x-core::card.body>

    @include('plugins/logistics::admin.reports.widgets.card-description', ['result' => $result])
</x-core::card>