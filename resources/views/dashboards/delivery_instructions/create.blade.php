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
            </div>
            
            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product / SKU *</th>
                        <th>Quantity *</th>
                        <th>Serial Numbers (if required)</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @if(count($remainingItems) > 0)
                        @foreach($remainingItems as $index => $item)
                            <tr>
                                <td>
                                    <input type="text" name="items[{{ $index }}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU" value="{{ $item['sku_code'] }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-input" required min="1" placeholder="Qty" value="{{ $item['quantity'] }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][serial_numbers]" class="form-input" placeholder="e.g. SN1, SN2" value="">
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
                                <input type="text" name="items[0][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU">
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-input" required min="1" placeholder="Qty">
                            </td>
                            <td>
                                <input type="text" name="items[0][serial_numbers]" class="form-input" placeholder="e.g. SN1, SN2">
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

        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${rowCount}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-input" required min="1" placeholder="Qty">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][serial_numbers]" class="form-input" placeholder="e.g. SN1, SN2">
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
    </script>
@endsection
