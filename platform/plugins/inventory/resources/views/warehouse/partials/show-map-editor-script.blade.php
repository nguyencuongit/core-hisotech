<script>
    (() => {
        @php
            $mapEditorPayload = [
                'mapEditorConfig' => $warehouseShow['mapEditorConfig'] ?? [],
                'locationMeta' => $warehouseShow['locationMeta'] ?? [],
                'mapTypeMeta' => $warehouseShow['mapTypeMeta'] ?? [],
                'mapEditorTools' => $warehouseShow['mapEditorTools'] ?? [],
                'storageModeMeta' => $warehouseShow['storageModeMeta'] ?? [],
                'locationTypes' => $warehouseShow['locationTypes'] ?? [],
                'mapLocationOptions' => $warehouseShow['mapLocationOptions'] ?? [],
            ];
        @endphp
        const warehouseShow = {!! json_encode($mapEditorPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) !!} || {};
        const mapEditorConfig = warehouseShow.mapEditorConfig || {};

        const $ = (selector, root = document) => root.querySelector(selector);
        const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
        const clone = (value) => JSON.parse(JSON.stringify(value ?? {}));
        const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
        const makeTempId = () => `tmp-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        const isNumericId = (value) => /^\d+$/.test(String(value ?? ''));
        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const notify = (message, type = 'success') => {
            if (window.Botble) {
                if (type === 'error' && typeof window.Botble.showError === 'function') {
                    window.Botble.showError(message);
                    return;
                }

                if (type === 'success' && typeof window.Botble.showSuccess === 'function') {
                    window.Botble.showSuccess(message);
                    return;
                }
            }

            window.alert(message);
        };

        const mapCreateForms = $$('[data-map-create-form]');
        const blueprintCards = $$('[data-blueprint-card]');
        const blueprintModeForm = mapCreateForms.find((form) => form.querySelector('input[name="storage_mode"]'));
        const selectedBlueprintMode = () => {
            return blueprintModeForm?.querySelector('input[name="storage_mode"]:checked')?.value
                || mapCreateForms.find((form) => form.querySelector('select[name="storage_mode"]'))?.querySelector('select[name="storage_mode"]')?.value
                || 'direct';
        };
        const syncBlueprintCards = () => {
            const mode = selectedBlueprintMode();

            blueprintCards.forEach((card) => {
                card.classList.toggle('is-hidden-by-mode', card.dataset.blueprintMode !== mode);
            });
        };

        mapCreateForms.forEach((form) => {
            const sizePreset = $('[data-map-size-preset]', form);
            const widthInput = $('[data-map-width-input]', form);
            const heightInput = $('[data-map-height-input]', form);

            sizePreset?.addEventListener('change', () => {
                if (!sizePreset.value || sizePreset.value === 'custom') {
                    widthInput?.focus();
                    return;
                }

                const [width, height] = sizePreset.value.split('x').map((value) => Number(value));

                if (widthInput && width) {
                    widthInput.value = String(width);
                }

                if (heightInput && height) {
                    heightInput.value = String(height);
                }
            });

            $$('input[name="storage_mode"], select[name="storage_mode"]', form).forEach((field) => {
                field.addEventListener('change', syncBlueprintCards);
            });
        });

        syncBlueprintCards();

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            if (window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modal).show();
                return;
            }

            modal.classList.add('show');
            modal.style.display = 'block';
            modal.removeAttribute('aria-hidden');
        };

        $$('[data-map-toggle-panel]').forEach((button) => {
            button.addEventListener('click', () => {
                const panel = document.getElementById(button.dataset.mapTogglePanel || '');

                if (!panel) {
                    return;
                }

                if (panel.classList.contains('modal')) {
                    openModal(panel);
                    return;
                }

                panel.classList.toggle('warehouse-hidden');
            });
        });

        $$('[data-open-warehouse-settings]').forEach((button) => {
            button.addEventListener('click', () => {
                const settingsModal = document.querySelector('[data-warehouse-settings-modal]');
                const currentModal = button.closest('.modal.show');

                if (currentModal && currentModal !== settingsModal && window.bootstrap?.Modal) {
                    currentModal.addEventListener('hidden.bs.modal', () => openModal(settingsModal), { once: true });
                    window.bootstrap.Modal.getOrCreateInstance(currentModal).hide();
                    return;
                }

                openModal(settingsModal);
            });
        });

        $$('[data-open-setup-wizard]').forEach((button) => {
            button.addEventListener('click', () => openModal(document.querySelector('[data-setup-wizard-modal]')));
        });

        $$('[data-map-show-blueprints]').forEach((button) => {
            button.addEventListener('click', () => {
                const panel = document.querySelector('[data-map-blueprints-panel]');
                if (panel) {
                    panel.classList.toggle('warehouse-hidden');
                }
            });
        });

        const dom = {
            root: $('[data-warehouse-map-editor]'),
            canvas: $('[data-map-canvas]'),
            canvasWrap: $('[data-map-canvas-wrap]'),
            viewport: $('[data-map-viewport]'),
            selection: $('[data-map-selection]'),
            statusText: $('[data-map-status-text]'),
            zoomIndicators: $$('[data-map-zoom-indicator]'),
            statTotal: $('[data-map-stat-total]'),
            statLinked: $('[data-map-stat-linked]'),
            statUnlinked: $('[data-map-stat-unlinked]'),
            legend: $('[data-map-legend]'),
            inspectorContent: $('[data-map-inspector-content]'),
            inspectorEmpty: $('[data-map-empty-inspector]'),
            viewDetail: $('[data-map-view-detail]'),
            mapSizeWidthInputs: $$('[data-map-size-width]'),
            mapSizeHeightInputs: $$('[data-map-size-height]'),
            mapSizeLabels: $$('[data-map-size-label]'),
            mapSizeHelp: $$('[data-map-size-help]'),
            detailModal: $('[data-map-detail-modal]'),
            detailTitle: $('[data-map-detail-title]'),
            detailSubtitle: $('[data-map-detail-subtitle]'),
            detailStats: $('[data-map-detail-stats]'),
            detailRows: $('[data-map-detail-rows]'),
        };

        if (!dom.root || !mapEditorConfig.map || !dom.canvas) {
            return;
        }

        const metaByType = { ...(warehouseShow.mapTypeMeta || {}), ...(warehouseShow.locationMeta || {}) };
        const toolsByKey = Object.fromEntries((mapEditorConfig.tools || []).map((tool) => [tool.key, tool]));
        let locationOptions = mapEditorConfig.locations || warehouseShow.mapLocationOptions || [];
        let locationById = Object.fromEntries(locationOptions.map((location) => [Number(location.id), location]));

        const normalizeItem = (item, index = 0) => ({
            id: item.id ?? makeTempId(),
            location_id: item.location_id ? Number(item.location_id) : null,
            item_type: item.item_type || 'zone',
            label: item.label || '',
            shape_type: item.shape_type || 'rect',
            x: Number(item.x || 0),
            y: Number(item.y || 0),
            width: Math.max(36, Number(item.width || 120)),
            height: Math.max(28, Number(item.height || 90)),
            rotation: Number(item.rotation || 0),
            color: item.color || metaByType[item.item_type || 'zone']?.color || '#e2e8f0',
            z_index: Number(item.z_index || index + 1),
            is_clickable: item.is_clickable !== false,
            meta_json: clone(item.meta_json || {}),
        });

        const state = {
            map: clone(mapEditorConfig.map),
            items: (mapEditorConfig.items || []).map((item, index) => normalizeItem(item, index)),
            selectedId: mapEditorConfig.selected_item_id ? String(mapEditorConfig.selected_item_id) : null,
            selectedLocationId: mapEditorConfig.selected_location_id ? Number(mapEditorConfig.selected_location_id) : null,
            zoom: 1,
            mode: dom.root.dataset.mapMode || 'view',
            dirty: false,
            snapEnabled: true,
            gridSize: Number($('[data-map-grid-size]', dom.root)?.value || 24),
            drawMode: false,
            drawTool: null,
            drag: null,
            pan: null,
            toolPointer: null,
            clipboardItem: null,
            clipboardPasteCount: 0,
            undoStack: [],
            isRestoring: false,
            suppressToolClick: false,
            didInitialFocus: false,
        };

        const clampMapWidth = (value) => Math.round(clamp(Number(value || state.map.width || 1200), 480, 10000));
        const clampMapHeight = (value) => Math.round(clamp(Number(value || state.map.height || 800), 320, 10000));
        const formatMapSize = () => `${Number(state.map.width || 0).toLocaleString('vi-VN')} x ${Number(state.map.height || 0).toLocaleString('vi-VN')} px`;
        const syncMapSizeUi = () => {
            state.map.width = clampMapWidth(state.map.width);
            state.map.height = clampMapHeight(state.map.height);

            dom.canvas.style.width = `${state.map.width}px`;
            dom.canvas.style.height = `${state.map.height}px`;

            dom.mapSizeWidthInputs.forEach((input) => {
                if (document.activeElement !== input) {
                    input.value = String(state.map.width);
                }
            });

            dom.mapSizeHeightInputs.forEach((input) => {
                if (document.activeElement !== input) {
                    input.value = String(state.map.height);
                }
            });

            dom.mapSizeLabels.forEach((label) => {
                label.textContent = formatMapSize();
            });

            dom.mapSizeHelp.forEach((help) => {
                help.textContent = `Canvas hiện tại: ${formatMapSize()}.`;
            });
        };
        const setMapSize = (width, height, options = {}) => {
            const nextWidth = clampMapWidth(width);
            const nextHeight = clampMapHeight(height);
            const changed = nextWidth !== Number(state.map.width) || nextHeight !== Number(state.map.height);

            state.map.width = nextWidth;
            state.map.height = nextHeight;
            syncMapSizeUi();

            if (changed && options.dirty !== false) {
                setDirty(true);
            }

            return changed;
        };
        const getItemRight = (item) => {
            const rotation = Number(item?.rotation || 0);
            const itemWidth = Number(item?.width || 0);
            const itemHeight = Number(item?.height || 0);
            const visualWidth = [90, 270].includes(((rotation % 360) + 360) % 360) ? itemHeight : itemWidth;
            const centerX = Number(item?.x || 0) + itemWidth / 2;

            return centerX + visualWidth / 2;
        };
        const getItemBottom = (item) => {
            const rotation = Number(item?.rotation || 0);
            const itemWidth = Number(item?.width || 0);
            const itemHeight = Number(item?.height || 0);
            const visualHeight = [90, 270].includes(((rotation % 360) + 360) % 360) ? itemWidth : itemHeight;
            const centerY = Number(item?.y || 0) + itemHeight / 2;

            return centerY + visualHeight / 2;
        };
        const getRequiredMapSize = (padding = 160) => {
            const maxRight = state.items.reduce((max, item) => Math.max(max, getItemRight(item)), 0);
            const maxBottom = state.items.reduce((max, item) => Math.max(max, getItemBottom(item)), 0);

            return {
                width: clampMapWidth(Math.max(state.map.width || 0, maxRight + padding)),
                height: clampMapHeight(Math.max(state.map.height || 0, maxBottom + padding)),
            };
        };
        const autoExpandMapForItem = (item, padding = 160) => {
            if (!item) {
                return false;
            }

            const requiredWidth = getItemRight(item) + padding;
            const requiredHeight = getItemBottom(item) + padding;

            return setMapSize(
                Math.max(state.map.width || 0, requiredWidth),
                Math.max(state.map.height || 0, requiredHeight)
            );
        };

        const isEditMode = () => state.mode === 'edit';
        const getItem = (id = state.selectedId) => state.items.find((item) => String(item.id) === String(id)) || null;
        const getModuleType = (item) => item?.meta_json?.module_type || item?.item_type || 'zone';
        const getModuleClass = (item) => String(getModuleType(item)).replace(/[^a-z0-9_-]/gi, '-').toLowerCase();
        const isRackLikeMapItem = (item) => ['simple_shelf', 'pallet_rack', 'rack', 'floor_pallet_area'].includes(getModuleType(item));
        const isAisleItem = (item) => getModuleType(item) === 'aisle';
        const layoutMetricModuleTypes = [
            'zone',
            'receiving_area',
            'qc_area',
            'staging_area',
            'dispatch_area',
            'dock',
            'rack',
            'simple_shelf',
            'pallet_rack',
            'pallet_slot',
            'floor_pallet_area',
            'bin_area',
        ];
        const isLayoutMetricItem = (item) => layoutMetricModuleTypes.includes(getModuleType(item));
        const snapValue = (value) => state.snapEnabled ? Math.round(value / state.gridSize) * state.gridSize : value;
        const getLocationLabel = (locationId) => locationById[Number(locationId)]?.label || 'Chưa gắn location';
        const syncLocationOptions = (locations) => {
            if (!Array.isArray(locations) || locations.length === 0) {
                return;
            }

            locationOptions = locations;
            locationById = Object.fromEntries(locationOptions.map((location) => [Number(location.id), location]));

            $$('[data-inspector-field="location_id"]', dom.inspectorContent).forEach((select) => {
                const selectedValue = getItem()?.location_id || select.value || '';
                select.replaceChildren();

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'Chưa gắn location';
                select.appendChild(emptyOption);

                locationOptions.forEach((location) => {
                    const option = document.createElement('option');
                    option.value = String(location.id);
                    option.textContent = location.label;
                    select.appendChild(option);
                });

                select.value = selectedValue && locationById[Number(selectedValue)] ? String(selectedValue) : '';
            });
        };
        const getMapFootprintCellSize = () => Math.max(1, Number(state.gridSize || 24)) * 4;
        const normalizeRotation = (rotation) => {
            const normalized = Number(rotation || 0) % 360;

            return normalized < 0 ? normalized + 360 : normalized;
        };
        const getVisualRotation = (item) => normalizeRotation(item?.rotation);
        const getAisleLength = (item) => Math.max(
            getMapFootprintCellSize(),
            Number(item?.width || 0),
            Number(item?.height || 0)
        );
        const getFootprintMetrics = (item) => {
            const meta = item?.meta_json || {};

            return {
                widthCount: Math.max(1, Number(meta.width_count || meta.positions_per_level || meta.column_count || 1)),
                heightCount: Math.max(1, Number(meta.height_count || meta.level_count || 1)),
                lengthCount: Math.max(1, Number(meta.length_count || meta.row_count || 1)),
            };
        };
        const ensureLayoutMetrics = (item) => {
            if (!item || !isLayoutMetricItem(item)) {
                return null;
            }

            item.meta_json = item.meta_json || {};

            if (!item.meta_json.width_count) {
                item.meta_json.width_count = Math.max(1, Math.round(Number(item.width || 96) / getMapFootprintCellSize()));
            }

            if (!item.meta_json.length_count) {
                item.meta_json.length_count = Math.max(1, Math.round(Number(item.height || 96) / getMapFootprintCellSize()));
            }

            if (!item.meta_json.height_count && !item.meta_json.level_count) {
                item.meta_json.height_count = 1;
            }

            return getFootprintMetrics(item);
        };
        const getLayoutCapacity = (item) => {
            const metrics = ensureLayoutMetrics(item);

            if (!metrics) {
                return null;
            }

            return metrics.widthCount * metrics.heightCount * metrics.lengthCount;
        };
        const rotateItem90 = (item) => {
            if (!item) {
                return false;
            }

            item.rotation = normalizeRotation(Number(item.rotation || 0) + 90);

            if (getModuleType(item) === 'aisle') {
                applyResizeRules(item);
            }

            autoExpandMapForItem(item);

            return true;
        };
        const getShelfMetrics = (item) => {
            const meta = item?.meta_json || {};

            return {
                widthCount: Math.max(1, Number(meta.width_count || meta.bin_count_per_level || 1)),
                heightCount: Math.max(1, Number(meta.height_count || meta.level_count || 1)),
                lengthCount: Math.max(1, Number(meta.length_count || 1)),
            };
        };
        const getCapacity = (item) => {
            const moduleType = getModuleType(item);
            const meta = item?.meta_json || {};

            if (moduleType === 'pallet_rack') {
                return Math.max(1, Number(meta.level_count || 1))
                    * Math.max(1, Number(meta.positions_per_level || meta.width_count || 1))
                    * Math.max(1, Number(meta.length_count || 1))
                    * Math.max(1, Number(meta.pallets_per_position || 1));
            }

            if (moduleType === 'simple_shelf') {
                const metrics = getShelfMetrics(item);

                return metrics.widthCount * metrics.heightCount * metrics.lengthCount;
            }

            if (moduleType === 'pallet_slot') {
                return Math.max(1, Number(meta.pallets_per_position || 1));
            }

            if (moduleType === 'floor_pallet_area') {
                return Math.max(1, Number(meta.row_count || 1))
                    * Math.max(1, Number(meta.column_count || 1))
                    * Math.max(1, Number(meta.pallets_per_position || 1));
            }

            const layoutCapacity = getLayoutCapacity(item);

            if (layoutCapacity) {
                return layoutCapacity;
            }

            return null;
        };
        const isTypingTarget = (target) => Boolean(target?.closest?.('input, select, textarea, [contenteditable="true"]'));

        const setDirty = (value = true) => {
            state.dirty = value;

            if (dom.statusText) {
                dom.statusText.textContent = value
                    ? 'Bạn đang có thay đổi chưa lưu. Bấm "Lưu layout" để ghi xuống database.'
                    : 'Sơ đồ đang đồng bộ với dữ liệu hiện tại.';
            }
        };
        const makeHistorySnapshot = () => ({
            items: clone(state.items),
            selectedId: state.selectedId,
            selectedLocationId: state.selectedLocationId,
            map: {
                width: state.map.width,
                height: state.map.height,
            },
        });
        const pushUndoSnapshot = () => {
            if (state.isRestoring) {
                return;
            }

            state.undoStack.push(makeHistorySnapshot());

            if (state.undoStack.length > 80) {
                state.undoStack.shift();
            }
        };
        const restoreUndoSnapshot = () => {
            const snapshot = state.undoStack.pop();

            if (!snapshot) {
                notify('Chưa có thao tác nào để quay lại.', 'error');
                return false;
            }

            state.isRestoring = true;
            state.items = (snapshot.items || []).map((item, index) => normalizeItem(item, index));
            state.selectedId = snapshot.selectedId || null;
            state.selectedLocationId = snapshot.selectedLocationId || null;

            if (snapshot.map) {
                state.map.width = snapshot.map.width || state.map.width;
                state.map.height = snapshot.map.height || state.map.height;
            }

            setDirty(true);
            render();
            state.isRestoring = false;

            return true;
        };
        const markMapChanged = () => {
            pushUndoSnapshot();
            setDirty(true);
        };

        const updateUrlLocation = (locationId) => {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'maps');

            if (locationId) {
                url.searchParams.set('location_id', String(locationId));
            } else {
                url.searchParams.delete('location_id');
            }

            if (state.map?.id) {
                url.searchParams.set('map_id', String(state.map.id));
            }

            window.history.replaceState({}, '', url.toString());
        };

        const setActiveLocationNode = (locationId, scroll = false) => {
            state.selectedLocationId = locationId ? Number(locationId) : null;

            $$('[data-location-node]').forEach((node) => {
                node.classList.toggle('is-active', Number(node.dataset.locationId) === state.selectedLocationId);
            });

            if (scroll && state.selectedLocationId) {
                const node = $$('[data-location-node]').find((element) => Number(element.dataset.locationId) === state.selectedLocationId);
                node?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };

        const centerCanvasOnItem = (item) => {
            if (!item || !dom.viewport) {
                return;
            }

            window.requestAnimationFrame(() => {
                dom.viewport.scrollTo({
                    left: Math.max(0, ((Number(item.x) + Number(item.width) / 2) * state.zoom) - dom.viewport.clientWidth / 2),
                    top: Math.max(0, ((Number(item.y) + Number(item.height) / 2) * state.zoom) - dom.viewport.clientHeight / 2),
                    behavior: 'smooth',
                });
            });
        };

        const selectItem = (itemId, options = {}) => {
            state.selectedId = itemId ? String(itemId) : null;
            const item = getItem();

            if (item?.location_id) {
                setActiveLocationNode(item.location_id, options.scrollTree ?? false);
                updateUrlLocation(item.location_id);
            }

            render();

            if (options.center && item) {
                centerCanvasOnItem(item);
            }
        };

        const updateSelectionBox = () => {
            const item = getItem();

            if (!dom.selection || !item) {
                dom.selection?.classList.remove('is-visible');
                return;
            }

            dom.selection.classList.add('is-visible');
            dom.selection.style.left = `${item.x}px`;
            dom.selection.style.top = `${item.y}px`;
            dom.selection.style.width = `${item.width}px`;
            dom.selection.style.height = `${item.height}px`;
            dom.selection.style.transform = `rotate(${getVisualRotation(item)}deg)`;
        };

        const renderCanvasItem = (item) => {
            const meta = metaByType[item.item_type] || { label: item.item_type, icon: 'ti ti-map-pin' };
            const capacity = getCapacity(item);
            const moduleType = getModuleType(item);
            const isRackLayout = ['simple_shelf', 'pallet_rack', 'floor_pallet_area', 'rack'].includes(moduleType);
            const storageMode = item.meta_json?.storage_mode || state.map?.storage_mode || 'direct';
            const layoutMetrics = ensureLayoutMetrics(item);
            const metrics = moduleType === 'simple_shelf' ? getShelfMetrics(item) : null;
            const displayMetrics = metrics || (layoutMetrics && !['aisle', 'label'].includes(moduleType) ? layoutMetrics : null);
            const itemTitle = moduleType === 'simple_shelf' && (!item.label || item.label === 'Kệ 4 x 4')
                ? meta.label
                : (item.label || meta.label);
            const itemMetaText = displayMetrics
                ? `Mặt bằng ${displayMetrics.widthCount} x ${displayMetrics.lengthCount} · ${displayMetrics.heightCount} tầng`
                : (item.location_id ? getLocationLabel(item.location_id) : meta.label);
            const shelfVisual = '';
            const shelfSummary = metrics
                ? `
                    <div class="warehouse-map-canvas__shelf-levels">${metrics.heightCount} tầng</div>
                    <div class="warehouse-map-canvas__shelf-summary">${metrics.widthCount} ngang · ${metrics.lengthCount} dài</div>
                `
                : '';
            const areaSummary = !metrics && displayMetrics
                ? `<div class="warehouse-map-canvas__shelf-summary">${displayMetrics.widthCount} ngang · ${displayMetrics.lengthCount} dài · ${displayMetrics.heightCount} tầng</div>`
                : '';
            const capacitySummary = capacity
                ? `<div class="warehouse-map-canvas__capacity-text">Sức chứa ${capacity}</div>`
                : '';
            const button = document.createElement('button');
            button.type = 'button';
            const aisleOrientationClass = moduleType === 'aisle' ? ` is-aisle-${getAisleOrientation(item)}` : '';
            button.className = `warehouse-map-canvas__item warehouse-map-canvas__item--${getModuleClass(item)}${aisleOrientationClass}${item.location_id ? ' is-linked' : ''}${item.shape_type === 'label' ? ' warehouse-map-canvas__item--label' : ''}${String(item.id) === String(state.selectedId) ? ' is-selected' : ''}${isRackLayout ? ' is-rack-layout' : ''}${storageMode === 'pallet' ? ' is-pallet-layout' : ' is-direct-layout'}`;
            button.dataset.mapItemId = String(item.id);
            button.style.left = `${item.x}px`;
            button.style.top = `${item.y}px`;
            button.style.width = `${item.width}px`;
            button.style.height = `${item.height}px`;
            button.style.background = item.shape_type === 'label' ? 'transparent' : item.color;
            button.style.zIndex = String(item.z_index || 1);
            button.style.setProperty('--map-item-rotation', `${getVisualRotation(item)}deg`);
            button.innerHTML = `
                ${shelfVisual}
                <div class="warehouse-map-canvas__content">
                    <div class="warehouse-map-canvas__title"><i class="${meta.icon}"></i><span>${itemTitle}</span></div>
                    ${shelfSummary}
                    ${areaSummary}
                    ${capacitySummary}
                    <div class="warehouse-map-canvas__meta">${itemMetaText}</div>
                </div>
            `;

            if (isEditMode() && moduleType === 'aisle') {
                ['start', 'end'].forEach((edge) => {
                    const handle = document.createElement('span');
                    handle.className = `warehouse-map-canvas__handle warehouse-map-canvas__handle--${edge}`;
                    handle.dataset.mapResizeHandle = edge;
                    button.appendChild(handle);
                });
            }

            return button;
        };

        const renderInspector = () => {
            const item = getItem();

            if (!dom.inspectorContent || !dom.inspectorEmpty) {
                return;
            }

            if (!item) {
                dom.inspectorContent.classList.add('warehouse-hidden');
                dom.inspectorEmpty.classList.remove('warehouse-hidden');
                return;
            }

            dom.inspectorEmpty.classList.add('warehouse-hidden');
            dom.inspectorContent.classList.remove('warehouse-hidden');
            ensureLayoutMetrics(item);

            $$('[data-inspector-field]', dom.inspectorContent).forEach((field) => {
                const key = field.dataset.inspectorField;
                const meta = metaByType[item.item_type] || { label: item.item_type };

                if (key === 'item_type_label') {
                    field.value = meta.label;
                    return;
                }

                if (key === 'location_id') {
                    field.value = item.location_id || '';
                    return;
                }

                if (key === 'is_clickable') {
                    field.checked = Boolean(item.is_clickable);
                    return;
                }

                field.value = item[key] ?? '';
            });

            $$('[data-inspector-meta]', dom.inspectorContent).forEach((field) => {
                const value = item.meta_json?.[field.dataset.inspectorMeta];

                if (field.type === 'checkbox') {
                    field.checked = Boolean(value);
                    return;
                }

                field.value = value ?? '';
            });

            const moduleType = getModuleType(item);
            $$('[data-inspector-layout-section]', dom.inspectorContent).forEach((section) => {
                section.classList.toggle('warehouse-hidden', moduleType === 'aisle');
            });

            $$('[data-inspector-section]', dom.inspectorContent).forEach((section) => {
                section.classList.toggle('warehouse-hidden', section.dataset.inspectorSection !== moduleType);
            });

            const capacityElement = $('[data-inspector-capacity]', dom.inspectorContent);
            if (capacityElement) {
                const capacity = getCapacity(item);
                capacityElement.textContent = capacity ? `${capacity} vị trí logic` : '-';
            }

            const miniSpecBody = $('[data-inspector-mini-spec-body]', dom.inspectorContent);
            if (miniSpecBody) {
                const metrics = ensureLayoutMetrics(item);
                const capacity = getCapacity(item);

                miniSpecBody.textContent = metrics
                    ? `${metrics.widthCount} ô ngang x ${metrics.lengthCount} ô dài x ${metrics.heightCount} tầng = ${capacity || '-'} vị trí logic`
                    : '-';
            }
        };

        const renderViewDetail = () => {
            if (!dom.viewDetail) {
                return;
            }

            const item = getItem();
            const title = $('strong', dom.viewDetail);
            const detail = $('span', dom.viewDetail);

            if (!title || !detail) {
                return;
            }

            if (!item) {
                title.textContent = 'Đang xem sơ đồ';
                detail.textContent = 'Bấm vào một vùng trên sơ đồ hoặc một vị trí trong cây để xem liên kết tương ứng.';
                return;
            }

            const meta = metaByType[item.item_type] || { label: item.item_type };
            title.textContent = item.label || meta.label || 'Vùng chưa đặt tên';
            detail.textContent = item.location_id ? `Đã gắn với ${getLocationLabel(item.location_id)}` : `${meta.label || item.item_type} chưa gắn location.`;
        };

        const formatNumber = (value) => Number(value || 0).toLocaleString('vi-VN');
        const getItemDetailRows = (item) => {
            const meta = metaByType[item.item_type] || { label: item.item_type };
            const moduleType = getModuleType(item);
            const metrics = ensureLayoutMetrics(item);
            const capacity = getCapacity(item);
            const rows = [
                ['Loại module', meta.label || item.item_type],
                ['Tên hiển thị', item.label || meta.label || '-'],
                ['Location liên kết', item.location_id ? getLocationLabel(item.location_id) : 'Chưa gắn location'],
                ['Chế độ lưu trữ', item.meta_json?.storage_mode || state.map?.storage_mode || 'direct'],
                ['Kích thước trên map', `${Math.round(Number(item.width || 0))} x ${Math.round(Number(item.height || 0))} px`],
                ['Góc xoay', `${normalizeRotation(item.rotation)}°`],
            ];

            if (metrics) {
                rows.push(
                    ['Ô ngang / cột', formatNumber(metrics.widthCount)],
                    ['Chiều dài / số hàng', formatNumber(metrics.lengthCount)],
                    ['Số tầng logic', formatNumber(metrics.heightCount)],
                    ['Ô mỗi tầng', formatNumber(metrics.widthCount * metrics.lengthCount)],
                    ['Sức chứa logic', capacity ? `${formatNumber(capacity)} vị trí` : '-']
                );
            }

            if (moduleType === 'pallet_rack') {
                rows.push(
                    ['Pallet mỗi slot', formatNumber(item.meta_json?.pallets_per_position || 1)],
                    ['Vị trí mỗi tầng', formatNumber(item.meta_json?.positions_per_level || item.meta_json?.width_count || 1)]
                );
            }

            if (moduleType === 'floor_pallet_area') {
                rows.push(
                    ['Số hàng pallet', formatNumber(item.meta_json?.row_count || metrics?.lengthCount || 1)],
                    ['Số cột pallet', formatNumber(item.meta_json?.column_count || metrics?.widthCount || 1)],
                    ['Pallet mỗi vị trí', formatNumber(item.meta_json?.pallets_per_position || 1)]
                );
            }

            if (moduleType === 'simple_shelf') {
                rows.push(
                    ['Số kệ/module', '1'],
                    ['Tiền tố sinh location', item.meta_json?.prefix || '-']
                );
            }

            if (moduleType === 'aisle') {
                rows.push(
                    ['Hướng lối đi', getAisleOrientation(item) === 'horizontal' ? 'Ngang' : 'Dọc'],
                    ['Chiều dài lối đi', `${formatNumber(item.meta_json?.length_count || 1)} ô`]
                );
            }

            return rows;
        };
        const renderMapItemDetailModal = () => {
            const item = getItem();

            if (!item) {
                notify('Chọn một vùng trên sơ đồ trước khi xem chi tiết.', 'error');
                return;
            }

            const meta = metaByType[item.item_type] || { label: item.item_type };
            const metrics = ensureLayoutMetrics(item);
            const capacity = getCapacity(item);
            const title = item.label || meta.label || 'Vùng đang chọn';

            if (dom.detailTitle) {
                dom.detailTitle.textContent = title;
            }

            if (dom.detailSubtitle) {
                dom.detailSubtitle.textContent = item.location_id
                    ? `Đang liên kết: ${getLocationLabel(item.location_id)}`
                    : 'Module này chưa liên kết location.';
            }

            if (dom.detailStats) {
                const stats = [
                    ['Sức chứa', capacity ? formatNumber(capacity) : '-'],
                    ['Ô ngang', metrics ? formatNumber(metrics.widthCount) : '-'],
                    ['Số hàng', metrics ? formatNumber(metrics.lengthCount) : '-'],
                    ['Số tầng', metrics ? formatNumber(metrics.heightCount) : '-'],
                ];

                dom.detailStats.innerHTML = stats.map(([label, value]) => `
                    <div class="warehouse-map-detail-card">
                        <span>${escapeHtml(label)}</span>
                        <strong>${escapeHtml(value)}</strong>
                    </div>
                `).join('');
            }

            if (dom.detailRows) {
                dom.detailRows.innerHTML = getItemDetailRows(item).map(([label, value]) => `
                    <div class="warehouse-map-detail-row">
                        <span>${escapeHtml(label)}</span>
                        <strong>${escapeHtml(value)}</strong>
                    </div>
                `).join('');
            }

            if (dom.detailModal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(dom.detailModal).show();
                return;
            }

            if (dom.detailModal) {
                dom.detailModal.classList.add('show');
                dom.detailModal.style.display = 'block';
                dom.detailModal.removeAttribute('aria-hidden');
            }
        };

        const updateModeState = () => {
            dom.root.dataset.mapMode = state.mode;
            dom.root.classList.toggle('is-edit-mode', state.mode === 'edit');
            dom.root.classList.toggle('is-view-mode', state.mode === 'view');
            dom.root.classList.toggle('has-selected', Boolean(getItem()));

            $$('[data-map-mode-button]', dom.root).forEach((button) => {
                button.classList.toggle('is-active', button.dataset.mapModeButton === state.mode);
            });

            $$('[data-map-draw-toggle]', dom.root).forEach((button) => {
                button.classList.toggle('is-active', state.drawMode);
            });

            $$('[data-map-snap-toggle]', dom.root).forEach((button) => {
                button.classList.toggle('is-active', state.snapEnabled);
            });

            $$('[data-map-grid-size]', dom.root).forEach((select) => {
                select.value = String(state.gridSize);
            });

            $$('[data-map-tool]', dom.root).forEach((button) => {
                button.classList.toggle('is-active', state.drawMode && button.dataset.mapTool === state.drawTool);
            });

            $$('[data-map-rotate-90]', dom.root).forEach((button) => {
                const item = getItem();
                const canRotate = isEditMode() && item;
                button.disabled = !canRotate;
                button.title = canRotate ? 'Xoay khối 90 độ' : 'Chọn một khối để xoay 90 độ';
            });

            $$('[data-map-sync-levels]', dom.root).forEach((button) => {
                const item = getItem();
                const canSyncLevels = isEditMode() && item && isRackLikeMapItem(item);
                button.disabled = !canSyncLevels;
                button.classList.toggle('d-none', Boolean(item) && !isRackLikeMapItem(item));
                button.title = canSyncLevels ? 'Tạo level theo số tầng của kệ/rack đang chọn' : 'Chọn một kệ/rack để tạo level';
            });

            $$('[data-map-sync-levels-help]', dom.root).forEach((help) => {
                const item = getItem();
                help.classList.toggle('d-none', Boolean(item) && !isRackLikeMapItem(item));
            });

            if (dom.canvas) {
                dom.canvas.classList.toggle('is-draw-mode', state.drawMode);
                dom.canvas.style.backgroundSize = state.map.background_url
                    ? `${state.gridSize}px ${state.gridSize}px, ${state.gridSize}px ${state.gridSize}px, cover`
                    : `${state.gridSize}px ${state.gridSize}px, ${state.gridSize}px ${state.gridSize}px`;
            }
        };

        const setZoom = (nextZoom, focalEvent = null) => {
            const previousZoom = Number(state.zoom || 1);
            const viewportRect = dom.viewport?.getBoundingClientRect();
            const anchorX = focalEvent && viewportRect ? focalEvent.clientX - viewportRect.left : (dom.viewport?.clientWidth || 0) / 2;
            const anchorY = focalEvent && viewportRect ? focalEvent.clientY - viewportRect.top : (dom.viewport?.clientHeight || 0) / 2;
            const mapX = ((dom.viewport?.scrollLeft || 0) + anchorX) / previousZoom;
            const mapY = ((dom.viewport?.scrollTop || 0) + anchorY) / previousZoom;

            state.zoom = clamp(Number(nextZoom.toFixed(2)), 0.5, 2);
            render();

            if (dom.viewport) {
                dom.viewport.scrollLeft = Math.max(0, (mapX * state.zoom) - anchorX);
                dom.viewport.scrollTop = Math.max(0, (mapY * state.zoom) - anchorY);
            }
        };

        function render() {
            syncMapSizeUi();
            dom.canvas.querySelectorAll('[data-map-item-id]').forEach((node) => node.remove());
            state.items
                .slice()
                .sort((a, b) => (a.z_index || 0) - (b.z_index || 0))
                .forEach((item) => dom.canvas.appendChild(renderCanvasItem(item)));

            if (dom.canvasWrap) {
                dom.canvasWrap.style.transform = `scale(${state.zoom})`;
            }

            if (dom.statTotal) {
                dom.statTotal.textContent = String(state.items.length);
            }

            if (dom.statLinked) {
                dom.statLinked.textContent = String(state.items.filter((item) => item.location_id).length);
            }

            if (dom.statUnlinked) {
                dom.statUnlinked.textContent = String(state.items.filter((item) => !item.location_id).length);
            }

            dom.zoomIndicators.forEach((indicator) => {
                indicator.textContent = `Zoom ${Math.round(state.zoom * 100)}%`;
            });

            updateSelectionBox();
            renderInspector();
            renderViewDetail();
            updateModeState();

            if (!state.didInitialFocus) {
                state.didInitialFocus = true;
                const initialItem = getItem();

                if (initialItem) {
                    setActiveLocationNode(initialItem.location_id || state.selectedLocationId, true);
                    centerCanvasOnItem(initialItem);
                } else if (state.selectedLocationId) {
                    setActiveLocationNode(state.selectedLocationId, true);
                }
            }
        }

        const getCanvasPoint = (clientX, clientY) => {
            const rect = dom.canvas.getBoundingClientRect();

            return {
                x: clamp((clientX - rect.left) / state.zoom, 0, state.map.width),
                y: clamp((clientY - rect.top) / state.zoom, 0, state.map.height),
            };
        };

        const isPointInsideDropZone = (clientX, clientY) => {
            const rect = (dom.viewport || dom.canvas).getBoundingClientRect();
            return clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom;
        };

        const createItemFromTool = (toolKey, point) => {
            if (!isEditMode()) {
                return null;
            }

            const tool = toolsByKey[toolKey];
            if (!tool) {
                return null;
            }

            const x = snapValue(point?.x ?? ((dom.viewport?.scrollLeft || 0) / state.zoom) + ((dom.viewport?.clientWidth || 320) / state.zoom / 2));
            const y = snapValue(point?.y ?? ((dom.viewport?.scrollTop || 0) / state.zoom) + ((dom.viewport?.clientHeight || 240) / state.zoom / 2));
            const item = normalizeItem({
                id: makeTempId(),
                item_type: tool.item_type,
                label: tool.label,
                shape_type: tool.shape_type || 'rect',
                x: clamp(x - Number(tool.width || 120) / 2, 0, Math.max(0, state.map.width - Number(tool.width || 120))),
                y: clamp(y - Number(tool.height || 90) / 2, 0, Math.max(0, state.map.height - Number(tool.height || 90))),
                width: tool.width || 120,
                height: tool.height || 90,
                rotation: 0,
                color: tool.color || '#e2e8f0',
                z_index: state.items.length + 1,
                is_clickable: true,
                meta_json: clone(tool.meta_json || {}),
            }, state.items.length);

            applyResizeRules(item);
            item.x = clamp(x - Number(item.width || 120) / 2, 0, Math.max(0, state.map.width - Number(item.width || 120)));
            item.y = clamp(y - Number(item.height || 90) / 2, 0, Math.max(0, state.map.height - Number(item.height || 90)));

            pushUndoSnapshot();
            state.items.push(item);
            state.selectedId = String(item.id);
            mergeConnectedAisles(item);
            autoExpandMapForItem(item);
            setDirty(true);
            render();
            centerCanvasOnItem(item);
            return item;
        };

        const getAisleHandleAxis = (item, edge = 'end') => {
            const rotation = normalizeRotation(item?.rotation);
            const isStart = edge === 'start';

            if (rotation === 90) {
                return { x: isStart ? 1 : -1, y: 0 };
            }

            if (rotation === 180) {
                return { x: 0, y: isStart ? 1 : -1 };
            }

            if (rotation === 270) {
                return { x: isStart ? -1 : 1, y: 0 };
            }

            return {
                x: 0,
                y: isStart ? -1 : 1,
            };
        };
        const resizeAisleFromHandle = (item, dragState, point) => {
            const axis = getAisleHandleAxis(item, dragState.resizeEdge);
            const pointerDeltaX = point.x - dragState.startPointer.x;
            const pointerDeltaY = point.y - dragState.startPointer.y;
            const signedDelta = (pointerDeltaX * axis.x) + (pointerDeltaY * axis.y);
            const footprintCellSize = getMapFootprintCellSize();
            const startLength = Math.max(footprintCellSize, Number(dragState.startAisleLength || getAisleLength(item)));
            const nextLength = Math.max(footprintCellSize, snapValue(startLength + signedDelta));
            const centerShift = (nextLength - startLength) / 2;
            const centerX = Number(dragState.startCenterX || 0) + (axis.x * centerShift);
            const centerY = Number(dragState.startCenterY || 0) + (axis.y * centerShift);

            item.width = footprintCellSize;
            item.height = nextLength;
            item.meta_json = item.meta_json || {};
            item.meta_json.width_count = 1;
            item.meta_json.length_count = Math.max(1, Math.round(nextLength / footprintCellSize));

            setItemCenterWithinMap(item, centerX, centerY);
            autoExpandMapForItem(item);
        };
        const beginItemPointer = (event, itemId, mode) => {
            const item = getItem(itemId);
            if (!item) {
                return;
            }

            pushUndoSnapshot();
            state.drag = {
                mode,
                itemId: String(item.id),
                startPointer: getCanvasPoint(event.clientX, event.clientY),
                startX: item.x,
                startY: item.y,
                startWidth: item.width,
                startHeight: item.height,
                startCenterX: Number(item.x || 0) + Number(item.width || 0) / 2,
                startCenterY: Number(item.y || 0) + Number(item.height || 0) / 2,
                startAisleLength: getAisleLength(item),
                resizeEdge: event.target.closest('[data-map-resize-handle]')?.dataset.mapResizeHandle || 'end',
            };

            event.preventDefault();
        };

        const applyResizeRules = (item) => {
            const moduleType = getModuleType(item);
            item.meta_json = item.meta_json || {};

            if (moduleType === 'pallet_rack') {
                const metrics = getFootprintMetrics(item);
                const footprintCellSize = getMapFootprintCellSize();
                item.meta_json.width_count = metrics.widthCount;
                item.meta_json.height_count = metrics.heightCount;
                item.meta_json.length_count = metrics.lengthCount;
                item.meta_json.positions_per_level = metrics.widthCount;
                item.meta_json.level_count = metrics.heightCount;
                item.meta_json.pallets_per_position = Math.max(1, Number(item.meta_json.pallets_per_position || 1));
                item.width = Math.max(footprintCellSize, metrics.widthCount * footprintCellSize);
                item.height = Math.max(footprintCellSize, metrics.lengthCount * footprintCellSize);
            }

            if (moduleType === 'simple_shelf') {
                const metrics = getShelfMetrics(item);
                const footprintCellSize = getMapFootprintCellSize();
                item.meta_json.width_count = metrics.widthCount;
                item.meta_json.height_count = metrics.heightCount;
                item.meta_json.length_count = metrics.lengthCount;
                delete item.meta_json.bin_count_per_level;
                delete item.meta_json.level_count;
                if (!item.label || item.label === 'Kệ 4 x 4') {
                    item.label = 'Kệ';
                }
                item.width = Math.max(footprintCellSize, metrics.widthCount * footprintCellSize);
                item.height = Math.max(footprintCellSize, metrics.lengthCount * footprintCellSize);
            }

            if (moduleType === 'aisle') {
                const footprintCellSize = getMapFootprintCellSize();
                const length = getAisleLength(item);

                item.meta_json.width_count = 1;
                item.meta_json.length_count = Math.max(1, Math.round(length / footprintCellSize));

                item.width = footprintCellSize;
                item.height = length;
            }

            if (moduleType === 'floor_pallet_area') {
                item.meta_json.column_count = Math.max(1, Math.round(item.width / 48));
                item.meta_json.row_count = Math.max(1, Math.round(item.height / 42));
                item.meta_json.pallets_per_position = Math.max(1, Number(item.meta_json.pallets_per_position || 1));
                item.width = Math.max(96, item.meta_json.column_count * 48);
                item.height = Math.max(84, item.meta_json.row_count * 42);
            }

            if ([
                'zone',
                'receiving_area',
                'qc_area',
                'staging_area',
                'dispatch_area',
                'dock',
                'rack',
                'pallet_slot',
                'bin_area',
            ].includes(moduleType) && (item.meta_json.width_count || item.meta_json.length_count)) {
                const metrics = getFootprintMetrics(item);
                const footprintCellSize = getMapFootprintCellSize();
                item.meta_json.width_count = metrics.widthCount;
                item.meta_json.height_count = metrics.heightCount;
                item.meta_json.length_count = metrics.lengthCount;
                item.width = Math.max(footprintCellSize, metrics.widthCount * footprintCellSize);
                item.height = Math.max(footprintCellSize, metrics.lengthCount * footprintCellSize);
            }
        };

        const getVisualBounds = (item) => {
            const rotation = normalizeRotation(item?.rotation);
            const isSideways = [90, 270].includes(rotation);
            const itemWidth = Number(item?.width || 0);
            const itemHeight = Number(item?.height || 0);
            const width = isSideways ? itemHeight : itemWidth;
            const height = isSideways ? itemWidth : itemHeight;
            const centerX = Number(item?.x || 0) + itemWidth / 2;
            const centerY = Number(item?.y || 0) + itemHeight / 2;

            return {
                x: centerX - width / 2,
                y: centerY - height / 2,
                width,
                height,
                right: centerX + width / 2,
                bottom: centerY + height / 2,
            };
        };
        const setItemCenterWithinMap = (item, centerX, centerY) => {
            const bounds = getVisualBounds(item);
            const halfVisualWidth = bounds.width / 2;
            const halfVisualHeight = bounds.height / 2;
            const nextCenterX = clamp(snapValue(centerX), halfVisualWidth, Math.max(halfVisualWidth, state.map.width - halfVisualWidth));
            const nextCenterY = clamp(snapValue(centerY), halfVisualHeight, Math.max(halfVisualHeight, state.map.height - halfVisualHeight));

            item.x = nextCenterX - Number(item.width || 0) / 2;
            item.y = nextCenterY - Number(item.height || 0) / 2;
        };
        const getAisleOrientation = (item) => [90, 270].includes(normalizeRotation(item?.rotation)) ? 'horizontal' : 'vertical';
        const getAisleVisualBounds = (item) => getVisualBounds(item);
        const rangesTouch = (startA, endA, startB, endB, tolerance = 0) => startA <= endB + tolerance && startB <= endA + tolerance;
        const rangeOverlap = (startA, endA, startB, endB) => Math.max(0, Math.min(endA, endB) - Math.max(startA, startB));
        const canMergeAisles = (first, second) => {
            if (!isAisleItem(first) || !isAisleItem(second) || String(first.id) === String(second.id)) {
                return false;
            }

            const firstOrientation = getAisleOrientation(first);
            const secondOrientation = getAisleOrientation(second);

            if (firstOrientation !== secondOrientation) {
                return false;
            }

            const tolerance = Math.max(4, Number(state.gridSize || 24) / 2);
            const firstBounds = getAisleVisualBounds(first);
            const secondBounds = getAisleVisualBounds(second);

            if (firstOrientation === 'horizontal') {
                return rangesTouch(firstBounds.x, firstBounds.right, secondBounds.x, secondBounds.right, tolerance)
                    && rangeOverlap(firstBounds.y, firstBounds.bottom, secondBounds.y, secondBounds.bottom) >= Math.min(firstBounds.height, secondBounds.height) * 0.45;
            }

            return rangesTouch(firstBounds.y, firstBounds.bottom, secondBounds.y, secondBounds.bottom, tolerance)
                && rangeOverlap(firstBounds.x, firstBounds.right, secondBounds.x, secondBounds.right) >= Math.min(firstBounds.width, secondBounds.width) * 0.45;
        };
        const applyAisleVisualBounds = (item, bounds) => {
            const rotation = normalizeRotation(item.rotation);
            const isSideways = [90, 270].includes(rotation);
            const centerX = bounds.x + bounds.width / 2;
            const centerY = bounds.y + bounds.height / 2;

            if (isSideways) {
                item.width = bounds.height;
                item.height = bounds.width;
            } else {
                item.width = bounds.width;
                item.height = bounds.height;
            }

            applyResizeRules(item);

            item.x = clamp(snapValue(centerX - Number(item.width || 0) / 2), 0, Math.max(0, state.map.width - item.width));
            item.y = clamp(snapValue(centerY - Number(item.height || 0) / 2), 0, Math.max(0, state.map.height - item.height));
        };
        const mergeConnectedAisles = (target) => {
            if (!isAisleItem(target)) {
                return false;
            }

            let merged = false;
            let keepMerging = true;

            while (keepMerging) {
                keepMerging = false;
                const other = state.items.find((item) => canMergeAisles(target, item));

                if (!other) {
                    continue;
                }

                const firstBounds = getAisleVisualBounds(target);
                const secondBounds = getAisleVisualBounds(other);
                const bounds = {
                    x: Math.min(firstBounds.x, secondBounds.x),
                    y: Math.min(firstBounds.y, secondBounds.y),
                    width: Math.max(firstBounds.right, secondBounds.right) - Math.min(firstBounds.x, secondBounds.x),
                    height: Math.max(firstBounds.bottom, secondBounds.bottom) - Math.min(firstBounds.y, secondBounds.y),
                };

                target.location_id = target.location_id || other.location_id || null;
                target.label = target.label || other.label || 'Lối đi';
                target.color = target.color || other.color;
                applyAisleVisualBounds(target, bounds);
                state.items = state.items.filter((item) => String(item.id) !== String(other.id));
                merged = true;
                keepMerging = true;
            }

            if (merged) {
                state.selectedId = String(target.id);
                autoExpandMapForItem(target);
            }

            return merged;
        };
        const normalizeConnectedAisles = () => {
            let merged = false;

            state.items.slice().forEach((item) => {
                const stillExists = state.items.some((current) => String(current.id) === String(item.id));

                if (stillExists && mergeConnectedAisles(item)) {
                    merged = true;
                }
            });

            return merged;
        };

        const duplicateMapItem = (source, offset = 32) => {
            if (!source || !isEditMode()) {
                return null;
            }

            const copy = normalizeItem({
                ...clone(source),
                id: makeTempId(),
                location_id: null,
                label: `${source.label || 'Item'} copy`,
                x: clamp(snapValue(Number(source.x || 0) + offset), 0, Math.max(0, state.map.width - Number(source.width || 0))),
                y: clamp(snapValue(Number(source.y || 0) + offset), 0, Math.max(0, state.map.height - Number(source.height || 0))),
                z_index: state.items.length + 1,
            }, state.items.length);

            applyResizeRules(copy);
            pushUndoSnapshot();
            state.items.push(copy);
            state.selectedId = String(copy.id);
            autoExpandMapForItem(copy);
            setDirty(true);
            render();
            centerCanvasOnItem(copy);

            return copy;
        };

        const beginCanvasPan = (event) => {
            if (!dom.viewport || event.button > 0) {
                return;
            }

            state.pan = {
                startX: event.clientX,
                startY: event.clientY,
                startScrollLeft: dom.viewport.scrollLeft,
                startScrollTop: dom.viewport.scrollTop,
                moved: false,
            };
            dom.viewport.classList.add('is-panning');
            event.preventDefault();
        };

        dom.canvas.addEventListener('pointerdown', (event) => {
            const itemElement = event.target.closest('[data-map-item-id]');

            if (!itemElement) {
                if (isEditMode() && state.drawMode && state.drawTool) {
                    createItemFromTool(state.drawTool, getCanvasPoint(event.clientX, event.clientY));
                    return;
                }

                state.selectedId = null;
                render();
                beginCanvasPan(event);
                return;
            }

            const itemId = itemElement.dataset.mapItemId;
            selectItem(itemId, { scrollTree: true });

            if (isEditMode()) {
                beginItemPointer(event, itemId, event.target.closest('[data-map-resize-handle]') ? 'resize' : 'move');
            } else {
                renderMapItemDetailModal();
            }
        });

        window.addEventListener('pointermove', (event) => {
            if (state.toolPointer) {
                const dx = Math.abs(event.clientX - state.toolPointer.startX);
                const dy = Math.abs(event.clientY - state.toolPointer.startY);

                if (dx + dy > 6) {
                    state.toolPointer.isDragging = true;
                    state.toolPointer.button?.classList.add('is-dragging');
                    dom.root.classList.add('is-tool-dragging');

                    if (dom.statusText) {
                        dom.statusText.textContent = 'Đang kéo module. Thả vào canvas để tạo vùng mới.';
                    }
                }

                return;
            }

            if (state.pan && dom.viewport) {
                const deltaX = event.clientX - state.pan.startX;
                const deltaY = event.clientY - state.pan.startY;

                if (Math.abs(deltaX) + Math.abs(deltaY) > 3) {
                    state.pan.moved = true;
                }

                dom.viewport.scrollLeft = state.pan.startScrollLeft - deltaX;
                dom.viewport.scrollTop = state.pan.startScrollTop - deltaY;
                event.preventDefault();
                return;
            }

            if (!state.drag) {
                return;
            }

            const item = getItem(state.drag.itemId);
            if (!item) {
                return;
            }

            const point = getCanvasPoint(event.clientX, event.clientY);
            const deltaX = point.x - state.drag.startPointer.x;
            const deltaY = point.y - state.drag.startPointer.y;

            if (state.drag.mode === 'resize') {
                if (isAisleItem(item)) {
                    resizeAisleFromHandle(item, state.drag, point);
                } else {
                    item.width = clamp(snapValue(state.drag.startWidth + deltaX), 36, state.map.width);
                    item.height = clamp(snapValue(state.drag.startHeight + deltaY), 28, state.map.height);
                    applyResizeRules(item);
                    autoExpandMapForItem(item);
                }
            } else {
                setItemCenterWithinMap(
                    item,
                    Number(state.drag.startCenterX || 0) + deltaX,
                    Number(state.drag.startCenterY || 0) + deltaY
                );
                autoExpandMapForItem(item);
            }

            setDirty(true);
            render();
        });

        window.addEventListener('pointerup', (event) => {
            if (state.toolPointer) {
                const pointer = state.toolPointer;
                state.toolPointer = null;
                pointer.button?.classList.remove('is-dragging');
                dom.root.classList.remove('is-tool-dragging');

                if (pointer.isDragging) {
                    state.suppressToolClick = true;
                    window.setTimeout(() => {
                        state.suppressToolClick = false;
                    }, 150);

                    if (isPointInsideDropZone(event.clientX, event.clientY)) {
                        createItemFromTool(pointer.toolKey, getCanvasPoint(event.clientX, event.clientY));
                    }
                }
            }

            if (state.pan) {
                state.pan = null;
                dom.viewport?.classList.remove('is-panning');
            }

            const activeDrag = state.drag;
            state.drag = null;

            if (activeDrag) {
                const item = getItem(activeDrag.itemId);

                if (item && mergeConnectedAisles(item)) {
                    setDirty(true);
                    render();
                }
            }
        });

        dom.canvas.addEventListener('dragover', (event) => {
            if (isEditMode()) {
                event.preventDefault();
            }
        });

        dom.canvas.addEventListener('drop', (event) => {
            if (!isEditMode()) {
                return;
            }

            event.preventDefault();
            const toolKey = event.dataTransfer?.getData('text/plain');

            if (toolKey) {
                createItemFromTool(toolKey, getCanvasPoint(event.clientX, event.clientY));
            }
        });

        $$('[data-map-tool]', dom.root).forEach((button) => {
            button.addEventListener('pointerdown', (event) => {
                if (!isEditMode() || event.button > 0) {
                    return;
                }

                state.toolPointer = {
                    toolKey: button.dataset.mapTool,
                    startX: event.clientX,
                    startY: event.clientY,
                    isDragging: false,
                    button,
                };
            });

            button.addEventListener('dragstart', (event) => {
                if (!isEditMode()) {
                    event.preventDefault();
                    return;
                }

                event.dataTransfer?.setData('text/plain', button.dataset.mapTool || '');
            });

            button.addEventListener('click', (event) => {
                event.preventDefault();

                if (!isEditMode() || state.suppressToolClick) {
                    return;
                }

                if (state.drawMode) {
                    state.drawTool = button.dataset.mapTool;
                    updateModeState();
                    return;
                }

                createItemFromTool(button.dataset.mapTool);
            });
        });

        document.addEventListener('click', (event) => {
            const node = event.target.closest('[data-location-node]');

            if (!node || event.target.closest('a, button, input, select, textarea, label')) {
                return;
            }

            const locationId = Number(node.dataset.locationId);
            const linkedItem = state.items.find((item) => Number(item.location_id) === locationId);
            setActiveLocationNode(locationId, false);
            updateUrlLocation(locationId);

            if (linkedItem) {
                selectItem(linkedItem.id, { center: true });
            } else {
                notify('Vị trí này chưa được gắn với vùng nào trên sơ đồ.', 'error');
            }
        });

        $$('[data-map-mode-button]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                state.mode = button.dataset.mapModeButton === 'edit' && mapEditorConfig.can_manage ? 'edit' : 'view';
                render();
            });
        });

        $$('[data-map-draw-toggle]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                state.drawMode = !state.drawMode;
                state.drawTool = state.drawMode ? (state.drawTool || Object.keys(toolsByKey)[0] || null) : null;
                updateModeState();
            });
        });

        $$('[data-map-snap-toggle]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                state.snapEnabled = !state.snapEnabled;
                updateModeState();
            });
        });

        $$('[data-map-grid-size]', dom.root).forEach((select) => {
            select.addEventListener('change', () => {
                pushUndoSnapshot();
                state.gridSize = Number(select.value || 24);
                state.items.forEach((item) => {
                    if (['aisle', 'simple_shelf'].includes(getModuleType(item))) {
                        applyResizeRules(item);
                    }
                });
                setDirty(true);
                render();
                updateModeState();
            });
        });

        $$('[data-map-zoom-in]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                setZoom(state.zoom + 0.1);
            });
        });

        $$('[data-map-zoom-out]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                setZoom(state.zoom - 0.1);
            });
        });

        dom.viewport?.addEventListener('wheel', (event) => {
            if (!event.ctrlKey && !event.metaKey) {
                return;
            }

            event.preventDefault();
            const direction = event.deltaY < 0 ? 1 : -1;
            setZoom(state.zoom + (direction * 0.1), event);
        }, { passive: false });

        $$('[data-map-size-apply]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                const width = Number(dom.mapSizeWidthInputs[0]?.value || state.map.width);
                const height = Number(dom.mapSizeHeightInputs[0]?.value || state.map.height);
                pushUndoSnapshot();
                const changed = setMapSize(width, height);

                if (changed) {
                    render();
                }
            });
        });

        $$('[data-map-auto-expand]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                if (!isEditMode()) {
                    return;
                }

                const required = getRequiredMapSize();
                pushUndoSnapshot();
                const changed = setMapSize(required.width, required.height);

                if (changed) {
                    render();
                    notify('Đã nới canvas theo bố cục hiện tại.');
                }
            });
        });

        $$('[data-map-rotate-90]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                const item = getItem();

                if (!isEditMode() || !item) {
                    return;
                }

                pushUndoSnapshot();
                if (!rotateItem90(item)) {
                    notify('Chọn một khối trên sơ đồ trước khi xoay.', 'error');
                    return;
                }

                mergeConnectedAisles(item);
                setDirty(true);
                render();
            });
        });

        $$('[data-map-delete]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                if (!isEditMode() || !state.selectedId) {
                    return;
                }

                pushUndoSnapshot();
                state.items = state.items.filter((item) => String(item.id) !== String(state.selectedId));
                state.selectedId = null;
                setDirty(true);
                render();
            });
        });

        $$('[data-map-duplicate]', dom.root).forEach((button) => {
            button.addEventListener('click', () => {
                const item = getItem();

                if (!isEditMode() || !item) {
                    return;
                }

                duplicateMapItem(item);
            });
        });

        $$('[data-map-show-detail]', dom.root).forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                renderMapItemDetailModal();
            });
        });

        document.addEventListener('keydown', (event) => {
            if (!isEditMode() || isTypingTarget(event.target) || (!event.ctrlKey && !event.metaKey)) {
                return;
            }

            const key = event.key.toLowerCase();

            if (key === 'z') {
                event.preventDefault();
                restoreUndoSnapshot();
                return;
            }

            if (key === 'c') {
                const item = getItem();

                if (!item) {
                    return;
                }

                state.clipboardItem = clone(item);
                state.clipboardPasteCount = 0;
                event.preventDefault();
                notify('Đã sao chép khối đang chọn.');
                return;
            }

            if (key === 'v') {
                if (!state.clipboardItem) {
                    return;
                }

                state.clipboardPasteCount += 1;
                event.preventDefault();
                duplicateMapItem(state.clipboardItem, Math.max(24, Number(state.gridSize || 24)) * 2 * state.clipboardPasteCount);
            }
        });

        dom.inspectorContent?.addEventListener('input', (event) => {
            const field = event.target.closest('[data-inspector-field], [data-inspector-meta]');
            const item = getItem();

            if (!field || !item || !isEditMode()) {
                return;
            }

            pushUndoSnapshot();

            if (field.dataset.inspectorMeta) {
                item.meta_json = item.meta_json || {};
                const value = field.type === 'checkbox'
                    ? Boolean(field.checked)
                    : (field.value === '' ? null : (Number.isNaN(Number(field.value)) ? field.value : Number(field.value)));
                item.meta_json[field.dataset.inspectorMeta] = value;

                if (['positions_per_level', 'width_count', 'height_count', 'length_count', 'column_count'].includes(field.dataset.inspectorMeta)) {
                    applyResizeRules(item);
                    autoExpandMapForItem(item);
                }
            } else {
                const key = field.dataset.inspectorField;

                if (key === 'item_type_label') {
                    return;
                }

                if (key === 'location_id') {
                    item.location_id = field.value ? Number(field.value) : null;
                    setActiveLocationNode(item.location_id, false);
                    updateUrlLocation(item.location_id);
                } else if (key === 'is_clickable') {
                    item.is_clickable = Boolean(field.checked);
                } else if (['x', 'y', 'width', 'height', 'rotation'].includes(key)) {
                    item[key] = Number(field.value || 0);
                    if (key === 'width' || key === 'height') {
                        item[key] = Math.max(key === 'width' ? 36 : 28, item[key]);
                        applyResizeRules(item);
                        autoExpandMapForItem(item);
                    } else if ((key === 'x' || key === 'y') && state.snapEnabled) {
                        item[key] = snapValue(item[key]);
                        autoExpandMapForItem(item);
                    }
                } else {
                    item[key] = field.value;
                }
            }

            setDirty(true);
            render();
        });

        dom.inspectorContent?.addEventListener('change', (event) => {
            if (event.target.closest('[data-inspector-field], [data-inspector-meta]')) {
                event.target.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        const saveMapLayout = async (button = null, successMessage = null) => {
            if (!isEditMode() || !state.map.sync_url) {
                return false;
            }

            if (button) {
                button.disabled = true;
            }

            try {
                const response = await fetch(state.map.sync_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        map_width: Math.round(state.map.width),
                        map_height: Math.round(state.map.height),
                        items: state.items.map((item, index) => ({
                            id: isNumericId(item.id) ? Number(item.id) : null,
                            location_id: item.location_id || null,
                            item_type: item.item_type,
                            label: item.label,
                            shape_type: item.shape_type,
                            x: Math.round(item.x),
                            y: Math.round(item.y),
                            width: Math.round(item.width),
                            height: Math.round(item.height),
                            rotation: normalizeRotation(item.rotation),
                            color: item.color,
                            z_index: index + 1,
                            is_clickable: Boolean(item.is_clickable),
                            meta_json: clone(item.meta_json || {}),
                        })),
                    }),
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || payload.error || 'Không thể lưu layout sơ đồ kho.');
                }

                if (payload.map) {
                    state.map.width = Number(payload.map.width || state.map.width);
                    state.map.height = Number(payload.map.height || state.map.height);
                }

                state.items = (payload.data || []).map((item, index) => normalizeItem(item, index));
                const selected = getItem(state.selectedId);
                state.selectedId = selected ? String(selected.id) : null;
                state.mode = 'view';
                state.drawMode = false;
                state.drawTool = null;
                syncLocationOptions(payload.locations || []);
                setDirty(false);
                render();
                notify(successMessage || payload.message || 'Đã lưu bố cục sơ đồ kho.');

                return true;
            } catch (error) {
                notify(error.message || 'Không thể lưu layout sơ đồ kho.', 'error');

                return false;
            } finally {
                if (button) {
                    button.disabled = false;
                    updateModeState();
                }
            }
        };

        $$('[data-map-save]', dom.root).forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.preventDefault();
                await saveMapLayout(button);
            });
        });

        $$('[data-map-sync-levels]', dom.root).forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.preventDefault();

                const item = getItem();

                if (!isEditMode() || !item) {
                    return;
                }

                if (!isRackLikeMapItem(item)) {
                    notify('Chọn kệ hoặc rack trên sơ đồ trước khi tạo tầng.', 'error');
                    return;
                }

                await saveMapLayout(button, 'Đã tạo/cập nhật vị trí lưu trữ theo layout.');
            });
        });

        if (state.selectedLocationId && !state.selectedId) {
            const linkedItem = state.items.find((item) => Number(item.location_id) === state.selectedLocationId);
            if (linkedItem) {
                state.selectedId = String(linkedItem.id);
            }
        }

        state.items.forEach((item) => {
            if (getModuleType(item) === 'simple_shelf') {
                applyResizeRules(item);
            }
        });

        if (isEditMode() && normalizeConnectedAisles()) {
            setDirty(true);
        }

        const requiredMapSize = getRequiredMapSize();
        setMapSize(requiredMapSize.width, requiredMapSize.height, { dirty: false });

        render();
    })();
</script>
