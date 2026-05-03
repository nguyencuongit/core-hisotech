@php
    $rows = $items ?: [[]];
    $stockBalancesById = collect($stockBalances)->keyBy('id');
    $status = $status ?? 'draft';
    $isReceiving = $isReceiving ?? false;
    $showReceivingColumns = $isReceiving
        || in_array($status, ['completed'], true)
        || collect($rows)->contains(fn ($row) => (float) ($row['received_qty'] ?? 0) > 0 || (float) ($row['damaged_qty'] ?? 0) > 0);
@endphp

<div class="transfer-items-card">
    <div class="transfer-items-header">
        <div>
            <span>ITEMS</span>
            <h2>Sản phẩm chuyển kho</h2>
        </div>

        @if(! $isLocked)
            <button type="button" class="transfer-add-item-button" id="transfer-add-item">
                <i class="ti ti-plus"></i> Thêm dòng
            </button>
        @endif
    </div>

    <div class="table-responsive transfer-items-scroll">
        <table class="table table-vcenter mb-0 transfer-items-table" id="transfer-items-table">
            <thead>
                <tr>
                    <th style="min-width: 360px;">Tồn kho nguồn</th>
                    <th style="min-width: 150px;">Khả dụng</th>
                    <th style="min-width: 150px;">Số lượng chuyển</th>
                    @if($showReceivingColumns)
                        <th style="min-width: 130px;">Đã xuất</th>
                        <th style="min-width: 140px;">Thực nhận</th>
                        <th style="min-width: 140px;">Hỏng</th>
                        <th style="min-width: 140px;">Thiếu</th>
                        <th style="min-width: 140px;">Dư</th>
                    @endif
                    <th style="min-width: 260px;">Vị trí nhập</th>
                    <th style="min-width: 220px;">Pallet đích</th>
                    <th style="min-width: 140px;">Giá vốn</th>
                    <th style="min-width: 150px;">Thành tiền</th>
                    <th style="min-width: 220px;">Ghi chú</th>
                    <th style="width: 64px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $index => $item)
                    @php
                        $selectedStockId = (string) ($item['stock_balance_id'] ?? '');
                        $selectedStock = $stockBalancesById->get($selectedStockId);
                        $availableQty = (float) ($selectedStock['available_qty'] ?? 0);
                    @endphp

                    <tr class="transfer-item-row">
                        <td>
                            <input type="hidden" class="item-id-input" name="items[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                            <input type="hidden" class="product-id-input" name="items[{{ $index }}][product_id]" value="{{ $item['product_id'] ?? ($selectedStock['product_id'] ?? '') }}">
                            <input type="hidden" class="product-variation-id-input" name="items[{{ $index }}][product_variation_id]" value="{{ $item['product_variation_id'] ?? ($selectedStock['product_variation_id'] ?? '') }}">
                            <input type="hidden" class="product-code-input" name="items[{{ $index }}][product_code]" value="{{ $item['product_code'] ?? ($selectedStock['product_code'] ?? '') }}">
                            <input type="hidden" class="product-name-input" name="items[{{ $index }}][product_name]" value="{{ $item['product_name'] ?? ($selectedStock['product_name'] ?? '') }}">
                            <input type="hidden" class="from-location-id-input" name="items[{{ $index }}][from_location_id]" value="{{ $item['from_location_id'] ?? ($selectedStock['from_location_id'] ?? '') }}">
                            <input type="hidden" class="pallet-id-input" name="items[{{ $index }}][pallet_id]" value="{{ $item['pallet_id'] ?? ($selectedStock['pallet_id'] ?? '') }}">
                            <input type="hidden" class="batch-id-input" name="items[{{ $index }}][batch_id]" value="{{ $item['batch_id'] ?? ($selectedStock['batch_id'] ?? '') }}">
                            <input type="hidden" class="goods-receipt-batch-id-input" name="items[{{ $index }}][goods_receipt_batch_id]" value="{{ $item['goods_receipt_batch_id'] ?? ($selectedStock['goods_receipt_batch_id'] ?? '') }}">

                            <select class="form-control transfer-stock-select" name="items[{{ $index }}][stock_balance_id]" @disabled($isLocked)>
                                <option value="">Chọn tồn kho</option>
                                @foreach($stockBalances as $stock)
                                    <option
                                        value="{{ $stock['id'] }}"
                                        data-warehouse-id="{{ $stock['warehouse_id'] }}"
                                        @selected($selectedStockId === (string) $stock['id'])
                                    >
                                        {{ $stock['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1 transfer-stock-meta">
                                {{ $selectedStock ? (($selectedStock['warehouse_name'] ?? '-') . ' / ' . ($selectedStock['from_location_label'] ?? '-')) : '-' }}
                            </div>
                        </td>

                        <td>
                            <div class="transfer-available-qty">{{ number_format($availableQty, 4) }}</div>
                            <div class="small text-muted transfer-pallet-meta">
                                {{ $selectedStock && ($selectedStock['pallet_code'] ?? null) ? ('Pallet ' . $selectedStock['pallet_code']) : '' }}
                            </div>
                        </td>

                        <td>
                            <input
                                type="number"
                                class="form-control transfer-qty-input"
                                name="items[{{ $index }}][requested_qty]"
                                value="{{ $item['requested_qty'] ?? '' }}"
                                min="0"
                                step="0.0001"
                                @disabled($isLocked)
                            >
                        </td>

                        @if($showReceivingColumns)
                            @php
                                $exportedQty = (float) ($item['exported_qty'] ?? 0);
                                $receivedQty = array_key_exists('received_qty', $item)
                                    ? (float) ($item['received_qty'] ?? 0)
                                    : (float) ($item['requested_qty'] ?? 0);
                                $displayReceivedQty = $isReceiving && $receivedQty <= 0
                                    ? (float) ($item['requested_qty'] ?? 0)
                                    : $receivedQty;
                                $damagedQty = (float) ($item['damaged_qty'] ?? 0);
                                $shortageQty = max((float) ($item['requested_qty'] ?? 0) - $displayReceivedQty, 0);
                                $overageQty = max($displayReceivedQty - (float) ($item['requested_qty'] ?? 0), 0);
                            @endphp

                            <td>
                                <input
                                    type="number"
                                    class="form-control transfer-exported-qty-input"
                                    name="items[{{ $index }}][exported_qty]"
                                    value="{{ $exportedQty ?: ($status === 'exporting' ? ($item['requested_qty'] ?? 0) : '') }}"
                                    readonly
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    class="form-control transfer-received-qty-input"
                                    name="items[{{ $index }}][received_qty]"
                                    value="{{ $displayReceivedQty ?: '' }}"
                                    min="0"
                                    step="0.0001"
                                    @disabled(! $isReceiving)
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    class="form-control transfer-damaged-qty-input"
                                    name="items[{{ $index }}][damaged_qty]"
                                    value="{{ $damagedQty ?: '' }}"
                                    min="0"
                                    step="0.0001"
                                    @disabled(! $isReceiving)
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    class="form-control transfer-shortage-qty-input"
                                    name="items[{{ $index }}][shortage_qty]"
                                    value="{{ $shortageQty ?: '' }}"
                                    readonly
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    class="form-control transfer-overage-qty-input"
                                    name="items[{{ $index }}][overage_qty]"
                                    value="{{ $overageQty ?: '' }}"
                                    readonly
                                >
                            </td>
                        @endif

                        <td>
                            <select class="form-control transfer-to-location-select" name="items[{{ $index }}][to_location_id]" @disabled($isLocked)>
                                <option value="">Chọn vị trí nhập</option>
                                @foreach($locations as $location)
                                    <option
                                        value="{{ $location['id'] }}"
                                        data-warehouse-id="{{ $location['warehouse_id'] }}"
                                        @selected((int) ($item['to_location_id'] ?? 0) === (int) $location['id'])
                                    >
                                        {{ $location['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <select class="form-control transfer-to-pallet-select" name="items[{{ $index }}][to_pallet_id]" @disabled($isLocked && ! $isReceiving)>
                                <option value="">Không pallet</option>
                                @foreach($pallets as $pallet)
                                    <option
                                        value="{{ $pallet['id'] }}"
                                        data-warehouse-id="{{ $pallet['warehouse_id'] }}"
                                        @selected((int) ($item['to_pallet_id'] ?? 0) === (int) $pallet['id'])
                                    >
                                        {{ $pallet['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input
                                type="number"
                                class="form-control transfer-unit-price-input"
                                name="items[{{ $index }}][unit_price]"
                                value="{{ $item['unit_price'] ?? ($selectedStock['unit_price'] ?? 0) }}"
                                min="0"
                                step="0.01"
                                @disabled($isLocked)
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                class="form-control transfer-amount-input"
                                name="items[{{ $index }}][amount]"
                                value="{{ $item['amount'] ?? '' }}"
                                readonly
                                @disabled($isLocked)
                            >
                        </td>

                        <td>
                            <input
                                type="text"
                                class="form-control transfer-note-input"
                                name="items[{{ $index }}][note]"
                                value="{{ $item['note'] ?? '' }}"
                                placeholder="Ghi chú"
                                @disabled($isLocked)
                            >
                        </td>

                        <td class="text-center">
                            @if(! $isLocked)
                                <button type="button" class="transfer-icon-button transfer-remove-item" aria-label="Xóa dòng">
                                    <i class="ti ti-trash"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
    .transfer-items-card {
        background: #FFFFFF;
        border: 1px solid #D9DEE6;
        border-radius: 16px;
        overflow: hidden;
    }

    .transfer-items-header {
        align-items: center;
        background: #FFFFFF;
        border-bottom: 1px solid #D9DEE6;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        padding: 20px 24px;
    }

    .transfer-items-header span {
        color: #4A5568;
        font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    .transfer-items-header h2 {
        color: #0F1419;
        font-size: 1.35rem;
        font-weight: 650;
        letter-spacing: 0;
        margin: 4px 0 0;
    }

    .transfer-add-item-button {
        align-items: center;
        background: #FFFFFF;
        border: 1px solid #D9DEE6;
        border-radius: 10px;
        color: #0F1419;
        display: inline-flex;
        font-weight: 700;
        gap: 8px;
        min-height: 44px;
        padding: 10px 16px;
    }

    .transfer-add-item-button:hover {
        border-color: #4A5568;
    }

    .transfer-items-scroll {
        background: #FFFFFF;
    }

    .transfer-items-table {
        min-width: {{ $showReceivingColumns ? '2100px' : '1660px' }};
    }

    .transfer-items-table th {
        background: #F1F3F5;
        border-bottom: 1px solid #D9DEE6;
        color: #4A5568;
        font-family: "Geist Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .transfer-items-table td {
        border-color: #EEF1F4;
        vertical-align: top;
    }

    .transfer-items-table .form-control {
        border: 1px solid #D9DEE6;
        border-radius: 10px;
        color: #0F1419;
        min-height: 44px;
    }

    .transfer-available-qty {
        color: #0F1419;
        font-size: 18px;
        font-weight: 750;
    }

    .transfer-stock-meta,
    .transfer-pallet-meta {
        color: #4A5568 !important;
        line-height: 1.45;
    }

    .transfer-icon-button {
        align-items: center;
        background: #FFFFFF;
        border: 1px solid #D9DEE6;
        border-radius: 10px;
        color: #4A5568;
        display: inline-flex;
        height: 44px;
        justify-content: center;
        width: 44px;
    }

    .transfer-icon-button:hover {
        border-color: #4A5568;
        color: #0F1419;
    }

    @media (max-width: 767.98px) {
        .transfer-items-header {
            align-items: stretch;
            display: grid;
            padding: 16px;
        }

        .transfer-add-item-button {
            justify-content: center;
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('transfer-items-table');
        const addButton = document.getElementById('transfer-add-item');
        const fromWarehouse = document.getElementById('transfer-from-warehouse');
        const toWarehouse = document.getElementById('transfer-to-warehouse');
        const stockBalances = @json($stockBalances);

        if (!table) {
            return;
        }

        const tbody = table.querySelector('tbody');
        const stockById = {};

        stockBalances.forEach(function (stock) {
            stockById[String(stock.id)] = stock;
        });

        function rows() {
            return Array.from(tbody.querySelectorAll('.transfer-item-row'));
        }

        function formatNumber(value) {
            return Number(value || 0).toLocaleString('vi-VN', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 4,
            });
        }

        function setInput(row, selector, value) {
            const input = row.querySelector(selector);

            if (input) {
                input.value = value || '';
            }
        }

        function selectedToWarehouseId() {
            return toWarehouse ? String(toWarehouse.value || '') : '';
        }

        function selectedFromWarehouseId() {
            return fromWarehouse ? String(fromWarehouse.value || '') : '';
        }

        function updateStockOptions(row) {
            const select = row.querySelector('.transfer-stock-select');
            const warehouseId = selectedFromWarehouseId();

            if (!select) {
                return;
            }

            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const matches = !warehouseId || String(option.dataset.warehouseId || '') === warehouseId;
                option.disabled = !matches;
            });

            if (select.value && select.selectedOptions[0]?.disabled) {
                select.value = '';
                applyStock(row);
            }
        }

        function updateLocationOptions(row) {
            const select = row.querySelector('.transfer-to-location-select');
            const warehouseId = selectedToWarehouseId();

            if (!select) {
                return;
            }

            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const matches = !warehouseId || String(option.dataset.warehouseId || '') === warehouseId;
                option.disabled = !matches;
            });

            if (select.value && select.selectedOptions[0]?.disabled) {
                select.value = '';
            }
        }

        function updatePalletOptions(row) {
            const select = row.querySelector('.transfer-to-pallet-select');
            const warehouseId = selectedToWarehouseId();

            if (!select) {
                return;
            }

            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const matches = !warehouseId || String(option.dataset.warehouseId || '') === warehouseId;
                option.disabled = !matches;
            });

            if (select.value && select.selectedOptions[0]?.disabled) {
                select.value = '';
            }
        }

        function calculateRow(row) {
            const qty = parseFloat(row.querySelector('.transfer-qty-input')?.value || 0);
            const price = parseFloat(row.querySelector('.transfer-unit-price-input')?.value || 0);
            const amount = qty * price;
            const amountInput = row.querySelector('.transfer-amount-input');

            if (amountInput) {
                amountInput.value = amount ? amount.toFixed(2) : '';
            }
        }

        function calculateReceivingRow(row) {
            const requestedQty = parseFloat(row.querySelector('.transfer-qty-input')?.value || 0);
            const receivedQty = parseFloat(row.querySelector('.transfer-received-qty-input')?.value || 0);
            const shortageInput = row.querySelector('.transfer-shortage-qty-input');
            const overageInput = row.querySelector('.transfer-overage-qty-input');

            if (shortageInput) {
                const shortage = Math.max(requestedQty - receivedQty, 0);
                shortageInput.value = shortage ? shortage.toFixed(4) : '';
            }

            if (overageInput) {
                const overage = Math.max(receivedQty - requestedQty, 0);
                overageInput.value = overage ? overage.toFixed(4) : '';
            }
        }

        function applyStock(row) {
            const select = row.querySelector('.transfer-stock-select');
            const stock = stockById[String(select?.value || '')];
            const meta = row.querySelector('.transfer-stock-meta');
            const available = row.querySelector('.transfer-available-qty');
            const palletMeta = row.querySelector('.transfer-pallet-meta');
            const qtyInput = row.querySelector('.transfer-qty-input');

            if (!stock) {
                ['.product-id-input', '.product-variation-id-input', '.product-code-input', '.product-name-input', '.from-location-id-input', '.pallet-id-input', '.batch-id-input', '.goods-receipt-batch-id-input'].forEach(function (selector) {
                    setInput(row, selector, '');
                });

                if (meta) meta.textContent = '-';
                if (available) available.textContent = '0';
                if (palletMeta) palletMeta.textContent = '';

                return;
            }

            setInput(row, '.product-id-input', stock.product_id);
            setInput(row, '.product-variation-id-input', stock.product_variation_id);
            setInput(row, '.product-code-input', stock.product_code);
            setInput(row, '.product-name-input', stock.product_name);
            setInput(row, '.from-location-id-input', stock.from_location_id);
            setInput(row, '.pallet-id-input', stock.pallet_id);
            setInput(row, '.batch-id-input', stock.batch_id);
            setInput(row, '.goods-receipt-batch-id-input', stock.goods_receipt_batch_id);
            setInput(row, '.transfer-unit-price-input', stock.unit_price);

            if (qtyInput) {
                qtyInput.max = stock.available_qty || '';
            }

            if (meta) {
                meta.textContent = [stock.warehouse_name, stock.from_location_label].filter(Boolean).join(' / ') || '-';
            }

            if (available) {
                available.textContent = formatNumber(stock.available_qty || 0);
            }

            if (palletMeta) {
                palletMeta.textContent = stock.pallet_code ? ('Pallet ' + stock.pallet_code) : '';
            }

            calculateRow(row);
            calculateReceivingRow(row);
        }

        function reindexRows() {
            rows().forEach(function (row, index) {
                row.querySelectorAll('input, select, textarea').forEach(function (input) {
                    if (!input.name) {
                        return;
                    }

                    input.name = input.name.replace(/items\[\d+]/, 'items[' + index + ']');
                });
            });
        }

        function resetRow(row) {
            row.querySelectorAll('input').forEach(function (input) {
                if (input.classList.contains('transfer-unit-price-input')) {
                    input.value = 0;
                    return;
                }

                input.value = '';
            });

            row.querySelectorAll('select').forEach(function (select) {
                select.value = '';
            });

            const available = row.querySelector('.transfer-available-qty');
            const meta = row.querySelector('.transfer-stock-meta');
            const palletMeta = row.querySelector('.transfer-pallet-meta');

            if (available) available.textContent = '0';
            if (meta) meta.textContent = '-';
            if (palletMeta) palletMeta.textContent = '';
        }

        if (addButton) {
            addButton.addEventListener('click', function () {
                const firstRow = tbody.querySelector('.transfer-item-row');

                if (!firstRow) {
                    return;
                }

                const clone = firstRow.cloneNode(true);
                resetRow(clone);
                tbody.appendChild(clone);
                reindexRows();
                updateStockOptions(clone);
                updateLocationOptions(clone);
                updatePalletOptions(clone);
            });
        }

        tbody.addEventListener('click', function (event) {
            const button = event.target.closest('.transfer-remove-item');

            if (!button) {
                return;
            }

            const row = button.closest('.transfer-item-row');

            if (!row) {
                return;
            }

            if (rows().length <= 1) {
                resetRow(row);
            } else {
                row.remove();
            }

            reindexRows();
        });

        tbody.addEventListener('change', function (event) {
            const row = event.target.closest('.transfer-item-row');

            if (!row) {
                return;
            }

            if (event.target.classList.contains('transfer-stock-select')) {
                applyStock(row);
            }
        });

        tbody.addEventListener('input', function (event) {
            const row = event.target.closest('.transfer-item-row');

            if (!row) {
                return;
            }

            if (event.target.classList.contains('transfer-qty-input') || event.target.classList.contains('transfer-unit-price-input')) {
                calculateRow(row);
            }

            if (
                event.target.classList.contains('transfer-qty-input') ||
                event.target.classList.contains('transfer-received-qty-input') ||
                event.target.classList.contains('transfer-damaged-qty-input')
            ) {
                calculateReceivingRow(row);
            }
        });

        [fromWarehouse, toWarehouse].forEach(function (select) {
            if (!select) {
                return;
            }

            select.addEventListener('change', function () {
                rows().forEach(function (row) {
                    updateStockOptions(row);
                    updateLocationOptions(row);
                    updatePalletOptions(row);
                });
            });
        });

        rows().forEach(function (row) {
            updateStockOptions(row);
            updateLocationOptions(row);
            updatePalletOptions(row);
            calculateRow(row);
            calculateReceivingRow(row);
        });
    });
</script>
