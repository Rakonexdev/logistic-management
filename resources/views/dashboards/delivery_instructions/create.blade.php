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
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
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
        <form action="{{ route('delivery-instructions.store') }}" method="POST">
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
                    <label class="form-label">Delivery Address *</label>
                    <input type="text" name="delivery_address" class="form-input" required 
                           value="{{ old('delivery_address', isset($parentDi) ? $parentDi->delivery_address : '') }}">
                </div>
            </div>

            <div class="section-title">
                Requested Items List
                <div>
                    <input type="file" id="csvUpload" accept=".csv" style="display: none;">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('csvUpload').click()" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                        <i class="ph ph-upload-simple"></i> Bulk Upload CSV
                    </button>
                    <a href="{{ route('delivery-instructions.template') }}" class="btn btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                        <i class="ph ph-download-simple"></i> Download Template
                    </a>
                </div>
            </div>
            
            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width: 20%;">Product / SKU *</th>
                        <th style="width: 42%;">Description</th>
                        <th style="width: 8%;">Quantity *</th>
                        <th style="width: 25%;">Serial Numbers (if required)</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @if(count($remainingItems) > 0)
                        @foreach($remainingItems as $index => $item)
                            <tr>
                                <td>
                                    <input type="text" name="items[{{ $index }}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU" value="{{ $item['sku_code'] }}" oninput="this.title = this.value" title="{{ $item['sku_code'] }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][description]" class="form-input" placeholder="Description" value="{{ $item['description'] ?? '' }}" oninput="this.title = this.value" title="{{ $item['description'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-input" required min="1" placeholder="Qty" value="{{ $item['quantity'] }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][serial_numbers]" class="form-input" placeholder="e.g. SN1, SN2" value="" oninput="this.title = this.value">
                                    <span class="hint-text">Enter serial numbers separated by commas</span>
                                </td>
                                <td>
                                    <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                <input type="text" name="items[0][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU" oninput="this.title = this.value">
                            </td>
                            <td>
                                <input type="text" name="items[0][description]" class="form-input" placeholder="Description" oninput="this.title = this.value">
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-input" required min="1" placeholder="Qty">
                            </td>
                            <td>
                                <input type="text" name="items[0][serial_numbers]" class="form-input" placeholder="e.g. SN1, SN2" oninput="this.title = this.value">
                                <span class="hint-text">Enter serial numbers separated by commas</span>
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
        
        <datalist id="products-list">
            @foreach($products as $prod)
                <option value="{{ $prod->sku_code }}">{{ $prod->sku_code }} - {{ $prod->name }} @if($prod->serial_number) (Requires SN) @endif</option>
            @endforeach
        </datalist>
    </div>

    <script>
        let rowCount = {{ count($remainingItems) > 0 ? count($remainingItems) : 1 }};

        function addRow(sku = '', description = '', qty = '', serials = '') {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${rowCount}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU" value="${sku}" oninput="this.title = this.value" title="${sku}">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][description]" class="form-input" placeholder="Description" value="${description}" oninput="this.title = this.value" title="${description}">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-input" required min="1" placeholder="Qty" value="${qty}">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][serial_numbers]" class="form-input" placeholder="e.g. SN1, SN2" value="${serials}" oninput="this.title = this.value" title="${serials}">
                    <span class="hint-text">Enter serial numbers separated by commas</span>
                </td>
                <td>
                    <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                        <i class="ph ph-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
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
                    const skuInput = rows[0].querySelector('[name$="[sku_code]"]');
                    if (skuInput && !skuInput.value) {
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
    </script>
@endsection
