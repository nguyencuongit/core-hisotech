@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $canEditReceipt = auth()->user()?->hasPermission('inventory.goods-receipts.edit');
    $storageStatuses = [
        'receiving' => 'Đang nhận',
        'qc_hold' => 'Giữ QC',
        'pending_putaway' => 'Chờ putaway',
        'stored' => 'Đã vào kho',
        'damaged' => 'Hư hỏng',
        'rejected' => 'Từ chối',
        'closed' => 'Đóng',
    ];
@endphp

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
                        @if ($canEditReceipt)
                            <a class="btn btn-primary" href="{{ route('inventory.goods-receipts.edit', $goodsReceipt) }}">
                                {{ trans('core/base::forms.edit') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.supplier') }}:</strong> {{ $goodsReceipt->supplier?->name }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.warehouse') }}:</strong> {{ $goodsReceipt->warehouse?->name }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.receipt_date') }}:</strong> {{ $goodsReceipt->receipt_date?->format('Y-m-d') }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.reference_code') }}:</strong> {{ $goodsReceipt->reference_code ?: '—' }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.subtotal') }}:</strong> {{ number_format((float) $goodsReceipt->subtotal, 0) }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.discount_amount') }}:</strong> {{ number_format((float) $goodsReceipt->discount_amount, 0) }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.tax_amount') }}:</strong> {{ number_format((float) $goodsReceipt->tax_amount, 0) }}</div>
                        <div class="col-md-3"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.total_amount') }}:</strong> {{ number_format((float) $goodsReceipt->total_amount, 0) }}</div>
                        @if ($goodsReceipt->note)
                            <div class="col-12"><strong>{{ trans('plugins/inventory::inventory.goods_receipt.note') }}:</strong> {{ $goodsReceipt->note }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-3">
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
                            @forelse ($goodsReceipt->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product_name }}</div>
                                        @if ($item->barcode)
                                            <div class="text-muted small">{{ $item->barcode }}</div>
                                        @endif
                                        @if ($item->batches->isNotEmpty())
                                            <div class="text-muted small mt-1">
                                                @foreach ($item->batches as $batch)
                                                    <div>
                                                        Batch: {{ $batch->batch_no ?: '—' }}
                                                        @if ($batch->serial_no)
                                                            • Serial: {{ $batch->serial_no }}
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $item->sku ?: '—' }}</td>
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

            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong>Hàng thực tế trong kho</strong>
                        <div class="text-muted small">Sinh storage item, gán vị trí, gán pallet và ghi tồn kho từ phiếu nhập.</div>
                    </div>
                    @if ($canEditReceipt)
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('inventory.goods-receipts.storage-items.generate', $goodsReceipt) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">Sinh storage items</button>
                            </form>
                            @if ($goodsReceipt->storageItems->whereNull('posted_at')->whereIn('status', ['stored', 'qc_hold', 'damaged', 'rejected'])->isNotEmpty())
                                <form method="POST" action="{{ route('inventory.goods-receipts.storage-items.post-all', $goodsReceipt) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Ghi tồn tất cả item hợp lệ</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Storage items</div>
                                <div class="fs-3 fw-bold">{{ $goodsReceipt->storageItems->count() }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Đã post</div>
                                <div class="fs-3 fw-bold">{{ $goodsReceipt->storageItems->whereNotNull('posted_at')->count() }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Đang chờ xử lý</div>
                                <div class="fs-3 fw-bold">{{ $goodsReceipt->storageItems->whereNull('posted_at')->count() }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Kho dùng pallet</div>
                                <div class="fs-3 fw-bold">{{ $warehouseSetting->use_pallet ? 'Có' : 'Không' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table align-middle">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Batch / Serial</th>
                                <th>Số lượng</th>
                                <th>Vị trí hiện tại</th>
                                @if ($warehouseSetting->use_pallet)
                                    <th>Pallet</th>
                                @endif
                                <th>Trạng thái</th>
                                <th>Available</th>
                                <th>Đã post</th>
                                <th class="text-end">Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($goodsReceipt->storageItems as $storageItem)
                                @php
                                    $formId = 'storage-item-form-' . $storageItem->getKey();
                                @endphp
                                <tr>
                                    <td style="min-width: 240px;">
                                        <div class="fw-semibold">{{ $storageItem->product?->name ?: $storageItem->goodsReceiptItem?->product_name ?: '—' }}</div>
                                        <div class="text-muted small">
                                            SKU: {{ $storageItem->goodsReceiptItem?->sku ?: '—' }}
                                            • Item: {{ $storageItem->goods_receipt_item_id }}
                                        </div>
                                    </td>
                                    <td style="min-width: 180px;">
                                        <div>Batch: {{ $storageItem->goodsReceiptBatch?->batch_no ?: '—' }}</div>
                                        <div class="text-muted small">Serial: {{ $storageItem->goodsReceiptBatch?->serial_no ?: '—' }}</div>
                                    </td>
                                    <td style="min-width: 150px;">
                                        <div>Nhận: {{ number_format((float) $storageItem->received_qty, 4) }}</div>
                                        <div class="text-muted small">Available: {{ number_format((float) $storageItem->available_qty, 4) }}</div>
                                    </td>
                                    <td style="min-width: 260px;">
                                        @if ($canEditReceipt && ! $storageItem->posted_at)
                                            <select class="form-select form-select-sm" name="warehouse_location_id" form="{{ $formId }}">
                                                    <option value="">Chọn vị trí kho</option>
                                                    @foreach ($storageLocations as $location)
                                                        <option value="{{ $location->id }}" @selected((int) $storageItem->warehouse_location_id === (int) $location->id)>
                                                            {{ $location->path ?: $location->code }} - {{ $location->name }}
                                                        </option>
                                                    @endforeach
                                            </select>
                                        @else
                                            <div>{{ $storageItem->warehouseLocation?->path ?: ($storageItem->warehouseLocation?->code ?: '—') }}</div>
                                            <div class="text-muted small">{{ $storageItem->warehouseLocation?->name ?: 'Chưa gán vị trí' }}</div>
                                        @endif
                                    </td>
                                    @if ($warehouseSetting->use_pallet)
                                        <td style="min-width: 220px;">
                                            @if ($canEditReceipt && ! $storageItem->posted_at)
                                                <select class="form-select form-select-sm" name="pallet_id" form="{{ $formId }}">
                                                        <option value="">Không dùng pallet</option>
                                                        @foreach ($pallets as $pallet)
                                                            <option value="{{ $pallet->id }}" @selected((int) $storageItem->pallet_id === (int) $pallet->id)>
                                                                {{ $pallet->code }}{{ $pallet->currentLocation ? ' • ' . $pallet->currentLocation->code : '' }}
                                                            </option>
                                                        @endforeach
                                                </select>
                                            @else
                                                @if ($storageItem->pallet)
                                                    <div>{{ $storageItem->pallet->code }}</div>
                                                    <div class="text-muted small">{{ $storageItem->pallet->currentLocation?->code ?: '—' }}</div>
                                                @else
                                                    —
                                                @endif
                                            @endif
                                        </td>
                                    @endif
                                    <td style="min-width: 180px;">
                                        @if ($canEditReceipt && ! $storageItem->posted_at)
                                            <select class="form-select form-select-sm" name="status" form="{{ $formId }}">
                                                @foreach ($storageStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($storageItem->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{ $storageStatuses[$storageItem->status] ?? $storageItem->status }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format((float) $storageItem->available_qty, 4) }}</span>
                                    </td>
                                    <td style="min-width: 160px;">
                                        @if ($storageItem->posted_at)
                                            <span class="badge bg-success-lt text-success">Đã post</span>
                                            <div class="text-muted small mt-1">{{ $storageItem->posted_at?->format('Y-m-d H:i') }}</div>
                                        @else
                                            <span class="badge bg-warning-lt text-warning">Chưa post</span>
                                        @endif
                                    </td>
                                    <td class="text-end" style="min-width: 260px;">
                                        @if ($canEditReceipt && ! $storageItem->posted_at)
                                            <form id="{{ $formId }}" method="POST" action="{{ route('inventory.goods-receipts.storage-items.update', [$goodsReceipt, $storageItem]) }}">
                                                @csrf
                                                @method('PUT')
                                            </form>
                                            <textarea class="form-control form-control-sm mb-2" name="note" rows="2" placeholder="Ghi chú" form="{{ $formId }}">{{ $storageItem->note }}</textarea>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="submit" class="btn btn-outline-secondary btn-sm" form="{{ $formId }}">Lưu</button>
                                                @if (in_array($storageItem->status, ['stored', 'qc_hold', 'damaged', 'rejected'], true))
                                                    <form method="POST" action="{{ route('inventory.goods-receipts.storage-items.post', [$goodsReceipt, $storageItem]) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-sm">Ghi tồn kho</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            @if ($storageItem->note)
                                                <span class="text-muted small">{{ $storageItem->note }}</span>
                                            @else
                                                —
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $warehouseSetting->use_pallet ? 9 : 8 }}" class="text-center py-5">
                                        <div class="fw-semibold mb-1">Chưa có storage item</div>
                                        <div class="text-muted small">Bấm “Sinh storage items” để tạo dữ liệu thực nhập từ phiếu nhập.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
