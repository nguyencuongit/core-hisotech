@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">{{ $goodsReceipt->code }}</h3>
                        <div class="text-muted">{{ trans('plugins/inventory::inventory.goods_receipt.show') }}</div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        {!! $goodsReceipt->status?->toHtml() !!}
                        @if (auth()->user()?->hasPermission('inventory.goods-receipts.edit'))
                            <a class="btn btn-primary" href="{{ route('inventory.goods-receipts.edit', $goodsReceipt) }}">{{ trans('core/base::forms.edit') }}</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.supplier') }}:</strong> {{ $goodsReceipt->supplier?->name }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.warehouse') }}:</strong> {{ $goodsReceipt->warehouse?->name }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.receipt_date') }}:</strong> {{ $goodsReceipt->receipt_date?->format('Y-m-d') }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.reference_code') }}:</strong> {{ $goodsReceipt->reference_code }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.subtotal') }}:</strong> {{ number_format((float) $goodsReceipt->subtotal, 0) }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.discount_amount') }}:</strong> {{ number_format((float) $goodsReceipt->discount_amount, 0) }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.tax_amount') }}:</strong> {{ number_format((float) $goodsReceipt->tax_amount, 0) }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.total_amount') }}:</strong> {{ number_format((float) $goodsReceipt->total_amount, 0) }}</div>
                        @if($goodsReceipt->note)
                            <div class="col-12"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.note') }}:</strong> {{ $goodsReceipt->note }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <strong>{{ trans('plugins/inventory::inventory.goods_receipt.items') }}</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>{{ trans('plugins/inventory::inventory.goods_receipt.product') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.goods_receipt.sku') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.goods_receipt.ordered_qty') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.goods_receipt.received_qty') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.goods_receipt.rejected_qty') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.goods_receipt.unit_cost') }}</th>
                                <th>{{ trans('plugins/inventory::inventory.goods_receipt.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($goodsReceipt->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product_name }}</div>
                                        @if($item->barcode)
                                            <div class="text-muted small">{{ $item->barcode }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->sku }}</td>
                                    <td>{{ number_format((float) $item->ordered_qty, 4) }}</td>
                                    <td>{{ number_format((float) $item->received_qty, 4) }}</td>
                                    <td>{{ number_format((float) $item->rejected_qty, 4) }}</td>
                                    <td>{{ number_format((float) $item->unit_cost, 0) }}</td>
                                    <td>{{ number_format((float) $item->line_total, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">{{ trans('plugins/inventory::inventory.goods_receipt.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
