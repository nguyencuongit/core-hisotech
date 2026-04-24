<div>
    <h2>Product</h2>

    <div id="product-errors" class="alert alert-danger d-none mt-2"></div>

    <div class="table-responsive overflow-auto" style="max-width: 100%; max-height: 520px;">
        <table class="table table-bordered align-middle" id="document-items-table" style="min-width: 1200px;">
            <thead>
                <tr>
                    <th style="min-width: 420px;">Sản phẩm</th>
                    <th style="min-width: 160px;">Số lượng</th>
                    <th style="min-width: 180px;">Đơn giá</th>
                    <th style="min-width: 180px;">Tổng tiền</th>
                    <th style="min-width: 260px;">Vị trí lấy hàng</th>
                    <th style="min-width: 260px;">Số lượng từng vị trí</th>
                    <th style="min-width: 260px;">Ghi chú</th>
                    <th style="min-width: 60px;">#</th>
                </tr>
            </thead>

            <tbody>
                <tr class="item-row">
                    <td>
                        <select name="items[0][product_id]" class="form-control select-search">
                            <option value="">Chọn sản phẩm...</option>

                            {{-- Demo option. Sau này loop sản phẩm của m vào đây --}}
                            <option value="1">Chọn SP</option>
                        </select>
                    </td>

                    <td>
                        <input
                            type="number"
                            name="items[0][qty]"
                            class="form-control js-qty"
                            value="1"
                            min="1"
                            step="1"
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="items[0][unit_cost]"
                            class="form-control js-unit_cost"
                            value="0"
                            min="0"
                            step="0.01"
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="items[0][line_total]"
                            class="form-control js-line-total"
                            value="0"
                            readonly
                        >
                    </td>

                    <td>
                        <select
                            name="items[0][levels][]"
                            class="form-control form-level-select"
                            multiple
                            style="width: 100%;"
                        >
                        </select>
                    </td>

                    <td>
                        <div class="js-level-qty-wrap"></div>
                    </td>

                    <td>
                        <input
                            type="text"
                            name="items[0][note]"
                            class="form-control"
                            value=""
                            placeholder="Ghi chú"
                        >
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>

                <tr class="js-total-row">
                    <td></td>
                    <td>
                        <strong>Tổng: <span class="js-total-qty">1</span></strong>
                    </td>
                    <td></td>
                    <td>
                        <strong><span class="js-total-money">0</span></strong>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div>
            <button type="button" class="btn btn-info btn-sm" id="add-item">
                <i class="fa fa-plus"></i> Thêm sản phẩm
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('document-items-table');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const addBtn = document.getElementById('add-item');

        function getItemRows() {
            return tbody.querySelectorAll('.item-row');
        }

        function getTotalRow() {
            return tbody.querySelector('.js-total-row');
        }

        function formatNumber(value) {
            const number = Number(value || 0);
            return number.toLocaleString('vi-VN');
        }

        function calculateRow(row) {
            const qtyInput = row.querySelector('.js-qty');
            const unitCostInput = row.querySelector('.js-unit_cost');
            const lineTotalInput = row.querySelector('.js-line-total');

            const qty = parseFloat(qtyInput?.value || 0);
            const unitCost = parseFloat(unitCostInput?.value || 0);
            const total = qty * unitCost;

            if (lineTotalInput) {
                lineTotalInput.value = total;
            }

            return {
                qty: qty,
                total: total,
            };
        }

        function updateTotals() {
            let totalQty = 0;
            let totalMoney = 0;

            getItemRows().forEach(function (row) {
                const result = calculateRow(row);
                totalQty += result.qty;
                totalMoney += result.total;
            });

            const totalQtyEl = tbody.querySelector('.js-total-qty');
            const totalMoneyEl = tbody.querySelector('.js-total-money');

            if (totalQtyEl) {
                totalQtyEl.textContent = formatNumber(totalQty);
            }

            if (totalMoneyEl) {
                totalMoneyEl.textContent = formatNumber(totalMoney);
            }
        }

        function resetRow(row) {
            const productSelect = row.querySelector('.select-search');
            const qtyInput = row.querySelector('.js-qty');
            const unitCostInput = row.querySelector('.js-unit_cost');
            const lineTotalInput = row.querySelector('.js-line-total');
            const noteInput = row.querySelector('input[name$="[note]"]');
            const levelSelect = row.querySelector('.form-level-select');
            const levelQtyWrap = row.querySelector('.js-level-qty-wrap');

            if (productSelect) {
                productSelect.value = '';
            }

            if (qtyInput) {
                qtyInput.value = 1;
            }

            if (unitCostInput) {
                unitCostInput.value = 0;
            }

            if (lineTotalInput) {
                lineTotalInput.value = 0;
            }

            if (noteInput) {
                noteInput.value = '';
            }

            if (levelSelect) {
                levelSelect.innerHTML = '';
                levelSelect.value = null;
            }

            if (levelQtyWrap) {
                levelQtyWrap.innerHTML = '';
            }
        }

        function reindexRows() {
            getItemRows().forEach(function (row, index) {
                const productSelect = row.querySelector('.select-search');
                const qtyInput = row.querySelector('.js-qty');
                const unitCostInput = row.querySelector('.js-unit_cost');
                const lineTotalInput = row.querySelector('.js-line-total');
                const noteInput = row.querySelector('input[type="text"]');
                const levelSelect = row.querySelector('.form-level-select');

                if (productSelect) {
                    productSelect.name = `items[${index}][product_id]`;
                }

                if (qtyInput) {
                    qtyInput.name = `items[${index}][qty]`;
                }

                if (unitCostInput) {
                    unitCostInput.name = `items[${index}][unit_cost]`;
                }

                if (lineTotalInput) {
                    lineTotalInput.name = `items[${index}][line_total]`;
                }

                if (levelSelect) {
                    levelSelect.name = `items[${index}][levels][]`;
                }

                if (noteInput) {
                    noteInput.name = `items[${index}][note]`;
                }

                const levelQtyWrap = row.querySelector('.js-level-qty-wrap');
                if (levelQtyWrap) {
                    levelQtyWrap.querySelectorAll('input, select, textarea').forEach(function (input, childIndex) {
                        const field = input.dataset.field || 'qty';
                        input.name = `items[${index}][level_quantities][${childIndex}][${field}]`;
                    });
                }
            });
        }

        function destroyEnhancedSelect(row) {
            if (window.jQuery) {
                const $row = window.jQuery(row);

                if ($row.find('.select-search').hasClass('select2-hidden-accessible')) {
                    $row.find('.select-search').select2('destroy');
                }

                if ($row.find('.form-level-select').hasClass('select2-hidden-accessible')) {
                    $row.find('.form-level-select').select2('destroy');
                }
            }
        }

        function initEnhancedSelect(row) {
            if (window.jQuery && window.jQuery.fn.select2) {
                const $row = window.jQuery(row);

                $row.find('.select-search').select2({
                    width: '100%',
                    placeholder: 'Chọn sản phẩm...',
                });

                $row.find('.form-level-select').select2({
                    width: '100%',
                    placeholder: 'Chọn vị trí',
                });
            }
        }

        function addRow() {
            const firstRow = tbody.querySelector('.item-row');
            const totalRow = getTotalRow();

            if (!firstRow || !totalRow) return;

            const cloneSource = firstRow.cloneNode(true);

            destroyEnhancedSelect(cloneSource);
            resetRow(cloneSource);

            tbody.insertBefore(cloneSource, totalRow);

            reindexRows();
            initEnhancedSelect(cloneSource);
            updateTotals();
        }

        function removeRow(button) {
            const row = button.closest('.item-row');
            if (!row) return;

            const rows = getItemRows();
            if (rows.length <= 1) {
                row.querySelector('.select-search').value = '';
                row.querySelector('.js-qty').value = 1;
                row.querySelector('.js-unit_cost').value = 0;
                row.querySelector('.js-line-total').value = 0;

                const noteInput = row.querySelector('input[type="text"]');
                if (noteInput) noteInput.value = '';

                const levelSelect = row.querySelector('.form-level-select');
                if (levelSelect) levelSelect.innerHTML = '';

                const levelQtyWrap = row.querySelector('.js-level-qty-wrap');
                if (levelQtyWrap) levelQtyWrap.innerHTML = '';

                reindexRows();
                updateTotals();
                return;
            }

            row.remove();
            reindexRows();
            updateTotals();
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                addRow();
            });
        }

        tbody.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.btn-remove-row');
            if (removeBtn) {
                removeRow(removeBtn);
            }
        });

        tbody.addEventListener('input', function (e) {
            if (
                e.target.classList.contains('js-qty') ||
                e.target.classList.contains('js-unit_cost')
            ) {
                updateTotals();
            }
        });

        initEnhancedSelect(document);
        reindexRows();
        updateTotals();
    });
</script>