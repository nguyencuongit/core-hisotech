@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $supplier->name }}</h3>
                                <div class="text-muted">{{ $supplier->code }}</div>
                                <div class="mt-2">{!! $supplier->status?->toHtml() !!}</div>
                            </div>
                            <div class="d-flex gap-2">
                                @if (auth()->user()->hasPermission(\Botble\Inventory\Domains\Supplier\Permissions\SupplierPermissions::EDIT))
                                    <a class="btn btn-primary" href="{{ route('inventory.suppliers.edit', $supplier->id) }}">{{ trans('core/base::forms.edit') }}</a>
                                @endif
                                @if (auth()->user()->hasPermission(\Botble\Inventory\Domains\Supplier\Permissions\SupplierPermissions::DESTROY))
                                    {!! delete_button(route('inventory.suppliers.destroy', $supplier->id)) !!}
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.supplier.type.label') }}:</strong> {{ $supplier->type->label() }}</div>
                                <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.supplier.tax_code') }}:</strong> {{ $supplier->taxCode }}</div>
                                <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.supplier.website') }}:</strong> {{ $supplier->website }}</div>
                                <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.supplier.approval.requires_reapproval') }}:</strong> {{ $supplier->requiresReapproval ? 'Yes' : 'No' }}</div>
                            </div>
                            <hr>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <strong>{{ trans('plugins/inventory::inventory.supplier.created_by') }}:</strong>
                                    {{ $supplier->creatorName ?: '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans('plugins/inventory::inventory.supplier.created_at') }}:</strong>
                                    {{ $supplier->createdAt ? BaseHelper::formatDateTime($supplier->createdAt) : '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans('plugins/inventory::inventory.supplier.submitted_by') }}:</strong>
                                    {{ $supplier->submitterName ?: '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans('plugins/inventory::inventory.supplier.submitted_at') }}:</strong>
                                    {{ $supplier->submittedAt ? BaseHelper::formatDateTime($supplier->submittedAt) : '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans('plugins/inventory::inventory.supplier.approved_by') }}:</strong>
                                    {{ $supplier->approverName ?: '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans('plugins/inventory::inventory.supplier.approved_at') }}:</strong>
                                    {{ $supplier->approvedAt ? BaseHelper::formatDateTime($supplier->approvedAt) : '-' }}
                                </div>
                            </div>
                            <p>{{ $supplier->note }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header"><strong>{{ trans('plugins/inventory::inventory.supplier.contacts') }}</strong></div>
                        <div class="card-body">
                            @forelse($supplier->contacts as $contact)
                                <div class="mb-3 border-bottom pb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $contact->name }}</strong>
                                        @if($contact->isPrimary)<span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.primary') }}</span>@endif
                                    </div>
                                    <div>{{ $contact->position }}</div>
                                    <div>{{ $contact->phone }}</div>
                                    <div>{{ $contact->email }}</div>
                                </div>
                            @empty
                                <div class="text-muted">{{ trans('plugins/inventory::inventory.supplier.empty') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header"><strong>{{ trans('plugins/inventory::inventory.supplier.addresses') }}</strong></div>
                        <div class="card-body">
                            @forelse($supplier->addresses as $address)
                                <div class="mb-3 border-bottom pb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $address->type->label() }}</strong>
                                        @if($address->isDefault)<span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>@endif
                                    </div>
                                    <div>{{ $address->address }}</div>
                                </div>
                            @empty
                                <div class="text-muted">{{ trans('plugins/inventory::inventory.supplier.empty') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header"><strong>{{ trans('plugins/inventory::inventory.supplier.banks') }}</strong></div>
                        <div class="card-body">
                            @forelse($supplier->banks as $bank)
                                <div class="mb-3 border-bottom pb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $bank->bankName }}</strong>
                                        @if($bank->isDefault)<span class="badge bg-success">{{ trans('plugins/inventory::inventory.supplier.default') }}</span>@endif
                                    </div>
                                    <div>{{ $bank->accountName }}</div>
                                    <div>{{ $bank->accountNumber }}</div>
                                    <div>{{ $bank->branch }}</div>
                                </div>
                            @empty
                                <div class="text-muted">{{ trans('plugins/inventory::inventory.supplier.empty') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>{{ trans('plugins/inventory::inventory.supplier.products') }}</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>{{ trans('plugins/inventory::inventory.supplier.product') }}</th><th>{{ trans('plugins/inventory::inventory.supplier.purchase_price') }}</th><th>{{ trans('plugins/inventory::inventory.supplier.moq') }}</th><th>{{ trans('plugins/inventory::inventory.supplier.lead_time_days') }}</th></tr></thead>
                        <tbody>
                        @forelse($supplier->products as $item)
                            <tr>
                                <td>{{ $item->productName }}</td>
                                <td>{{ number_format((float) $item->purchasePrice, 4) }}</td>
                                <td>{{ $item->moq }}</td>
                                <td>{{ $item->leadTimeDays }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">{{ trans('plugins/inventory::inventory.supplier.empty') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>{{ trans('plugins/inventory::inventory.supplier.approval.history') }}</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Action</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Note</th>
                            <th>{{ trans('plugins/inventory::inventory.supplier.acted_by') }}</th>
                            <th>At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($supplier->approvals as $approval)
                            <tr>
                                <td>{{ $approval->action }}</td>
                                <td>{{ $approval->fromStatus }}</td>
                                <td>{{ $approval->toStatus }}</td>
                                <td>{{ $approval->note }}</td>
                                <td>{{ $approval->actorName ?: '-' }}</td>
                                <td>{{ $approval->actedAt ? BaseHelper::formatDateTime($approval->actedAt) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ trans('plugins/inventory::inventory.supplier.empty') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
