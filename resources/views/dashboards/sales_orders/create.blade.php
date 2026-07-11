@extends('layouts.dashboard')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .actions-group {
            display: flex;
            gap: 0.5rem;
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
            vertical-align: middle;
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

        .stock-indicator {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-active { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .badge-low { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .badge-inactive { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        .badge-out { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

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
            <i class="ph ph-plus-circle"></i>
            Create Sales Order
        </h1>
        <div class="actions-group">
            <input type="file" id="excelUpload" accept=".csv" style="display: none;">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('excelUpload').click()">
                <i class="ph ph-upload-simple"></i> Upload SO by Excel
            </button>
            <a href="{{ route('sales-orders.template') }}" class="btn btn-outline">
                <i class="ph ph-download-simple"></i> Download Template
            </a>
            <button type="button" class="btn btn-outline" onclick="runStockCheck()" style="background: rgba(99, 102, 241, 0.1); border-color: rgba(99, 102, 241, 0.3);">
                <i class="ph ph-activity"></i> Run Stock Check
            </button>
            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline">
                <i class="ph ph-arrow-left"></i> Back to Sales Orders
            </a>
        </div>
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
        <form action="{{ route('sales-orders.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="section-title">General Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Sales Order Number *</label>
                    <input type="text" name="so_number" class="form-input" required value="{{ old('so_number', 'SO-'.date('Ymd').'-'.rand(100,999)) }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-input" required value="{{ old('customer_name') }}" placeholder="Enter Customer Name">
                </div>

                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-input" value="{{ old('designation') }}" placeholder="Enter Designation (e.g. Procurement Manager)">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Order Date *</label>
                    <input type="date" name="order_date" class="form-input" required value="{{ old('order_date', date('Y-m-d')) }}">
                </div>
            </div>

            <div class="section-title">
                Line Items
            </div>
            
            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product / SKU *</th>
                        <th>Ordered Quantity *</th>
                        <th>Stock Availability Indicator</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td>
                            <input type="text" name="items[0][sku_code]" class="form-input" required placeholder="Enter SKU" onchange="resetStockIndicator(this)">
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-input" required min="1" placeholder="Qty" onchange="resetStockIndicator(this)">
                        </td>
                        <td class="stock-cell" style="min-width: 150px;">
                            <span class="stock-indicator">-</span>
                        </td>
                        <td>
                            <button type="button" class="remove-row" onclick="this.closest('tr').remove(); runStockCheck();">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <button type="button" class="btn btn-outline" onclick="addRow()">
                <i class="ph ph-plus"></i> Add Line Item
            </button>

            <div class="section-title" style="margin-top: 2rem;">Attachments & Remarks</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Excel Upload File (Reference)</label>
                    <input type="file" name="excel_file" class="form-input" accept=".csv,.xlsx,.xls">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Attached PDF Document</label>
                    <input type="file" name="pdf_file" class="form-input" accept=".pdf">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Remarks / Notes</label>
                <textarea name="remarks" class="form-input" rows="4">{{ old('remarks') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" name="status" value="draft" class="btn btn-outline">
                    <i class="ph ph-floppy-disk"></i> Save Draft
                </button>
                <button type="submit" name="status" value="submitted" class="btn btn-primary">
                    <i class="ph ph-paper-plane-right"></i> Submit Order
                </button>
            </div>
        </form>
    </div>

    <script>
        let rowCount = 1;

        function addRow(sku = '', qty = '') {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${rowCount}][sku_code]" class="form-input" required placeholder="Enter SKU" value="${sku}" onchange="resetStockIndicator(this)">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-input" required min="1" placeholder="Qty" value="${qty}" onchange="resetStockIndicator(this)">
                </td>
                <td class="stock-cell">
                    <span class="stock-indicator">-</span>
                </td>
                <td>
                    <button type="button" class="remove-row" onclick="this.closest('tr').remove(); runStockCheck();">
                        <i class="ph ph-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            rowCount++;
        }

        function resetStockIndicator(element) {
            const row = element.closest('tr');
            const indicator = row.querySelector('.stock-indicator');
            if (indicator) {
                indicator.className = 'stock-indicator';
                indicator.innerHTML = '-';
            }
        }

        async function runStockCheck() {
            const rows = document.querySelectorAll('#itemsBody tr');
            const items = [];
            rows.forEach(row => {
                const skuInput = row.querySelector('input[name$="[sku_code]"]');
                if (skuInput && skuInput.value.trim()) {
                    items.push({ sku_code: skuInput.value.trim() });
                }
            });

            if (items.length === 0) {
                return;
            }

            try {
                const response = await fetch('{{ route("sales-orders.stock-check") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ items: items })
                });
                const data = await response.json();
                
                rows.forEach(row => {
                    const skuInput = row.querySelector('input[name$="[sku_code]"]');
                    const qtyInput = row.querySelector('input[name$="[quantity]"]');
                    const indicator = row.querySelector('.stock-indicator');
                    
                    if (skuInput && indicator) {
                        const sku = skuInput.value.trim();
                        const qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
                        
                        if (data.stocks && data.stocks[sku]) {
                            const info = data.stocks[sku];
                            if (info.status === 'not_found') {
                                indicator.className = 'stock-indicator badge-inactive';
                                indicator.innerHTML = '<i class="ph ph-warning-circle"></i> Not Found';
                            } else if (info.available >= qty) {
                                indicator.className = 'stock-indicator badge-active';
                                indicator.innerHTML = `<i class="ph ph-check-circle"></i> Available (${info.available})`;
                            } else if (info.available > 0) {
                                indicator.className = 'stock-indicator badge-low';
                                indicator.innerHTML = `<i class="ph ph-warning"></i> Low Stock (${info.available})`;
                            } else {
                                indicator.className = 'stock-indicator badge-out';
                                indicator.innerHTML = '<i class="ph ph-x-circle"></i> Out of Stock';
                            }
                        } else {
                            indicator.className = 'stock-indicator';
                            indicator.innerHTML = '-';
                        }
                    }
                });
            } catch (e) {
                console.error(e);
            }
        }

        // CSV parsing logic for Excel Template upload
        document.getElementById('excelUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const text = event.target.result;
                const lines = text.split('\n');
                
                const startIndex = lines[0].toLowerCase().includes('sku') ? 1 : 0;
                
                let addedCount = 0;
                const tbody = document.getElementById('itemsBody');
                
                // Clear initial empty row if first row has empty input
                const firstRowSku = tbody.querySelector('tr input[name="items[0][sku_code]"]');
                const firstRowQty = tbody.querySelector('tr input[name="items[0][quantity]"]');
                let clearFirst = firstRowSku && !firstRowSku.value.trim() && firstRowQty && !firstRowQty.value.trim();
                if (clearFirst) {
                    tbody.innerHTML = '';
                }

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
                
                runStockCheck();
                alert(`Successfully imported ${addedCount} items from Excel/CSV file.`);
                e.target.value = ''; // Reset file input
            };
            reader.readAsText(file);
        });
    </script>
@endsection
