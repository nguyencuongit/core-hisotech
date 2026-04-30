@php
    $packages = is_array($packages ?? null) ? $packages : [];
    $exportItems = is_array($exportItems ?? null) ? $exportItems : [];
@endphp

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">Danh sach package</h5>
                <div class="text-muted">Chon dong hang cua phieu xuat, sau do nhap so luong dong goi.</div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" id="add-package-btn">
                <i class="fa fa-plus"></i> Them package
            </button>
        </div>

        <div id="packing-packages-root"></div>
    </div>
</div>

<template id="package-template">
    <div class="card border mb-3 package-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold package-title">Package</div>
            <button type="button" class="btn btn-danger btn-sm remove-package-btn">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <div class="card-body">
            <input type="hidden" class="package-id-input">
            <div class="row">
                <div class="form-group col-md-2">
                    <label>So kien</label>
                    <input type="number" class="form-control package-no-input" min="1" step="1" value="1">
                </div>
                <div class="form-group col-md-3">
                    <label>Ma kien</label>
                    <input type="text" class="form-control package-code-input" placeholder="PKG-001">
                </div>
                <div class="form-group col-md-2">
                    <label>Loai kien</label>
                    <select class="form-control package-type-input">
                        <option value="">Chon loai</option>
                        <option value="box">Box</option>
                        <option value="pallet">Pallet</option>
                        <option value="bag">Bag</option>
                        <option value="crate">Crate</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Trang thai kien</label>
                    <select class="form-control package-status-input">
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Tracking code</label>
                    <input type="text" class="form-control package-tracking-code-input" placeholder="Tracking code">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-1">
                    <label>Dai</label>
                    <input type="number" class="form-control package-length-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-md-1">
                    <label>Rong</label>
                    <input type="number" class="form-control package-width-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-md-1">
                    <label>Cao</label>
                    <input type="number" class="form-control package-height-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-md-2">
                    <label>Don vi KT</label>
                    <select class="form-control package-dimension-unit-input">
                        <option value="cm">cm</option>
                        <option value="m">m</option>
                        <option value="mm">mm</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>The tich</label>
                    <input type="number" class="form-control package-volume-input" min="0" step="0.0001" value="0" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label>Trong luong</label>
                    <input type="number" class="form-control package-weight-input" min="0" step="0.0001" value="0">
                </div>
                <div class="form-group col-md-1">
                    <label>Don vi KL</label>
                    <select class="form-control package-weight-unit-input">
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="lb">lb</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Volume weight</label>
                    <input type="number" class="form-control package-volume-weight-input" min="0" step="0.0001" value="0">
                </div>
            </div>

            <div class="form-group">
                <label>Shipping label URL</label>
                <input type="text" class="form-control package-shipping-label-url-input" placeholder="https://...">
            </div>

            <div class="form-group">
                <label>Ghi chu package</label>
                <textarea class="form-control package-note-input" rows="2" placeholder="Ghi chu"></textarea>
            </div>

            <div class="border rounded p-3 bg-light-subtle">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">San pham trong package</h6>
                    <button type="button" class="btn btn-info btn-sm add-item-btn">
                        <i class="fa fa-plus"></i> Them san pham
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 package-items-table">
                        <thead>
                            <tr>
                                <th style="min-width: 360px;">Dong phieu xuat</th>
                                <th style="min-width: 140px;">So luong dong goi</th>
                                <th style="min-width: 140px;">Don vi</th>
                                <th style="min-width: 220px;">Ghi chu</th>
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
        <td>
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
                <option value="">Chon dong phieu xuat...</option>
                @foreach($exportItems as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" class="form-control packed-qty-input" min="0" step="0.0001" value="1">
        </td>
        <td>
            <input type="text" class="form-control unit-name-input" placeholder="Cai, hop, thung...">
        </td>
        <td>
            <input type="text" class="form-control item-note-input" placeholder="Ghi chu san pham">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                <i class="fa fa-trash"></i>
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

        if (!root || !addPackageBtn || !packageTemplate || !itemTemplate) {
            return;
        }

        function initSelect2(scope) {
            if (!window.jQuery || !window.jQuery.fn.select2) {
                return;
            }

            window.jQuery(scope).find('.export-item-select').select2({
                width: '100%',
                placeholder: 'Chon dong phieu xuat...',
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
        }

        function setValue(row, selector, value) {
            const input = row.querySelector(selector);

            if (input) {
                input.value = value || '';
            }
        }

        function createItemRow(itemData) {
            const fragment = itemTemplate.content.cloneNode(true);
            const row = fragment.querySelector('.package-item-row');

            if (!row) {
                return null;
            }

            setValue(row, '.item-id-input', itemData?.id);
            setValue(row, '.export-item-select', itemData?.export_item_id);
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

            row.querySelector('.packed-qty-input').value = itemData?.packed_qty || 1;

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

            const items = Array.isArray(packageData?.items) && packageData.items.length ? packageData.items : [{}];

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

        addPackageBtn.addEventListener('click', function () {
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
                const card = removePackageBtn.closest('.package-card');

                if (card) {
                    destroySelect2(card);
                    card.remove();
                    reindexPackages();
                }
            }

            if (addItemBtn) {
                const card = addItemBtn.closest('.package-card');

                if (card) {
                    addItem(card, {});
                }
            }

            if (removeItemBtn) {
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
                    row.querySelector('.packed-qty-input').value = 1;
                    reindexPackages();
                    return;
                }

                destroySelect2(row);
                row.remove();
                reindexPackages();
            }
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
            }
        });

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
    });
</script>
