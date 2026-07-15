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

        function addRow(sku = '', qty = '') {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${rowCount}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU" value="${sku}">
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
            rowCount++;
        }

        const productsData = @json($products);
        const skuQtyMap = {};
        productsData.forEach(p => {
            skuQtyMap[p.sku_code] = p.qty;
        });

        function validateRowQty(input) {
            const tr = input.closest('tr');
            const skuInput = tr.querySelector('[name$="[sku_code]"]');
            const qtyInput = tr.querySelector('[name$="[quantity]"]');
            const sku = skuInput.value;
            const enteredQty = parseInt(qtyInput.value) || 0;
            const maxQty = skuQtyMap[sku];
            
            let errorSpan = qtyInput.parentNode.querySelector('.qty-error');
            if (errorSpan) {
                errorSpan.remove();
            }

            if (maxQty !== undefined && enteredQty > maxQty) {
                errorSpan = document.createElement('span');
                errorSpan.className = 'qty-error';
                errorSpan.style.color = 'var(--danger)';
                errorSpan.style.fontSize = '0.75rem';
                errorSpan.style.display = 'block';
                errorSpan.style.marginTop = '0.25rem';
                errorSpan.innerText = `Max allowed: ${maxQty}`;
                qtyInput.parentNode.appendChild(errorSpan);
            }
        }

        document.getElementById('itemsBody').addEventListener('input', function(e) {
            if (e.target.name && (e.target.name.includes('[quantity]') || e.target.name.includes('[sku_code]'))) {
                validateRowQty(e.target);
            }
        });

        document.getElementById('csvUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const text = event.target.result;
                const lines = text.split('\n');
                
                // If it's a valid CSV with headers, we skip the first row
                const startIndex = lines[0].toLowerCase().includes('sku') ? 1 : 0;
                
                let addedCount = 0;
                for (let i = startIndex; i < lines.length; i++) {
                    if (!lines[i].trim()) continue;
                    
                    const cols = lines[i].split(',');
                    if (cols.length >= 2) {
                        const sku = cols[0].trim();
                        const qty = parseInt(cols[1].trim());
                        if (sku && !isNaN(qty)) {
                            addRow(sku, qty);
                            addedCount++;
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
