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

        .form-input {
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

        [data-theme="light"] .form-input {
            background: rgba(0, 0, 0, 0.02);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
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
            padding: 0.5rem;
        }
        
        .items-table input {
            width: 100%;
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

        .multiselect-options {
            display: flex;
            flex-direction: column;
            gap: 2px;
            background: #ffffff !important;
            border: 1px solid rgba(0, 0, 0, 0.15) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .sn-option-label {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            width: 100%;
            box-sizing: border-box;
            padding: 0.4rem 0.6rem;
            cursor: pointer;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #1e293b !important;
            white-space: nowrap;
            transition: background 0.15s ease;
        }

        .sn-option-label:hover {
            background: rgba(99, 102, 241, 0.12) !important;
        }

        [data-theme="dark"] .multiselect-options {
            background: #1e212b !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        [data-theme="dark"] .sn-option-label {
            color: #f8f9fa !important;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-pencil-simple"></i>
            Edit Advance Shipping Note
        </h1>
        <a href="{{ route('asns.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to ASNs
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
        <form action="{{ route('asns.update', $asn->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="section-title">General Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">ASN Reference Number *</label>
                    <input type="text" name="asn_reference" class="form-input" required value="{{ old('asn_reference', $asn->asn_reference) }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Airway Bill Number *</label>
                    <input type="text" name="airway_bill" class="form-input" required value="{{ old('airway_bill', $asn->airway_bill) }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Vendor / Supplier *</label>
                    <select name="vendor_id" class="form-input" required style="appearance: auto; -webkit-appearance: auto;">
                        <option value="Solutions Four W.L.L" {{ old('vendor_id', $asn->vendor_id) == 'Solutions Four W.L.L' ? 'selected' : '' }}>Solutions Four W.L.L</option>
                    </select>
                </div>
            </div>

            <div class="section-title">
                Equipment List
                <div>
                    <input type="file" id="csvUpload" accept=".csv" style="display: none;">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('csvUpload').click()" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                        <i class="ph ph-upload-simple"></i> Bulk Upload CSV
                    </button>
                    <a href="{{ route('asns.template') }}" class="btn btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                        <i class="ph ph-download-simple"></i> Download Template
                    </a>
                </div>
            </div>
            
            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product / SKU *</th>
                        <th>Serial Numbers</th>
                        <th>Quantity *</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @foreach($asn->items as $index => $item)
                    <tr>
                        <td>
                            <input type="text" name="items[{{ $index }}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU" value="{{ old('items.'.$index.'.sku_code', $item->sku_code) }}">
                        </td>
                        <td>
                            <input type="text" name="items[{{ $index }}][serial_numbers]" class="form-input" placeholder="Comma-separated serials (e.g. SN-1, SN-2)" value="{{ old('items.'.$index.'.serial_numbers', $item->serial_numbers) }}">
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][quantity]" class="form-input" required min="1" placeholder="Qty" value="{{ old('items.'.$index.'.quantity', $item->quantity) }}">
                        </td>
                        <td>
                            <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <button type="button" class="btn btn-outline" onclick="addRow()">
                <i class="ph ph-plus"></i> Add Line Item
            </button>

            <div class="section-title" style="margin-top: 2rem;">Attachments & Remarks</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Upload Scanned Airway Bill (Leave empty to keep current)</label>
                    @if($asn->airway_bill_path)
                        <span style="font-size: 0.8rem; color: var(--success);"><i class="ph ph-check-circle"></i> File uploaded</span>
                    @endif
                    <input type="file" name="airway_bill_file" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Additional Attachments (Leave empty to keep current)</label>
                    @if($asn->additional_attachments_path)
                        <span style="font-size: 0.8rem; color: var(--success);"><i class="ph ph-check-circle"></i> File uploaded</span>
                    @endif
                    <input type="file" name="additional_file" class="form-input">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Supporting Remarks</label>
                <textarea name="remarks" class="form-input" rows="4">{{ old('remarks', $asn->remarks) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" name="status" value="draft" class="btn btn-outline">
                    <i class="ph ph-floppy-disk"></i> Save as Draft
                </button>
                <button type="submit" name="status" value="submitted" class="btn btn-primary">
                    <i class="ph ph-paper-plane-right"></i> Submit ASN
                </button>
            </div>
        </form>
        <datalist id="products-list">
            @foreach($products as $prod)
                <option value="{{ $prod->sku_code }}">{{ $prod->sku_code }} - {{ $prod->name }}</option>
            @endforeach
        </datalist>
    </div>

    <script>
        let rowCount = {{ $asn->items->count() > 0 ? $asn->items->count() : 1 }};
        const productsData = @json($products);
        const productsMap = {};

        productsData.forEach(p => {
            let serials = [];
            if (p.serial_number) {
                serials = p.serial_number.split(',').map(s => s.trim()).filter(Boolean);
            }
            productsMap[p.sku_code] = {
                name: p.name,
                qty: p.qty,
                type: p.type,
                serials: serials,
                totalAvailableQty: serials.length > 0 ? serials.length : p.qty
            };
        });

        function getSelectedSerialsAcrossRows(currentSelect = null) {
            const selected = new Set();
            document.querySelectorAll('.serial-select').forEach(sel => {
                if (sel !== currentSelect && sel.value) {
                    selected.add(sel.value.toLowerCase());
                }
            });
            return selected;
        }

        function refreshAllSerialDropdowns() {
            document.querySelectorAll('#itemsBody tr').forEach(tr => {
                const skuInput = tr.querySelector('[name$="[sku_code]"]');
                const selectElem = tr.querySelector('.serial-select');
                if (skuInput && selectElem) {
                    const sku = skuInput.value.trim();
                    const product = productsMap[sku];
                    if (product && product.serials.length > 0) {
                        const usedSerials = getSelectedSerialsAcrossRows(selectElem);
                        Array.from(selectElem.options).forEach(opt => {
                            if (!opt.value) return;
                            const isUsedElsewhere = usedSerials.has(opt.value.toLowerCase());
                            if (isUsedElsewhere) {
                                opt.disabled = true;
                                opt.text = `${opt.value} (Selected in another row)`;
                            } else {
                                opt.disabled = false;
                                opt.text = opt.value;
                            }
                        });
                    }
                }
            });
        }

        function handleSkuSelection(tr, defaultQty = '', defaultSerialsStr = '') {
            const skuInput = tr.querySelector('[name$="[sku_code]"]');
            const qtyInput = tr.querySelector('[name$="[quantity]"]');
            const serialCell = tr.children[1];
            if (!skuInput || !qtyInput || !serialCell) return;

            const sku = skuInput.value.trim();
            const product = productsMap[sku];

            let availSpan = qtyInput.parentNode.querySelector('.avail-qty-info');
            if (availSpan) availSpan.remove();

            if (product) {
                const availQty = product.totalAvailableQty;

                availSpan = document.createElement('span');
                availSpan.className = 'avail-qty-info';
                availSpan.style.color = 'var(--accent-primary)';
                availSpan.style.fontSize = '0.75rem';
                availSpan.style.display = 'block';
                availSpan.style.marginTop = '0.25rem';
                availSpan.style.fontWeight = '600';
                availSpan.innerHTML = `<i class="ph ph-check-circle"></i> Total Available: ${availQty}`;
                qtyInput.parentNode.appendChild(availSpan);

                const rowNameMatch = skuInput.name.match(/items\[(\w+)\]/);
                const rowIdx = rowNameMatch ? rowNameMatch[1] : 0;

                const existingSerialInput = serialCell.querySelector('input, .serials-hidden-input');
                const currentSerialsStr = defaultSerialsStr || (existingSerialInput ? existingSerialInput.value : '');

                if (product.serials.length > 0) {
                    let preSelectedSerials = currentSerialsStr ? currentSerialsStr.split(',').map(s => s.trim().toLowerCase()) : [];

                    let checkboxesHtml = '';
                    product.serials.forEach(sn => {
                        const isChecked = preSelectedSerials.length > 0
                            ? preSelectedSerials.includes(sn.toLowerCase())
                            : false;
                        checkboxesHtml += `
                            <label class="sn-option-label">
                                <input type="checkbox" value="${sn}" class="sn-checkbox" ${isChecked ? 'checked' : ''} style="accent-color: var(--accent-primary); width: 16px; height: 16px; cursor: pointer;">
                                <span style="font-weight: 500;">${sn}</span>
                            </label>
                        `;
                    });

                    serialCell.innerHTML = `
                        <div class="custom-multiselect" style="position: relative; width: 100%;">
                            <div class="form-input multiselect-trigger" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; min-height: 38px; padding: 0.4rem 0.75rem;">
                                <span class="selected-text" style="color: var(--text-primary); font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    -- Select Serial Numbers --
                                </span>
                                <i class="ph ph-caret-down" style="color: var(--text-secondary); font-size: 0.8rem; margin-left: 0.5rem;"></i>
                            </div>
                            <div class="multiselect-options" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 1000; max-height: 180px; overflow-y: auto; border-radius: 8px; padding: 0.35rem;">
                                ${checkboxesHtml}
                            </div>
                            <input type="hidden" name="items[${rowIdx}][serial_numbers]" value="${currentSerialsStr}" class="serials-hidden-input">
                        </div>
                    `;

                    const multiselect = serialCell.querySelector('.custom-multiselect');
                    const trigger = multiselect.querySelector('.multiselect-trigger');
                    const optionsPanel = multiselect.querySelector('.multiselect-options');
                    const hiddenInput = multiselect.querySelector('.serials-hidden-input');
                    const selectedText = multiselect.querySelector('.selected-text');
                    const checkboxes = multiselect.querySelectorAll('.sn-checkbox');

                    const updateSelection = function() {
                        const checked = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
                        hiddenInput.value = checked.join(', ');
                        if (checked.length > 2) {
                            selectedText.innerText = `${checked.length} Serials Selected (${checked.slice(0, 2).join(', ')}...)`;
                            selectedText.title = checked.join(', ');
                            selectedText.style.color = 'var(--text-primary)';
                            qtyInput.value = checked.length;
                        } else if (checked.length > 0) {
                            selectedText.innerText = checked.join(', ');
                            selectedText.title = checked.join(', ');
                            selectedText.style.color = 'var(--text-primary)';
                            qtyInput.value = checked.length;
                        } else {
                            selectedText.innerText = '-- Select Serial Numbers --';
                            selectedText.title = '';
                            selectedText.style.color = 'var(--text-secondary)';
                            if (!defaultQty) qtyInput.value = '';
                        }
                    };

                    trigger.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.querySelectorAll('.multiselect-options').forEach(panel => {
                            if (panel !== optionsPanel) panel.style.display = 'none';
                        });
                        optionsPanel.style.display = optionsPanel.style.display === 'none' ? 'block' : 'none';
                    });

                    optionsPanel.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });

                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', updateSelection);
                    });

                    updateSelection();
                    if (defaultQty && !preSelectedSerials.length) {
                        qtyInput.value = defaultQty;
                    }
                } else {
                    serialCell.innerHTML = `<input type="text" name="items[${rowIdx}][serial_numbers]" class="form-input" placeholder="No available serials" readonly style="opacity:0.7;" value="${currentSerialsStr}">`;
                    if (!qtyInput.value || qtyInput.value === '') {
                        qtyInput.value = defaultQty || availQty;
                    }
                }
            }
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.multiselect-options').forEach(panel => {
                panel.style.display = 'none';
            });
        });

        function addRow(sku = '', qty = '', serials = '') {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${rowCount}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU" value="${sku}">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][serial_numbers]" class="form-input" placeholder="Comma-separated serials (e.g. SN-1, SN-2)" value="${serials}">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-input" required min="1" placeholder="Qty" value="${qty}">
                </td>
                <td>
                    <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                        <i class="ph ph-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            if (sku) {
                handleSkuSelection(tr, qty, serials);
            }
            rowCount++;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#itemsBody tr').forEach(tr => {
                handleSkuSelection(tr);
            });
        });

        document.getElementById('itemsBody').addEventListener('change', function(e) {
            if (e.target.name && e.target.name.includes('[sku_code]')) {
                const tr = e.target.closest('tr');
                handleSkuSelection(tr);
            }
        });

        document.getElementById('itemsBody').addEventListener('input', function(e) {
            if (e.target.name && e.target.name.includes('[sku_code]')) {
                const tr = e.target.closest('tr');
                handleSkuSelection(tr);
            }
        });

        document.getElementById('csvUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const text = event.target.result;
                const lines = text.split('\n');
                if (lines.length === 0) return;
                
                // If it's a valid CSV with headers, we skip the first row
                const headerLine = lines[0].toLowerCase();
                const isNewFormat = headerLine.includes('asn_reference') || headerLine.includes('airway_bill') || headerLine.includes('vendor');
                const startIndex = (headerLine.includes('sku') || isNewFormat) ? 1 : 0;
                
                const tbody = document.getElementById('itemsBody');
                if (isNewFormat) {
                    tbody.innerHTML = '';
                }
                
                let addedCount = 0;
                let firstRowParsed = false;
                
                for (let i = startIndex; i < lines.length; i++) {
                    if (!lines[i].trim()) continue;
                    
                    const cols = lines[i].split(',');
                    if (isNewFormat) {
                        if (cols.length >= 5) {
                            const asnRef = cols[0].trim();
                            const awb = cols[1].trim();
                            const vendor = cols[2].trim();
                            const sku = cols[3].trim();
                            const qty = parseInt(cols[4].trim());
                            
                            if (!firstRowParsed) {
                                if (asnRef) {
                                    const refInput = document.querySelector('input[name="asn_reference"]');
                                    if (refInput) refInput.value = asnRef;
                                }
                                if (awb) {
                                    const awbInput = document.querySelector('input[name="airway_bill"]');
                                    if (awbInput) awbInput.value = awb;
                                }
                                if (vendor) {
                                    const vendorSelect = document.querySelector('select[name="vendor_id"]');
                                    if (vendorSelect) {
                                        let exists = false;
                                        for (let o = 0; o < vendorSelect.options.length; o++) {
                                            if (vendorSelect.options[o].value.toLowerCase() === vendor.toLowerCase()) {
                                                vendorSelect.selectedIndex = o;
                                                exists = true;
                                                break;
                                            }
                                        }
                                        if (!exists && vendor) {
                                            const opt = document.createElement('option');
                                            opt.value = vendor;
                                            opt.text = vendor;
                                            vendorSelect.add(opt);
                                            vendorSelect.value = vendor;
                                        }
                                    }
                                }
                                firstRowParsed = true;
                            }
                            
                            if (sku && !isNaN(qty)) {
                                const serials = cols[5] ? cols[5].trim() : '';
                                addRow(sku, qty, serials);
                                addedCount++;
                            }
                        }
                    } else {
                        if (cols.length >= 2) {
                            const sku = cols[0].trim();
                            const qty = parseInt(cols[1].trim());
                            const serials = cols[2] ? cols[2].trim() : '';
                            if (sku && !isNaN(qty)) {
                                addRow(sku, qty, serials);
                                addedCount++;
                            }
                        }
                    }
                }
                
                alert(`Successfully added ${addedCount} items from CSV.`);
                e.target.value = ''; // Reset input
            };
            reader.readAsText(file);
        });
    </script>
@endsection
