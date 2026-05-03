@php
    $packages = is_array($packages ?? null) ? $packages : [];
    $exportItems = is_array($exportItems ?? null) ? $exportItems : [];
    $exportItemsData = is_array($exportItemsData ?? null) ? $exportItemsData : [];
    $exportPreviewUrl = $exportPreviewUrl ?? null;
@endphp

<section class="packing-package-panel">
    <div class="packing-package-panel__header">
        <div>
            <div class="packing-package-panel__eyebrow">Kiện hàng</div>
            <div class="packing-package-panel__title">Danh sách package</div>
            <p class="packing-package-panel__hint">Chọn dòng hàng của phiếu xuất, sau đó nhập số lượng đóng gói cho từng kiện.</p>
        </div>
        <div class="packing-package-panel__actions">
            <span class="packing-package-chip">Tự tính tổng</span>
            <button type="button" class="btn btn-primary" id="add-package-btn">
                <i class="ti ti-plus me-1"></i>Thêm package
            </button>
        </div>
    </div>

    <div id="packing-packages-root"></div>
</section>

<template id="package-template">
    <div class="card package-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold package-title">Package</div>
            <button type="button" class="btn btn-outline-secondary btn-sm remove-package-btn" title="Xóa package">
                <i class="ti ti-trash"></i>
            </button>
        </div>
        <div class="card-body">
            <input type="hidden" class="package-id-input">
            <div class="row">
                <div class="form-group col-xl-2 col-lg-3 col-md-4 col-12">
                    <label>Số kiện</label>
                    <input type="number" class="form-control package-no-input" min="1" step="1" value="1">
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6 col-12">
                    <label>Mã kiện</label>
                    <input type="text" class="form-control package-code-input" placeholder="PKG-001">
                </div>
                <div class="form-group col-xl-2 col-lg-3 col-md-4 col-12">
                    <label>Loại kiện</label>
                    <select class="form-control package-type-input">
                        <option value="">Chọn loại</option>
                        <option value="box">Box</option>
                        <option value="pallet">Pallet</option>
                        <option value="bag">Bag</option>
                        <option value="crate">Crate</option>
                    </select>
                </div>
                <div class="form-group col-xl-2 col-lg-3 col-md-4 col-12">
                    <label>Trạng thái kiện</label>
                    <select class="form-control package-status-input">
                        <option value="open">Đang mở</option>
                        <option value="closed">Đã đóng</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6 col-12">
                    <label>Tracking code</label>
                    <input type="text" class="form-control package-tracking-code-input" placeholder="Tracking code">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-xl-1 col-lg-2 col-md-3 col-6">
                    <label>Dài</label>
                    <input type="number" class="form-control package-length-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-xl-1 col-lg-2 col-md-3 col-6">
                    <label>Rộng</label>
                    <input type="number" class="form-control package-width-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-xl-1 col-lg-2 col-md-3 col-6">
                    <label>Cao</label>
                    <input type="number" class="form-control package-height-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-xl-2 col-lg-3 col-md-4 col-6">
                    <label>Đơn vị KT</label>
                    <select class="form-control package-dimension-unit-input">
                        <option value="cm">cm</option>
                        <option value="m">m</option>
                        <option value="mm">mm</option>
                    </select>
                </div>
                <div class="form-group col-xl-2 col-lg-3 col-md-4 col-6">
                    <label>Thể tích</label>
                    <input type="number" class="form-control package-volume-input" min="0" step="0.0001" value="0" readonly>
                </div>
                <div class="form-group col-xl-2 col-lg-3 col-md-4 col-6">
                    <label>Trọng lượng</label>
                    <input type="number" class="form-control package-weight-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-xl-1 col-lg-2 col-md-3 col-6">
                    <label>Đơn vị KL</label>
                    <select class="form-control package-weight-unit-input">
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="lb">lb</option>
                    </select>
                </div>
                <div class="form-group col-xl-2 col-lg-3 col-md-4 col-6">
                    <label>Volume weight</label>
                    <input type="number" class="form-control package-volume-weight-input" min="0" step="0.0001" value="0">
                </div>
            </div>

            <div class="form-group">
                <label>Shipping label URL</label>
                <input type="text" class="form-control package-shipping-label-url-input" placeholder="https://...">
            </div>

            <div class="form-group">
                <label>Ghi chú package</label>
                <textarea class="form-control package-note-input" rows="2" placeholder="Ghi chú"></textarea>
            </div>

            <div class="packing-package-items">
                <div class="packing-package-items__header">
                    <strong>Sản phẩm trong package</strong>
                    <button type="button" class="btn btn-outline-secondary btn-sm add-item-btn">
                        <i class="ti ti-plus me-1"></i>Thêm sản phẩm
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 package-items-table">
                        <thead>
                            <tr>
                                <th class="package-item-col-export">Dòng phiếu xuất</th>
                                <th class="package-item-col-qty">SL đóng gói</th>
                                <th class="package-item-col-unit">Đơn vị</th>
                                <th class="package-item-col-note">Ghi chú</th>
                                <th style="width: 60px;">#</th>
                            </tr>
                        </thead>
                        <tbody class="package-items-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="package-item-template">
    <tr class="package-item-row">
        <td data-label="Dòng phiếu xuất">
            <label class="package-item-picker">
                <input type="checkbox" class="package-item-check">
                <span>Chọn sản phẩm cho kiện này</span>
            </label>
            <input type="hidden" class="item-id-input">
            <input type="hidden" class="product-id-input">
            <input type="hidden" class="product-variation-id-input">
            <input type="hidden" class="product-code-input">
            <input type="hidden" class="product-name-input">
            <input type="hidden" class="unit-id-input">
            <input type="hidden" class="warehouse-location-id-input">
            <input type="hidden" class="pallet-id-input">
            <input type="hidden" class="batch-id-input">
            <input type="hidden" class="goods-receipt-batch-id-input">
            <input type="hidden" class="stock-balance-id-input">
            <input type="hidden" class="storage-item-id-input">
            <input type="hidden" class="lot-no-input">
            <input type="hidden" class="expiry-date-input">
            <select class="form-control export-item-select">
                <option value="">Chọn dòng phiếu xuất...</option>
                @foreach($exportItems as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="package-item-summary" data-package-item-summary hidden></div>
        </td>
        <td data-label="SL đóng gói">
            <input type="number" class="form-control packed-qty-input" min="0" step="0.0001" value="0">
        </td>
        <td data-label="Đơn vị">
            <input type="text" class="form-control unit-name-input" placeholder="Cái, hộp, thùng...">
        </td>
        <td data-label="Ghi chú">
            <input type="text" class="form-control item-note-input" placeholder="Ghi chú sản phẩm">
        </td>
        <td class="text-center" data-label="#">
            <button type="button" class="btn btn-outline-secondary btn-sm remove-item-btn" title="Xóa sản phẩm">
                <i class="ti ti-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('packing-packages-root');
        const addPackageBtn = document.getElementById('add-package-btn');
        const packageTemplate = document.getElementById('package-template');
        const itemTemplate = document.getElementById('package-item-template');
        const initialPackages = @json($packages);
        const initialExportItems = @json($exportItemsData);
        const exportPreviewUrl = @json($exportPreviewUrl);
        const exportSelect = document.getElementById('packing-export-id');
        const warehouseSelect = document.getElementById('warehouse-id');
        const codeInput = document.querySelector('input[name="code"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const totalPackagesInput = document.getElementById('packing-total-packages');
        const previewPanel = document.querySelector('[data-packing-export-preview]');
        const isCreateScreen = /\/packing\/create$/.test(window.location.pathname);
        let exportItemsById = new Map();

        if (!root || !addPackageBtn || !packageTemplate || !itemTemplate) {
            return;
        }

        function initSelect2(scope) {
            if (!window.jQuery || !window.jQuery.fn.select2) {
                return;
            }

            window.jQuery(scope).find('.export-item-select').select2({
                width: '100%',
                placeholder: 'Chọn dòng phiếu xuất...',
            });
        }

        function destroySelect2(scope) {
            if (!window.jQuery) {
                return;
            }

            const $scope = window.jQuery(scope);

            $scope.find('.export-item-select').each(function () {
                const $select = window.jQuery(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
            });
        }

        function numberValue(selector, scope) {
            return parseFloat(scope.querySelector(selector)?.value || 0) || 0;
        }

        function updateTotals() {
            const packageCards = root.querySelectorAll('.package-card');
            const totalPackagesInput = document.querySelector('input[name="total_packages"]');
            const totalItemsInput = document.querySelector('input[name="total_items"]');
            const totalWeightInput = document.querySelector('input[name="total_weight"]');
            const totalVolumeInput = document.querySelector('input[name="total_volume"]');

            let totalItems = 0;
            let totalWeight = 0;
            let totalVolume = 0;

            packageCards.forEach(function (card) {
                const length = numberValue('.package-length-input', card);
                const width = numberValue('.package-width-input', card);
                const height = numberValue('.package-height-input', card);
                const volumeInput = card.querySelector('.package-volume-input');
                const volume = length * width * height;

                if (volumeInput) {
                    volumeInput.value = volume.toFixed(4);
                }

                totalWeight += numberValue('.package-weight-input', card);
                totalVolume += volume;

                card.querySelectorAll('.packed-qty-input').forEach(function (input) {
                    totalItems += parseFloat(input.value || 0) || 0;
                });
            });

            if (totalPackagesInput) {
                totalPackagesInput.value = packageCards.length;
            }

            if (totalItemsInput) {
                totalItemsInput.value = totalItems.toFixed(4);
            }

            if (totalWeightInput) {
                totalWeightInput.value = totalWeight.toFixed(4);
            }

            if (totalVolumeInput) {
                totalVolumeInput.value = totalVolume.toFixed(4);
            }
        }

        function reindexPackages() {
            root.querySelectorAll('.package-card').forEach(function (card, packageIndex) {
                const title = card.querySelector('.package-title');
                const itemsBody = card.querySelector('.package-items-body');

                if (title) {
                    title.textContent = 'Package #' + (packageIndex + 1);
                }

                const fieldMap = [
                    ['.package-id-input', 'id'],
                    ['.package-no-input', 'package_no'],
                    ['.package-code-input', 'package_code'],
                    ['.package-type-input', 'package_type_id'],
                    ['.package-status-input', 'status'],
                    ['.package-length-input', 'length'],
                    ['.package-width-input', 'width'],
                    ['.package-height-input', 'height'],
                    ['.package-dimension-unit-input', 'dimension_unit'],
                    ['.package-volume-input', 'volume'],
                    ['.package-volume-weight-input', 'volume_weight'],
                    ['.package-weight-input', 'weight'],
                    ['.package-weight-unit-input', 'weight_unit'],
                    ['.package-tracking-code-input', 'tracking_code'],
                    ['.package-shipping-label-url-input', 'shipping_label_url'],
                    ['.package-note-input', 'note'],
                ];

                fieldMap.forEach(function (entry) {
                    const input = card.querySelector(entry[0]);
                    if (input) {
                        input.name = 'packages[' + packageIndex + '][' + entry[1] + ']';
                    }
                });

                itemsBody.querySelectorAll('.package-item-row').forEach(function (row, itemIndex) {
                    const itemFieldMap = [
                        ['.item-id-input', 'id'],
                        ['.export-item-select', 'export_item_id'],
                        ['.product-id-input', 'product_id'],
                        ['.product-variation-id-input', 'product_variation_id'],
                        ['.product-code-input', 'product_code'],
                        ['.product-name-input', 'product_name'],
                        ['.packed-qty-input', 'packed_qty'],
                        ['.unit-id-input', 'unit_id'],
                        ['.unit-name-input', 'unit_name'],
                        ['.warehouse-location-id-input', 'warehouse_location_id'],
                        ['.pallet-id-input', 'pallet_id'],
                        ['.batch-id-input', 'batch_id'],
                        ['.goods-receipt-batch-id-input', 'goods_receipt_batch_id'],
                        ['.stock-balance-id-input', 'stock_balance_id'],
                        ['.storage-item-id-input', 'storage_item_id'],
                        ['.lot-no-input', 'lot_no'],
                        ['.expiry-date-input', 'expiry_date'],
                        ['.item-note-input', 'note'],
                    ];

                    itemFieldMap.forEach(function (entry) {
                        const input = row.querySelector(entry[0]);
                        if (input) {
                            input.name = 'packages[' + packageIndex + '][items][' + itemIndex + '][' + entry[1] + ']';
                        }
                    });
                });
            });

            updateTotals();
            syncProductEditLock();
        }

        function setValue(row, selector, value) {
            const input = row.querySelector(selector);

            if (input) {
                input.value = value || '';
            }
        }

        function syncControlledHidden(select, fieldName, locked) {
            if (!select) {
                return;
            }

            let hidden = document.querySelector('input[data-packing-hidden="' + fieldName + '"]');

            if (locked) {
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = fieldName;
                    hidden.dataset.packingHidden = fieldName;
                    select.insertAdjacentElement('afterend', hidden);
                }

                hidden.value = select.value || '';
                select.disabled = true;

                return;
            }

            select.disabled = false;

            if (hidden) {
                hidden.remove();
            }
        }

        function syncControlledFields() {
            const locked = Boolean(isCreateScreen && exportSelect?.value);

            syncControlledHidden(warehouseSelect, 'warehouse_id', locked);
            syncControlledHidden(statusSelect, 'status', locked);

            if (locked && statusSelect) {
                statusSelect.value = 'packing';
                syncControlledHidden(statusSelect, 'status', true);
            }
        }

        function formatItemQuantity(value) {
            const qty = parseFloat(value || 0) || 0;

            return qty.toLocaleString('vi-VN', {
                maximumFractionDigits: 4,
            });
        }

        function appendSummaryMeta(summary, label, value) {
            if (!value && value !== 0) {
                return;
            }

            const item = document.createElement('span');
            item.textContent = label + ': ' + value;
            summary.appendChild(item);
        }

        function renderItemSummary(row, item) {
            const summary = row?.querySelector('[data-package-item-summary]');

            if (!summary) {
                return;
            }

            summary.replaceChildren();

            if (!item) {
                summary.hidden = true;

                return;
            }

            const title = document.createElement('div');
            title.className = 'package-item-summary__title';
            title.textContent = [item.product_code, item.product_name].filter(Boolean).join(' - ')
                || item.label
                || 'Dòng phiếu xuất #' + item.id;

            const meta = document.createElement('div');
            meta.className = 'package-item-summary__meta';

            appendSummaryMeta(meta, 'SL chứng từ', formatItemQuantity(item.document_qty));
            appendSummaryMeta(meta, 'Đã đóng', formatItemQuantity(item.packed_qty));
            appendSummaryMeta(meta, 'Còn lại', formatItemQuantity(item.remaining_qty));
            appendSummaryMeta(meta, 'Đơn vị', item.unit_name);
            appendSummaryMeta(meta, 'Vị trí', item.warehouse_location_id);
            appendSummaryMeta(meta, 'Pallet', item.pallet_id);
            appendSummaryMeta(meta, 'Lot', item.lot_no);
            appendSummaryMeta(meta, 'HSD', item.expiry_date);

            summary.appendChild(title);

            if (meta.childElementCount) {
                summary.appendChild(meta);
            }

            summary.hidden = false;
        }

        function packedQuantitiesByExportItem() {
            const quantities = new Map();

            root.querySelectorAll('.package-item-row').forEach(function (row) {
                const exportItemId = String(row.querySelector('.export-item-select')?.value || '');
                const qty = parseFloat(row.querySelector('.packed-qty-input')?.value || 0) || 0;

                if (!exportItemId || qty <= 0) {
                    return;
                }

                quantities.set(exportItemId, (quantities.get(exportItemId) || 0) + qty);
            });

            return quantities;
        }

        function currentPackingCoversExportItems() {
            if (!exportItemsById.size) {
                return false;
            }

            const quantities = packedQuantitiesByExportItem();
            let hasRequiredQty = false;
            let isComplete = true;

            exportItemsById.forEach(function (item, id) {
                const requiredQty = parseFloat(item.remaining_qty || item.document_qty || 0) || 0;

                if (requiredQty <= 0) {
                    return;
                }

                hasRequiredQty = true;

                if ((quantities.get(String(id)) || 0) + 0.0001 < requiredQty) {
                    isComplete = false;
                }
            });

            return hasRequiredQty && isComplete;
        }

        function remainingQtyForRow(row, item) {
            if (!item) {
                return 0;
            }

            const exportItemId = String(item.id || row?.querySelector('.export-item-select')?.value || '');
            const requiredQty = parseFloat(item.remaining_qty || item.document_qty || 0) || 0;
            let usedQty = 0;

            root.querySelectorAll('.package-item-row').forEach(function (otherRow) {
                if (otherRow === row) {
                    return;
                }

                if (String(otherRow.querySelector('.export-item-select')?.value || '') !== exportItemId) {
                    return;
                }

                usedQty += parseFloat(otherRow.querySelector('.packed-qty-input')?.value || 0) || 0;
            });

            return Math.max(requiredQty - usedQty, 0);
        }

        function syncRowSelectionState(row, forceQuantity) {
            if (!row) {
                return;
            }

            const checkbox = row.querySelector('.package-item-check');
            const qtyInput = row.querySelector('.packed-qty-input');
            const item = exportItemsById.get(String(row.querySelector('.export-item-select')?.value || ''));
            const checked = Boolean(checkbox?.checked);

            row.classList.toggle('is-selected', checked);

            if (!qtyInput) {
                return;
            }

            qtyInput.disabled = !checked;

            if (!checked) {
                qtyInput.value = 0;

                return;
            }

            if (forceQuantity && (parseFloat(qtyInput.value || 0) || 0) <= 0) {
                qtyInput.value = remainingQtyForRow(row, item).toFixed(4);
            }
        }

        function syncStatusWithCompleteness() {
            if (!isCreateScreen || !statusSelect || statusSelect.value === 'cancelled') {
                return;
            }

            if (exportSelect?.value) {
                statusSelect.value = 'packing';
                syncControlledHidden(statusSelect, 'status', true);
            }
        }

        function productRowsAreLocked() {
            return Boolean(exportSelect?.value && exportItemsById.size);
        }

        function syncProductEditLock() {
            const locked = productRowsAreLocked();

            root.classList.toggle('packing-products-locked', locked);
            addPackageBtn.disabled = locked;
            addPackageBtn.title = locked
                ? 'Sản phẩm đã lấy từ phiếu xuất, không thêm package/sản phẩm thủ công.'
                : '';

            root.querySelectorAll('.add-item-btn, .remove-item-btn, .remove-package-btn').forEach(function (button) {
                button.disabled = locked;
                button.title = locked
                    ? 'Sản phẩm đã lấy từ phiếu xuất, không cho thêm/xóa dòng hàng.'
                    : '';
            });

            root.querySelectorAll('.export-item-select').forEach(function (select) {
                select.dataset.locked = locked ? '1' : '';
            });
        }

        function setExportItems(items) {
            exportItemsById = new Map();

            (Array.isArray(items) ? items : []).forEach(function (item) {
                if (item?.id) {
                    exportItemsById.set(String(item.id), item);
                }
            });
        }

        function populateExportItemSelect(select, selectedValue) {
            if (!select) {
                return;
            }

            const currentValue = selectedValue ? String(selectedValue) : String(select.value || '');

            select.innerHTML = '<option value="">Chọn dòng phiếu xuất...</option>';

            exportItemsById.forEach(function (item, id) {
                const option = new Option(item.label || ('Dòng #' + id), id);
                select.appendChild(option);
            });

            if (currentValue && exportItemsById.has(currentValue)) {
                select.value = currentValue;
            }
        }

        function refreshExportItemSelects() {
            destroySelect2(root);

            root.querySelectorAll('.export-item-select').forEach(function (select) {
                populateExportItemSelect(select, select.value);
            });

            initSelect2(root);
        }

        function applyExportItemToRow(row, item, forceQuantity) {
            if (!row || !item) {
                return;
            }

            setValue(row, '.product-id-input', item.product_id);
            setValue(row, '.product-variation-id-input', item.product_variation_id);
            setValue(row, '.product-code-input', item.product_code);
            setValue(row, '.product-name-input', item.product_name);
            setValue(row, '.unit-id-input', item.unit_id);
            setValue(row, '.warehouse-location-id-input', item.warehouse_location_id);
            setValue(row, '.pallet-id-input', item.pallet_id);
            setValue(row, '.batch-id-input', item.batch_id);
            setValue(row, '.goods-receipt-batch-id-input', item.goods_receipt_batch_id);
            setValue(row, '.stock-balance-id-input', item.stock_balance_id);
            setValue(row, '.storage-item-id-input', item.storage_item_id);
            setValue(row, '.lot-no-input', item.lot_no);
            setValue(row, '.expiry-date-input', item.expiry_date);
            setValue(row, '.unit-name-input', item.unit_name);
            renderItemSummary(row, item);

            if (forceQuantity) {
                const qty = parseFloat(item.remaining_qty || item.document_qty || 0) || 0;
                row.querySelector('.packed-qty-input').value = qty > 0 ? qty : 1;
            }
        }

        function packageItemFromExportItem(item, selected) {
            const qty = selected ? (parseFloat(item.remaining_qty || item.document_qty || 0) || 0) : 0;

            return {
                export_item_id: item.id,
                product_id: item.product_id,
                product_variation_id: item.product_variation_id,
                product_code: item.product_code,
                product_name: item.product_name,
                packed_qty: qty,
                is_selected: selected,
                unit_id: item.unit_id,
                unit_name: item.unit_name,
                warehouse_location_id: item.warehouse_location_id,
                pallet_id: item.pallet_id,
                batch_id: item.batch_id,
                goods_receipt_batch_id: item.goods_receipt_batch_id,
                stock_balance_id: item.stock_balance_id,
                storage_item_id: item.storage_item_id,
                lot_no: item.lot_no,
                expiry_date: item.expiry_date,
                note: item.note,
            };
        }

        function createItemRow(itemData) {
            const fragment = itemTemplate.content.cloneNode(true);
            const row = fragment.querySelector('.package-item-row');

            if (!row) {
                return null;
            }

            setValue(row, '.item-id-input', itemData?.id);
            populateExportItemSelect(row.querySelector('.export-item-select'), itemData?.export_item_id);
            setValue(row, '.product-id-input', itemData?.product_id);
            setValue(row, '.product-variation-id-input', itemData?.product_variation_id);
            setValue(row, '.product-code-input', itemData?.product_code);
            setValue(row, '.product-name-input', itemData?.product_name);
            setValue(row, '.unit-id-input', itemData?.unit_id);
            setValue(row, '.warehouse-location-id-input', itemData?.warehouse_location_id);
            setValue(row, '.pallet-id-input', itemData?.pallet_id);
            setValue(row, '.batch-id-input', itemData?.batch_id);
            setValue(row, '.goods-receipt-batch-id-input', itemData?.goods_receipt_batch_id);
            setValue(row, '.stock-balance-id-input', itemData?.stock_balance_id);
            setValue(row, '.storage-item-id-input', itemData?.storage_item_id);
            setValue(row, '.lot-no-input', itemData?.lot_no);
            setValue(row, '.expiry-date-input', itemData?.expiry_date);
            setValue(row, '.unit-name-input', itemData?.unit_name);
            setValue(row, '.item-note-input', itemData?.note);

            row.querySelector('.packed-qty-input').value = itemData?.packed_qty ?? (itemData?.export_item_id || itemData?.product_id ? 1 : 0);
            row.querySelector('.package-item-check').checked = Boolean(itemData?.is_selected || (parseFloat(itemData?.packed_qty || 0) > 0));

            const selectedExportItem = exportItemsById.get(String(itemData?.export_item_id || ''));

            if (selectedExportItem && !itemData?.product_id) {
                applyExportItemToRow(row, selectedExportItem, false);
            } else if (selectedExportItem) {
                renderItemSummary(row, selectedExportItem);
            } else if (itemData?.product_id || itemData?.product_name) {
                renderItemSummary(row, itemData);
            }

            syncRowSelectionState(row, false);

            return row;
        }

        function addItem(card, itemData) {
            const itemsBody = card.querySelector('.package-items-body');
            const row = createItemRow(itemData || {});

            if (!itemsBody || !row) {
                return;
            }

            itemsBody.appendChild(row);
            initSelect2(row);
            reindexPackages();
        }

        function createPackageCard(packageData) {
            const fragment = packageTemplate.content.cloneNode(true);
            const card = fragment.querySelector('.package-card');

            if (!card) {
                return null;
            }

            card.querySelector('.package-id-input').value = packageData?.id || '';
            card.querySelector('.package-no-input').value = packageData?.package_no || 1;
            card.querySelector('.package-code-input').value = packageData?.package_code || '';
            card.querySelector('.package-type-input').value = packageData?.package_type_id || packageData?.package_type || '';
            card.querySelector('.package-status-input').value = packageData?.status || 'open';
            card.querySelector('.package-length-input').value = packageData?.length || 0;
            card.querySelector('.package-width-input').value = packageData?.width || 0;
            card.querySelector('.package-height-input').value = packageData?.height || 0;
            card.querySelector('.package-dimension-unit-input').value = packageData?.dimension_unit || 'cm';
            card.querySelector('.package-volume-input').value = packageData?.volume || 0;
            card.querySelector('.package-volume-weight-input').value = packageData?.volume_weight || 0;
            card.querySelector('.package-weight-input').value = packageData?.weight || 0;
            card.querySelector('.package-weight-unit-input').value = packageData?.weight_unit || 'kg';
            card.querySelector('.package-tracking-code-input').value = packageData?.tracking_code || '';
            card.querySelector('.package-shipping-label-url-input').value = packageData?.shipping_label_url || '';
            card.querySelector('.package-note-input').value = packageData?.note || '';

            const items = Array.isArray(packageData?.items) && packageData.items.length
                ? packageData.items
                : (exportItemsById.size ? Array.from(exportItemsById.values()).map(function (item) {
                    return packageItemFromExportItem(item, false);
                }) : [{}]);

            items.forEach(function (item) {
                addItem(card, item);
            });

            return card;
        }

        function addPackage(packageData) {
            const card = createPackageCard(packageData || {});

            if (!card) {
                return;
            }

            root.appendChild(card);
            initSelect2(card);
            reindexPackages();
        }

        function packageCode(packageNo) {
            return 'PKG-' + String(packageNo).padStart(3, '0');
        }

        function packageItemsFromExport(selected) {
            return Array.from(exportItemsById.values()).map(function (item) {
                return packageItemFromExportItem(item, selected);
            });
        }

        function createDefaultPackageData(packageNo, selectedItems, exportData) {
            return {
                package_no: packageNo,
                package_code: packageCode(packageNo),
                package_type_id: 'pallet',
                weight_unit: 'kg',
                dimension_unit: 'cm',
                status: 'open',
                tracking_code: exportData?.tracking_code || '',
                items: exportItemsById.size ? packageItemsFromExport(selectedItems) : [{}],
            };
        }

        function packageCountValue() {
            return Math.max(parseInt(totalPackagesInput?.value || 1, 10) || 1, 1);
        }

        function syncPackageCountFromInput(exportData) {
            if (!totalPackagesInput) {
                return;
            }

            const desiredCount = packageCountValue();
            let cards = root.querySelectorAll('.package-card');

            while (cards.length > desiredCount) {
                const card = cards[cards.length - 1];
                destroySelect2(card);
                card.remove();
                cards = root.querySelectorAll('.package-card');
            }

            while (cards.length < desiredCount) {
                addPackage(createDefaultPackageData(cards.length + 1, false, exportData));
                cards = root.querySelectorAll('.package-card');
            }

            reindexPackages();
        }

        function text(value, fallback) {
            return value ? String(value) : (fallback || '-');
        }

        function setPreviewValue(selector, value) {
            const element = previewPanel?.querySelector(selector);

            if (element) {
                element.textContent = text(value);
            }
        }

        function renderExportPreview(payload) {
            const data = payload?.export || {};

            if (!previewPanel || !data.id) {
                return;
            }

            previewPanel.hidden = false;
            setPreviewValue('[data-export-preview-code]', data.code);
            setPreviewValue('[data-export-preview-status]', data.status ? ('Trạng thái: ' + data.status) : '-');
            setPreviewValue('[data-export-preview-warehouse]', [data.warehouse_code, data.warehouse_name].filter(Boolean).join(' - '));
            setPreviewValue('[data-export-preview-partner]', [data.partner_code, data.partner_name, data.partner_phone].filter(Boolean).join(' - '));
            setPreviewValue('[data-export-preview-receiver]', [data.receiver_name, data.receiver_phone].filter(Boolean).join(' - '));
            setPreviewValue('[data-export-preview-shipping]', [data.shipping_unit, data.tracking_code].filter(Boolean).join(' - '));
            setPreviewValue('[data-export-preview-note]', data.receiver_address || data.partner_address || data.note || '');
        }

        function resetPackagesFromExport(payload) {
            destroySelect2(root);
            root.innerHTML = '';

            const desiredCount = packageCountValue();

            for (let packageNo = 1; packageNo <= desiredCount; packageNo++) {
                addPackage(createDefaultPackageData(packageNo, packageNo === 1, payload?.export || {}));
            }
        }

        async function loadExportPreview(exportId, options) {
            if (!exportId || !exportPreviewUrl) {
                return;
            }

            const url = exportPreviewUrl.replace('__EXPORT_ID__', encodeURIComponent(exportId));

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const result = await response.json();
            const payload = result.data || {};

            setExportItems(payload.items || []);
            refreshExportItemSelects();
            renderExportPreview(payload);

            if (payload.export?.warehouse_id && warehouseSelect) {
                warehouseSelect.value = payload.export.warehouse_id;
                warehouseSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            if (totalPackagesInput && (!totalPackagesInput.value || parseInt(totalPackagesInput.value, 10) <= 0)) {
                totalPackagesInput.value = 1;
            }

            if (codeInput && !codeInput.value && payload.suggested_packing_code) {
                codeInput.value = payload.suggested_packing_code;
            }

            if (statusSelect) {
                statusSelect.value = 'packing';
            }

            syncControlledFields();

            if (options?.resetPackages) {
                resetPackagesFromExport(payload);
            }

            syncStatusWithCompleteness();
            syncProductEditLock();
        }

        addPackageBtn.addEventListener('click', function () {
            if (productRowsAreLocked()) {
                return;
            }

            addPackage({
                package_no: root.querySelectorAll('.package-card').length + 1,
                weight_unit: 'kg',
                dimension_unit: 'cm',
                status: 'open',
                items: [{}],
            });
        });

        root.addEventListener('click', function (event) {
            const removePackageBtn = event.target.closest('.remove-package-btn');
            const addItemBtn = event.target.closest('.add-item-btn');
            const removeItemBtn = event.target.closest('.remove-item-btn');

            if (removePackageBtn) {
                if (productRowsAreLocked()) {
                    return;
                }

                const card = removePackageBtn.closest('.package-card');

                if (card) {
                    destroySelect2(card);
                    card.remove();
                    reindexPackages();
                    syncStatusWithCompleteness();
                }
            }

            if (addItemBtn) {
                if (productRowsAreLocked()) {
                    return;
                }

                const card = addItemBtn.closest('.package-card');

                if (card) {
                    addItem(card, {});
                    syncStatusWithCompleteness();
                }
            }

            if (removeItemBtn) {
                if (productRowsAreLocked()) {
                    return;
                }

                const row = removeItemBtn.closest('.package-item-row');
                const card = removeItemBtn.closest('.package-card');

                if (!row || !card) {
                    return;
                }

                const rows = card.querySelectorAll('.package-item-row');

                if (rows.length <= 1) {
                    row.querySelectorAll('input').forEach(function (input) {
                        input.value = '';
                    });
                    row.querySelector('.export-item-select').value = '';
                    row.querySelector('.packed-qty-input').value = 0;
                    renderItemSummary(row, null);
                    reindexPackages();
                    syncStatusWithCompleteness();
                    return;
                }

                destroySelect2(row);
                row.remove();
                reindexPackages();
                syncStatusWithCompleteness();
            }
        });

        root.addEventListener('change', function (event) {
            if (event.target.classList.contains('package-item-check')) {
                const row = event.target.closest('.package-item-row');

                syncRowSelectionState(row, event.target.checked);
                reindexPackages();
                syncStatusWithCompleteness();

                return;
            }

            if (!event.target.classList.contains('export-item-select')) {
                return;
            }

            if (event.target.dataset.locked === '1') {
                return;
            }

            const row = event.target.closest('.package-item-row');
            const item = exportItemsById.get(String(event.target.value || ''));

            if (item) {
                applyExportItemToRow(row, item, true);
            } else {
                renderItemSummary(row, null);
            }

            reindexPackages();
            syncStatusWithCompleteness();
        });

        root.addEventListener('input', function (event) {
            if (
                event.target.classList.contains('package-weight-input')
                || event.target.classList.contains('package-length-input')
                || event.target.classList.contains('package-width-input')
                || event.target.classList.contains('package-height-input')
                || event.target.classList.contains('packed-qty-input')
            ) {
                updateTotals();

                if (event.target.classList.contains('packed-qty-input')) {
                    syncStatusWithCompleteness();
                }
            }
        });

        if (exportSelect) {
            exportSelect.addEventListener('change', function () {
                if (!this.value) {
                    setExportItems([]);
                    syncControlledFields();
                    syncProductEditLock();

                    return;
                }

                loadExportPreview(this.value, { resetPackages: true });
            });
        }

        if (totalPackagesInput) {
            totalPackagesInput.addEventListener('input', function () {
                syncPackageCountFromInput();
                syncStatusWithCompleteness();
            });
        }

        setExportItems(initialExportItems);

        if (initialPackages.length) {
            initialPackages.forEach(function (pkg) {
                addPackage(pkg);
            });
        } else {
            addPackage({
                package_no: 1,
                weight_unit: 'kg',
                dimension_unit: 'cm',
                status: 'open',
                items: [{}],
            });
        }

        if (exportSelect?.value) {
            loadExportPreview(exportSelect.value, { resetPackages: !initialPackages.length });
        } else {
            refreshExportItemSelects();
            syncStatusWithCompleteness();
        }
    });
</script>
