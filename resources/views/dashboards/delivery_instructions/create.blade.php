@extends('layouts.dashboard')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-panel {
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .form-input, .form-select {
            width: 100%;
            box-sizing: border-box;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            transition: all 0.2s;
        }

        [data-theme="light"] .form-input,
        [data-theme="light"] .form-select {
            background: rgba(0, 0, 0, 0.02);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-select option {
            background: var(--bg-color, #1e1e2d);
            color: var(--text-primary);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .items-table th {
            padding: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .items-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }

        .remove-row {
            color: var(--danger);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border-radius: 8px;
        }

        .remove-row:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .hint-text {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .sn-dropdown-menu label:hover {
            background: rgba(99, 102, 241, 0.15);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-plus-circle"></i> Create Delivery Instruction
        </h1>
        <a href="{{ route('delivery-instructions.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="glass" style="padding: 1rem; margin-bottom: 1rem; border-left: 4px solid var(--danger); background: rgba(239, 68, 68, 0.1);">
            <ul style="margin: 0; padding-left: 1.5rem; color: var(--danger);">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass form-panel">
        <form action="{{ route('delivery-instructions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="section-title">General Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Delivery Instruction Number *</label>
                    <input type="text" name="di_number" class="form-input" required 
                           value="{{ old('di_number', isset($parentDi) ? 'DI-REF-'.rand(1000, 9999) : 'DI-'.date('Ymd').'-'.rand(100,999)) }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Customer / Destination Name *</label>
                    <input type="text" name="customer_name" class="form-input" required 
                           value="{{ old('customer_name', isset($parentDi) ? $parentDi->customer_name : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">End User Name</label>
                    <input type="text" name="end_user_name" class="form-input" placeholder="e.g. Acme Health System" 
                           value="{{ old('end_user_name', isset($parentDi) ? $parentDi->end_user_name : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">SO Reference Number</label>
                    <input type="text" name="so_reference" class="form-input" placeholder="e.g. SO-2026-991" 
                           value="{{ old('so_reference', isset($parentDi) ? $parentDi->so_reference : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Delivery Note Document</label>
                    <input type="file" name="delivery_note_attachment" class="form-input" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" style="padding: 0.5rem 0.75rem;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Delivery Address *</label>
                    <input type="text" name="delivery_address" class="form-input" required 
                           value="{{ old('delivery_address', isset($parentDi) ? $parentDi->delivery_address : '') }}">
                </div>
            </div>

            <div class="section-title">Requested Items List</div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.75rem; margin-bottom: 1rem;">
                <input type="file" id="csvUpload" accept=".csv" style="display: none;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('csvUpload').click()" style="padding: 0.4rem 0.85rem; font-size: 0.875rem;">
                    <i class="ph ph-upload-simple"></i> Bulk Upload CSV
                </button>
                <a href="{{ route('delivery-instructions.template') }}" class="btn btn-outline" style="padding: 0.4rem 0.85rem; font-size: 0.875rem;">
                    <i class="ph ph-download-simple"></i> Download Template
                </a>
            </div>
            
            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width: 25%;">SKU *</th>
                        <th style="width: 30%;">Product Name</th>
                        <th style="width: 30%;">Serial Numbers (Checkbox Select)</th>
                        <th style="width: 15%;">Quantity *</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @if(count($remainingItems) > 0)
                        @foreach($remainingItems as $index => $item)
                            <tr id="row-{{ $index }}">
                                <td>
                                    <div class="sku-searchable-container" id="sku-container-{{ $index }}" style="position: relative;">
                                        <button type="button" class="form-input sku-dropdown-btn" onclick="toggleSkuDropdown(event, {{ $index }})" style="display: flex; justify-content: space-between; align-items: center; text-align: left; background: rgba(255,255,255,0.05); cursor: pointer; width: 100%;">
                                            <span id="sku-btn-text-{{ $index }}" style="font-weight: 600;">{{ $item['sku_code'] ?: 'Select SKU Code' }}</span>
                                            <i class="ph ph-caret-down" style="margin-left: 0.5rem;"></i>
                                        </button>
                                        <div id="sku-dropdown-menu-{{ $index }}" class="sku-dropdown-menu glass" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; max-height: 250px; overflow-y: auto; padding: 0.5rem; background: var(--bg-color, #1e1e2d); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); margin-top: 0.25rem;">
                                            <div style="position: sticky; top: 0; background: var(--bg-color, #1e1e2d); padding-bottom: 0.4rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--border-color); z-index: 10;">
                                                <input type="text" class="form-input" placeholder="🔍 Search SKU..." oninput="filterSkuOptions(this, {{ $index }})" onclick="event.stopPropagation()" style="padding: 0.4rem 0.6rem; font-size: 0.85rem; width: 100%; box-sizing: border-box;">
                                            </div>
                                            <div class="sku-options-list-{{ $index }}" style="display: flex; flex-direction: column; gap: 0.2rem;">
                                                @foreach($products as $prod)
                                                    <div class="sku-option-item-{{ $index }}" onclick="selectSkuOption({{ $index }}, '{{ $prod->sku_code }}')" style="padding: 0.4rem 0.6rem; cursor: pointer; border-radius: 4px; font-weight: 500; font-size: 0.85rem; transition: background 0.15s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" onmouseout="this.style.background='transparent'">
                                                        {{ $prod->sku_code }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <input type="hidden" id="sku-hidden-input-{{ $index }}" name="items[{{ $index }}][sku_code]" value="{{ $item['sku_code'] }}" required>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][description]" id="desc-{{ $index }}" class="form-input" placeholder="Product Name" value="{{ $item['description'] ?? '' }}">
                                </td>
                                <td>
                                    <div id="sn-cell-{{ $index }}">
                                        <input type="text" name="items[{{ $index }}][serial_numbers]" id="sn-input-{{ $index }}" class="form-input" placeholder="e.g. SN1, SN2" value="{{ $item['serial_numbers'] ?? '' }}">
                                        <span class="hint-text">Select SKU to load serial numbers dropdown</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]" id="qty-{{ $index }}" class="form-input" required min="1" placeholder="Qty" value="{{ $item['quantity'] }}">
                                </td>
                                <td>
                                    <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr id="row-0">
                            <td>
                                <div class="sku-searchable-container" id="sku-container-0" style="position: relative;">
                                    <button type="button" class="form-input sku-dropdown-btn" onclick="toggleSkuDropdown(event, 0)" style="display: flex; justify-content: space-between; align-items: center; text-align: left; background: rgba(255,255,255,0.05); cursor: pointer; width: 100%;">
                                        <span id="sku-btn-text-0" style="font-weight: 600;">Select SKU Code</span>
                                        <i class="ph ph-caret-down" style="margin-left: 0.5rem;"></i>
                                    </button>
                                    <div id="sku-dropdown-menu-0" class="sku-dropdown-menu glass" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; max-height: 250px; overflow-y: auto; padding: 0.5rem; background: var(--bg-color, #1e1e2d); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); margin-top: 0.25rem;">
                                        <div style="position: sticky; top: 0; background: var(--bg-color, #1e1e2d); padding-bottom: 0.4rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--border-color); z-index: 10;">
                                            <input type="text" class="form-input" placeholder="🔍 Search SKU..." oninput="filterSkuOptions(this, 0)" onclick="event.stopPropagation()" style="padding: 0.4rem 0.6rem; font-size: 0.85rem; width: 100%; box-sizing: border-box;">
                                        </div>
                                        <div class="sku-options-list-0" style="display: flex; flex-direction: column; gap: 0.2rem;">
                                            @foreach($products as $prod)
                                                <div class="sku-option-item-0" onclick="selectSkuOption(0, '{{ $prod->sku_code }}')" style="padding: 0.4rem 0.6rem; cursor: pointer; border-radius: 4px; font-weight: 500; font-size: 0.85rem; transition: background 0.15s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" onmouseout="this.style.background='transparent'">
                                                    {{ $prod->sku_code }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <input type="hidden" id="sku-hidden-input-0" name="items[0][sku_code]" value="" required>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="items[0][description]" id="desc-0" class="form-input" placeholder="Product Name">
                            </td>
                            <td>
                                <div id="sn-cell-0">
                                    <input type="text" name="items[0][serial_numbers]" id="sn-input-0" class="form-input" placeholder="e.g. SN1, SN2">
                                    <span class="hint-text">Select SKU to load serial numbers dropdown</span>
                                </div>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" id="qty-0" class="form-input" required min="1" placeholder="Qty" value="1">
                            </td>
                            <td>
                                <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            <button type="button" class="btn btn-outline" onclick="addRow()">
                <i class="ph ph-plus"></i> Add SKU
            </button>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check-circle"></i> Validate & Submit
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let rowCount = {{ count($remainingItems) > 0 ? count($remainingItems) : 1 }};

            const productsData = {
                @foreach($products as $prod)
                    @php
                        $serialsArr = $prod->serial_number ? array_values(array_filter(array_map('trim', explode(',', $prod->serial_number)))) : [];
                    @endphp
                    "{{ $prod->sku_code }}": {
                        "name": @json($prod->name),
                        "serials": @json($serialsArr)
                    },
                @endforeach
            };

            function toggleSkuDropdown(e, idx) {
                e.stopPropagation();
                const menu = document.getElementById(`sku-dropdown-menu-${idx}`);
                if (!menu) return;
                const isVisible = menu.style.display === 'block';

                document.querySelectorAll('.sku-dropdown-menu').forEach(m => m.style.display = 'none');
                document.querySelectorAll('.sn-dropdown-menu').forEach(m => m.style.display = 'none');

                if (!isVisible) {
                    menu.style.display = 'block';
                    const searchInput = menu.querySelector('input');
                    if (searchInput) {
                        searchInput.value = '';
                        filterSkuOptions(searchInput, idx);
                        setTimeout(() => searchInput.focus(), 50);
                    }
                }
            }

            function filterSkuOptions(input, idx) {
                const filter = input.value.toLowerCase();
                const list = document.querySelectorAll(`.sku-option-item-${idx}`);
                list.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? 'block' : 'none';
                });
            }

            function selectSkuOption(idx, skuCode) {
                const hiddenInput = document.getElementById(`sku-hidden-input-${idx}`);
                const btnText = document.getElementById(`sku-btn-text-${idx}`);
                const menu = document.getElementById(`sku-dropdown-menu-${idx}`);

                if (hiddenInput) hiddenInput.value = skuCode;
                if (btnText) btnText.textContent = skuCode;
                if (menu) menu.style.display = 'none';

                onSkuSelect(skuCode, idx);
            }

            function onSkuSelect(sku, idx, preselectedSerials = '') {
                const descInput = document.getElementById(`desc-${idx}`);
                const qtyInput = document.getElementById(`qty-${idx}`);
                const snCell = document.getElementById(`sn-cell-${idx}`);

                if (!sku || !productsData[sku]) {
                    snCell.innerHTML = `
                        <input type="text" name="items[${idx}][serial_numbers]" id="sn-input-${idx}" class="form-input" placeholder="e.g. SN1, SN2">
                        <span class="hint-text">Select SKU to load serial numbers dropdown</span>
                    `;
                    return;
                }

                const product = productsData[sku];
                if (descInput && (!descInput.value || descInput.dataset.autofilled === "true")) {
                    descInput.value = product.name;
                    descInput.dataset.autofilled = "true";
                }

                const serials = product.serials || [];

                if (serials.length > 0) {
                    const preselectedArr = preselectedSerials ? preselectedSerials.split(',').map(s => s.trim()) : [];
                    
                    let optionsHtml = serials.map((sn, sIdx) => {
                        const isChecked = preselectedArr.includes(sn) ? 'checked' : '';
                        return `
                            <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.5rem; cursor: pointer; color: var(--text-primary); margin: 0; border-radius: 4px;">
                                <input type="checkbox" value="${sn}" class="sn-cb-${idx} form-checkbox" ${isChecked} onchange="onSnCheckboxChange(${idx})" style="width: 16px; height: 16px; cursor: pointer;">
                                <span style="font-family: monospace; font-size: 0.85rem;">${sn}</span>
                            </label>
                        `;
                    }).join('');

                    snCell.innerHTML = `
                        <div class="sn-multiselect-container" style="position: relative;">
                            <button type="button" class="form-input sn-dropdown-btn" onclick="toggleSnDropdown(event, ${idx})" style="display: flex; justify-content: space-between; align-items: center; text-align: left; background: rgba(255,255,255,0.05); cursor: pointer; width: 100%;">
                                <span id="sn-btn-text-${idx}" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem;">Select Serial Numbers (0 selected)</span>
                                <i class="ph ph-caret-down" style="margin-left: 0.5rem;"></i>
                            </button>
                            <div id="sn-dropdown-menu-${idx}" class="sn-dropdown-menu glass" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 150; max-height: 220px; overflow-y: auto; padding: 0.5rem; background: var(--bg-color, #1e1e2d); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); margin-top: 0.25rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.4rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--border-color); font-size: 0.75rem; color: var(--text-secondary);">
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 0.3rem; margin: 0;">
                                        <input type="checkbox" onchange="selectAllSerials(this, ${idx})" style="cursor: pointer;"> <strong>Select All (${serials.length})</strong>
                                    </label>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                    ${optionsHtml}
                                </div>
                            </div>
                            <input type="hidden" id="sn-input-${idx}" name="items[${idx}][serial_numbers]" value="${preselectedSerials}">
                        </div>
                    `;

                    onSnCheckboxChange(idx);
                } else {
                    snCell.innerHTML = `
                        <input type="text" name="items[${idx}][serial_numbers]" id="sn-input-${idx}" class="form-input" placeholder="No predefined serials found. Type manually..." value="${preselectedSerials}">
                    `;
                }
            }

            function toggleSnDropdown(e, idx) {
                e.stopPropagation();
                const menu = document.getElementById(`sn-dropdown-menu-${idx}`);
                if (!menu) return;
                const isVisible = menu.style.display === 'block';

                document.querySelectorAll('.sku-dropdown-menu').forEach(m => m.style.display = 'none');
                document.querySelectorAll('.sn-dropdown-menu').forEach(m => m.style.display = 'none');

                if (!isVisible) {
                    menu.style.display = 'block';
                }
            }

            function selectAllSerials(masterCb, idx) {
                const checkboxes = document.querySelectorAll(`.sn-cb-${idx}`);
                checkboxes.forEach(cb => {
                    cb.checked = masterCb.checked;
                });
                onSnCheckboxChange(idx);
            }

            function onSnCheckboxChange(idx) {
                const checkboxes = document.querySelectorAll(`.sn-cb-${idx}`);
                const selected = [];
                checkboxes.forEach(cb => {
                    if (cb.checked) selected.push(cb.value);
                });

                const btnText = document.getElementById(`sn-btn-text-${idx}`);
                const hiddenInput = document.getElementById(`sn-input-${idx}`);
                const qtyInput = document.getElementById(`qty-${idx}`);

                if (hiddenInput) {
                    hiddenInput.value = selected.join(', ');
                }

                if (btnText) {
                    if (selected.length === 0) {
                        btnText.textContent = 'Select Serial Numbers (0 selected)';
                    } else {
                        btnText.textContent = `${selected.length} Selected (${selected.join(', ')})`;
                    }
                }

                if (qtyInput && selected.length > 0) {
                    qtyInput.value = selected.length;
                }
            }

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.sku-searchable-container')) {
                    document.querySelectorAll('.sku-dropdown-menu').forEach(m => m.style.display = 'none');
                }
                if (!e.target.closest('.sn-multiselect-container')) {
                    document.querySelectorAll('.sn-dropdown-menu').forEach(m => m.style.display = 'none');
                }
            });

            function addRow(sku = '', description = '', qty = 1, serials = '') {
                const tbody = document.getElementById('itemsBody');
                const tr = document.createElement('tr');
                tr.id = `row-${rowCount}`;

                let optionsListHtml = '';
                @foreach($products as $prod)
                    optionsListHtml += `
                        <div class="sku-option-item-${rowCount}" onclick="selectSkuOption(${rowCount}, '{{ $prod->sku_code }}')" style="padding: 0.4rem 0.6rem; cursor: pointer; border-radius: 4px; font-weight: 500; font-size: 0.85rem; transition: background 0.15s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" onmouseout="this.style.background='transparent'">
                            {{ $prod->sku_code }}
                        </div>
                    `;
                @endforeach

                tr.innerHTML = `
                    <td>
                        <div class="sku-searchable-container" id="sku-container-${rowCount}" style="position: relative;">
                            <button type="button" class="form-input sku-dropdown-btn" onclick="toggleSkuDropdown(event, ${rowCount})" style="display: flex; justify-content: space-between; align-items: center; text-align: left; background: rgba(255,255,255,0.05); cursor: pointer; width: 100%;">
                                <span id="sku-btn-text-${rowCount}" style="font-weight: 600;">${sku || 'Select SKU Code'}</span>
                                <i class="ph ph-caret-down" style="margin-left: 0.5rem;"></i>
                            </button>
                            <div id="sku-dropdown-menu-${rowCount}" class="sku-dropdown-menu glass" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; max-height: 250px; overflow-y: auto; padding: 0.5rem; background: var(--bg-color, #1e1e2d); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); margin-top: 0.25rem;">
                                <div style="position: sticky; top: 0; background: var(--bg-color, #1e1e2d); padding-bottom: 0.4rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--border-color); z-index: 10;">
                                    <input type="text" class="form-input" placeholder="🔍 Search SKU..." oninput="filterSkuOptions(this, ${rowCount})" onclick="event.stopPropagation()" style="padding: 0.4rem 0.6rem; font-size: 0.85rem; width: 100%; box-sizing: border-box;">
                                </div>
                                <div class="sku-options-list-${rowCount}" style="display: flex; flex-direction: column; gap: 0.2rem;">
                                    ${optionsListHtml}
                                </div>
                            </div>
                            <input type="hidden" id="sku-hidden-input-${rowCount}" name="items[${rowCount}][sku_code]" value="${sku}" required>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="items[${rowCount}][description]" id="desc-${rowCount}" class="form-input" placeholder="Product Name" value="${description}">
                    </td>
                    <td>
                        <div id="sn-cell-${rowCount}">
                            <input type="text" name="items[${rowCount}][serial_numbers]" id="sn-input-${rowCount}" class="form-input" placeholder="e.g. SN1, SN2" value="${serials}">
                            <span class="hint-text">Select SKU to load serial numbers dropdown</span>
                        </div>
                    </td>
                    <td>
                        <input type="number" name="items[${rowCount}][quantity]" id="qty-${rowCount}" class="form-input" required min="1" placeholder="Qty" value="${qty}">
                    </td>
                    <td>
                        <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                            <i class="ph ph-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);

                if (sku) {
                    selectSkuOption(rowCount, sku);
                }

                rowCount++;
            }

            document.getElementById('csvUpload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(event) {
                    const text = event.target.result;
                    const lines = text.split('\n');
                    if (lines.length === 0) return;
                    
                    const headerLine = lines[0].toLowerCase();
                    const startIndex = headerLine.includes('sku') ? 1 : 0;
                    
                    const tbody = document.getElementById('itemsBody');
                    const rows = tbody.querySelectorAll('tr');
                    if (rows.length === 1) {
                        const skuHidden = rows[0].querySelector('[name*="[sku_code]"]');
                        if (skuHidden && !skuHidden.value) {
                            tbody.innerHTML = '';
                        }
                    }
                    
                    let addedCount = 0;
                    for (let i = startIndex; i < lines.length; i++) {
                        if (!lines[i].trim()) continue;
                        
                        let cols = [];
                        let insideQuote = false;
                        let entry = "";
                        const lineText = lines[i];
                        for (let j = 0; j < lineText.length; j++) {
                            let char = lineText[j];
                            if (char === '"') {
                                insideQuote = !insideQuote;
                            } else if (char === ',' && !insideQuote) {
                                cols.push(entry.trim());
                                entry = "";
                            } else {
                                entry += char;
                            }
                        }
                        cols.push(entry.trim());
                        
                        let sku = cols[0] ? cols[0].replace(/^"|"$/g, '').trim() : '';
                        let desc = '';
                        let qty = 1;
                        let serials = '';
                        
                        if (cols.length >= 4) {
                            desc = cols[1] ? cols[1].replace(/^"|"$/g, '').trim() : '';
                            qty = parseInt(cols[2] ? cols[2].replace(/^"|"$/g, '').trim() : '') || 1;
                            serials = cols[3] ? cols[3].replace(/^"|"$/g, '').trim() : '';
                        } else if (cols.length === 3) {
                            const val1 = cols[1] ? cols[1].replace(/^"|"$/g, '').trim() : '';
                            const val2 = cols[2] ? cols[2].replace(/^"|"$/g, '').trim() : '';
                            const isSecondNum = !isNaN(parseInt(val1));
                            if (isSecondNum) {
                                qty = parseInt(val1) || 1;
                                serials = val2;
                            } else {
                                desc = val1;
                                qty = parseInt(val2) || 1;
                            }
                        } else if (cols.length === 2) {
                            const val1 = cols[1] ? cols[1].replace(/^"|"$/g, '').trim() : '';
                            const isSecondNum = !isNaN(parseInt(val1));
                            if (isSecondNum) {
                                qty = parseInt(val1) || 1;
                            } else {
                                desc = val1;
                            }
                        }
                        
                        if (sku) {
                            addRow(sku, desc, qty, serials);
                            addedCount++;
                        }
                    }
                    
                    alert(`Successfully added ${addedCount} items from CSV.`);
                    e.target.value = '';
                };
                reader.readAsText(file);
            });

            @if(count($remainingItems) > 0)
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('[id^="sku-hidden-input-"]').forEach((hidden, idx) => {
                        if (hidden.value) {
                            onSkuSelect(hidden.value, idx, hidden.closest('tr').querySelector('[name*="[serial_numbers]"]')?.value || '');
                        }
                    });
                });
            @endif
        </script>
    @endpush
@endsection
