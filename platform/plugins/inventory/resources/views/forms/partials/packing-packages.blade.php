@php
    $packages = is_array($packages ?? null) ? $packages : [];
    $products = is_array($products ?? null) ? $products : [];
@endphp

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">Danh sach package</h5>
                <div class="text-muted">Tao package truoc, sau do them san pham vao tung package.</div>
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
            <div class="row">
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
                <div class="form-group col-md-1">
                    <label>Dai</label>
                    <input type="number" class="form-control package-length-input" min="0" step="0.01" value="0">
                </div>
                <div class="form-group col-md-1">
                    <label>Rong</label>
                    <input type="number" class="form-control package-width-input" min="0" step="0.01" value="0">
                </div>
                <div class="form-group col-md-1">
                    <label>Cao</label>
                    <input type="number" class="form-control package-height-input" min="0" step="0.01" value="0">
                </div>
                <div class="form-group col-md-2">
                    <label>Trong luong</label>
                    <input type="number" class="form-control package-weight-input" min="0" step="0.01" value="0">
                </div>
                <div class="form-group col-md-2">
                    <label>Don vi KL</label>
                    <select class="form-control package-weight-unit-input">
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="lb">lb</option>
                    </select>
                </div>
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
                                <th style="min-width: 320px;">San pham</th>
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
            <select class="form-control product-select">
                <option value="">Chon san pham...</option>
                @foreach($products as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" class="form-control packed-qty-input" min="0" step="0.01" value="1">
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

            window.jQuery(scope).find('.product-select').select2({
                width: '100%',
                placeholder: 'Chon san pham...',
            });
        }

        function destroySelect2(scope) {
            if (!window.jQuery) {
                return;
            }

            const $scope = window.jQuery(scope);

            $scope.find('.product-select').each(function () {
                const $select = window.jQuery(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
            });
        }

        function updateTotals() {
            const packageCards = root.querySelectorAll('.package-card');
            const totalPackagesInput = document.querySelector('input[name="total_packages"]');
            const totalWeightInput = document.querySelector('input[name="total_weight"]');

            let totalWeight = 0;

            packageCards.forEach(function (card) {
                const weight = parseFloat(card.querySelector('.package-weight-input')?.value || 0);
                totalWeight += weight;
            });

            if (totalPackagesInput) {
                totalPackagesInput.value = packageCards.length;
            }

            if (totalWeightInput) {
                totalWeightInput.value = totalWeight.toFixed(2);
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
                    ['.package-code-input', 'package_code'],
                    ['.package-type-input', 'package_type'],
                    ['.package-length-input', 'length'],
                    ['.package-width-input', 'width'],
                    ['.package-height-input', 'height'],
                    ['.package-weight-input', 'weight'],
                    ['.package-weight-unit-input', 'weight_unit'],
                    ['.package-note-input', 'note'],
                ];

                fieldMap.forEach(function (entry) {
                    const input = card.querySelector(entry[0]);
                    if (input) {
                        input.name = 'packages[' + packageIndex + '][' + entry[1] + ']';
                    }
                });

                itemsBody.querySelectorAll('.package-item-row').forEach(function (row, itemIndex) {
                    const product = row.querySelector('.product-select');
                    const qty = row.querySelector('.packed-qty-input');
                    const unit = row.querySelector('.unit-name-input');
                    const note = row.querySelector('.item-note-input');

                    if (product) {
                        product.name = 'packages[' + packageIndex + '][items][' + itemIndex + '][product_id]';
                    }

                    if (qty) {
                        qty.name = 'packages[' + packageIndex + '][items][' + itemIndex + '][packed_qty]';
                    }

                    if (unit) {
                        unit.name = 'packages[' + packageIndex + '][items][' + itemIndex + '][unit_name]';
                    }

                    if (note) {
                        note.name = 'packages[' + packageIndex + '][items][' + itemIndex + '][note]';
                    }
                });
            });

            updateTotals();
        }

        function createItemRow(itemData) {
            const fragment = itemTemplate.content.cloneNode(true);
            const row = fragment.querySelector('.package-item-row');

            if (!row) {
                return null;
            }

            row.querySelector('.product-select').value = itemData?.product_id || '';
            row.querySelector('.packed-qty-input').value = itemData?.packed_qty || 1;
            row.querySelector('.unit-name-input').value = itemData?.unit_name || '';
            row.querySelector('.item-note-input').value = itemData?.note || '';

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

            card.querySelector('.package-code-input').value = packageData?.package_code || '';
            card.querySelector('.package-type-input').value = packageData?.package_type || '';
            card.querySelector('.package-length-input').value = packageData?.length || 0;
            card.querySelector('.package-width-input').value = packageData?.width || 0;
            card.querySelector('.package-height-input').value = packageData?.height || 0;
            card.querySelector('.package-weight-input').value = packageData?.weight || 0;
            card.querySelector('.package-weight-unit-input').value = packageData?.weight_unit || 'kg';
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
                weight_unit: 'kg',
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
                    row.querySelector('.product-select').value = '';
                    row.querySelector('.packed-qty-input').value = 1;
                    row.querySelector('.unit-name-input').value = '';
                    row.querySelector('.item-note-input').value = '';
                    reindexPackages();
                    return;
                }

                destroySelect2(row);
                row.remove();
                reindexPackages();
            }
        });

        root.addEventListener('input', function (event) {
            if (event.target.classList.contains('package-weight-input')) {
                updateTotals();
            }
        });

        if (initialPackages.length) {
            initialPackages.forEach(function (pkg) {
                addPackage(pkg);
            });
        } else {
            addPackage({
                weight_unit: 'kg',
                items: [{}],
            });
        }
    });
</script>
