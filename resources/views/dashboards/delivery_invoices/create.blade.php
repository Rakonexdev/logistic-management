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
            margin-bottom: 1.5rem;
        }

        .items-table th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            background: rgba(0,0,0,0.15);
        }

        .items-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .total-summary-card {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 12px;
            padding: 1.25rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .total-summary-label {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .total-summary-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--accent-primary, #6366f1);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-receipt"></i> Create Delivery Invoice
        </h1>
        <a href="{{ route('delivery-invoices.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Invoices
        </a>
    </div>

    @if($errors->any())
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--danger); background: rgba(239, 68, 68, 0.1); color: var(--danger);">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass form-panel">
        <form action="{{ route('delivery-invoices.store') }}" method="POST" id="invoiceForm">
            @csrf

            <div class="section-title">General Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Invoice Number *</label>
                    <input type="text" name="invoice_number" class="form-input" required value="{{ old('invoice_number', $defaultInvoiceNum) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Select Delivery Instruction *</label>
                    <select name="delivery_instruction_id" id="diSelect" class="form-input" required onchange="onDiSelected(this.value)" style="appearance: auto;">
                        <option value="">-- Select Delivery Instruction --</option>
                        @foreach($instructions as $di)
                            <option value="{{ $di->id }}" {{ old('delivery_instruction_id') == $di->id ? 'selected' : '' }}>
                                {{ $di->di_number }} ({{ $di->customer_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer / Destination</label>
                    <input type="text" id="customerNameInput" class="form-input" readonly placeholder="Auto-populated upon DI selection">
                </div>

                <div class="form-group">
                    <label class="form-label">SO Reference Number</label>
                    <input type="text" id="soRefInput" class="form-input" readonly placeholder="Auto-populated upon DI selection">
                </div>

                <div class="form-group">
                    <label class="form-label">End User Name</label>
                    <input type="text" id="endUserInput" class="form-input" readonly placeholder="Auto-populated upon DI selection">
                </div>
            </div>

            <div class="section-title">SFQ Handling Charges Sheet</div>
            
            <div id="noDiNotice" style="padding: 2rem; text-align: center; color: var(--text-secondary); background: rgba(0,0,0,0.1); border-radius: 8px; margin-bottom: 1.5rem;">
                <i class="ph ph-info" style="font-size: 1.75rem; display: block; margin-bottom: 0.5rem; color: var(--accent-primary, #6366f1);"></i>
                Please select a <strong>Delivery Instruction</strong> above to populate SKUs, Serial Numbers, and Quantities.
            </div>

            <div id="itemsContainer" style="display: none;">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">SKU Code</th>
                            <th style="width: 35%;">Serial Number</th>
                            <th style="width: 15%; text-align: center;">Quantity</th>
                            <th style="width: 25%;">Charge Amount (QAR)</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>

                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem 1.75rem; margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph ph-coins" style="font-size: 1.4rem; color: var(--accent-primary, #6366f1);"></i>
                        <span style="font-weight: 600; font-size: 1rem; color: var(--text-primary);">Lump Sum Amount (QAR):</span>
                    </div>
                    <div style="width: 240px;">
                        <input type="number" step="0.01" min="0" id="lumpSumInput" name="lump_sum_amount" class="form-input" placeholder="0.00" oninput="calculateTotals()" style="text-align: right; font-weight: 700; font-size: 1.1rem; padding: 0.5rem 0.75rem;">
                    </div>
                </div>

                <div class="total-summary-card">
                    <div class="total-summary-label">
                        <i class="ph ph-calculator" style="margin-right: 0.5rem;"></i> Total Invoice Amount:
                    </div>
                    <div class="total-summary-value" id="totalInvoiceAmountText">
                        QAR 0.00
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('delivery-invoices.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="ph ph-check-circle"></i> Generate Delivery Invoice
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const instructionsMap = @json($instructions->keyBy('id'));

            function onDiSelected(diId) {
                const notice = document.getElementById('noDiNotice');
                const container = document.getElementById('itemsContainer');
                const submitBtn = document.getElementById('submitBtn');
                const body = document.getElementById('itemsBody');

                const customerInput = document.getElementById('customerNameInput');
                const soRefInput = document.getElementById('soRefInput');
                const endUserInput = document.getElementById('endUserInput');
                const lumpSumInput = document.getElementById('lumpSumInput');

                if (!diId || !instructionsMap[diId]) {
                    notice.style.display = 'block';
                    container.style.display = 'none';
                    submitBtn.disabled = true;
                    customerInput.value = '';
                    soRefInput.value = '';
                    endUserInput.value = '';
                    if (lumpSumInput) lumpSumInput.value = '';
                    body.innerHTML = '';
                    return;
                }

                const di = instructionsMap[diId];
                customerInput.value = di.customer_name || '';
                soRefInput.value = di.so_reference || '-';
                endUserInput.value = di.end_user_name || '-';

                body.innerHTML = '';
                let rowIndex = 0;

                di.items.forEach(item => {
                    let serials = [];
                    if (item.serial_numbers) {
                        serials = item.serial_numbers.split(',').map(s => s.trim()).filter(s => s.length > 0);
                    }

                    if (serials.length > 0) {
                        // Expand each serial number to its own individual row with charge amount field
                        serials.forEach(sn => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><strong style="color: var(--text-primary);">${item.sku_code}</strong></td>
                                <td><span style="font-family: monospace; background: rgba(99, 102, 241, 0.1); color: var(--accent-primary, #6366f1); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">${sn}</span></td>
                                <td style="text-align: center;">1</td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][charge_amount]" class="form-input charge-input" placeholder="0.00" oninput="calculateTotals()" style="padding: 0.4rem 0.75rem;">
                                    <input type="hidden" name="items[${rowIndex}][sku_code]" value="${item.sku_code}">
                                    <input type="hidden" name="items[${rowIndex}][serial_number]" value="${sn}">
                                    <input type="hidden" name="items[${rowIndex}][quantity]" value="1">
                                </td>
                            `;
                            body.appendChild(tr);
                            rowIndex++;
                        });
                    } else {
                        // Non-serialized item row
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong style="color: var(--text-primary);">${item.sku_code}</strong></td>
                            <td><span style="color: var(--text-secondary); font-style: italic;">No Serial Number</span></td>
                            <td style="text-align: center;">${item.quantity}</td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[${rowIndex}][charge_amount]" class="form-input charge-input" placeholder="0.00" oninput="calculateTotals()" style="padding: 0.4rem 0.75rem;">
                                <input type="hidden" name="items[${rowIndex}][sku_code]" value="${item.sku_code}">
                                <input type="hidden" name="items[${rowIndex}][serial_number]" value="">
                                <input type="hidden" name="items[${rowIndex}][quantity]" value="${item.quantity}">
                            </td>
                        `;
                        body.appendChild(tr);
                        rowIndex++;
                    }
                });

                notice.style.display = 'none';
                container.style.display = 'block';
                submitBtn.disabled = false;

                if (lumpSumInput) lumpSumInput.value = '';
                calculateTotals();
            }

            function calculateTotals() {
                let itemsTotal = 0;
                const rows = document.querySelectorAll('#itemsBody tr');

                rows.forEach(tr => {
                    const chargeInput = tr.querySelector('.charge-input');
                    const qtyHidden = tr.querySelector('input[name*="[quantity]"]');

                    const charge = parseFloat(chargeInput ? chargeInput.value : 0) || 0;
                    const qty = parseInt(qtyHidden ? qtyHidden.value : 1) || 1;

                    itemsTotal += charge * qty;
                });

                const lumpSumInput = document.getElementById('lumpSumInput');
                const lumpSum = parseFloat(lumpSumInput ? lumpSumInput.value : 0) || 0;

                const grandTotal = itemsTotal + lumpSum;

                const totalSummaryText = document.getElementById('totalInvoiceAmountText');
                if (totalSummaryText) {
                    totalSummaryText.textContent = 'QAR ' + grandTotal.toFixed(2);
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const initialDiId = document.getElementById('diSelect').value;
                if (initialDiId) {
                    onDiSelected(initialDiId);
                }
            });
        </script>
    @endpush
@endsection
