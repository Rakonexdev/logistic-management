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
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-plus-circle"></i> Create Return Instruction
        </h1>
        <a href="{{ route('return-instructions.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Return Instructions
        </a>
    </div>

    @if($errors->any())
        <div class="glass" style="padding: 1rem; margin-bottom: 1rem; border-left: 4px solid var(--danger); background: rgba(239, 68, 68, 0.1); color: var(--danger);">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass form-panel">
        <form action="{{ route('return-instructions.store') }}" method="POST">
            @csrf

            <div class="section-title">Return Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Return Reference *</label>
                    <input type="text" name="return_ref" class="form-input" required value="{{ old('return_ref', $defaultRef) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Customer / Client Name *</label>
                    <input type="text" name="customer_name" class="form-input" required placeholder="e.g. Acme Corp" value="{{ old('customer_name') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Pickup Address *</label>
                    <input type="text" name="pickup_address" class="form-input" required placeholder="Full address for pickup" value="{{ old('pickup_address') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-input" placeholder="Contact person at client site" value="{{ old('contact_person') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-input" placeholder="Phone number" value="{{ old('contact_phone') }}">
                </div>
            </div>

            <div class="section-title">Items to Pick Up</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Product / SKU *</th>
                        <th style="width: 35%;">Description</th>
                        <th style="width: 15%;">Quantity *</th>
                        <th style="width: 25%;">Serial Numbers (if any)</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td>
                            <input type="text" name="items[0][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU">
                        </td>
                        <td>
                            <input type="text" name="items[0][description]" class="form-input" placeholder="Item details/condition">
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-input" required min="1" value="1">
                        </td>
                        <td>
                            <input type="text" name="items[0][serial_numbers]" class="form-input" placeholder="Comma separated serials">
                        </td>
                        <td>
                            <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-outline" onclick="addRow()">
                <i class="ph ph-plus"></i> Add Item SKU
            </button>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Special Instructions / Remarks</label>
                <textarea name="remarks" class="form-input" rows="3" placeholder="Additional details for driver or warehouse team..."></textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('return-instructions.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk"></i> Save & Issue Return Instruction
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
        let rowCount = 1;
        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${rowCount}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][description]" class="form-input" placeholder="Item details/condition">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-input" required min="1" value="1">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][serial_numbers]" class="form-input" placeholder="Comma separated serials">
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
