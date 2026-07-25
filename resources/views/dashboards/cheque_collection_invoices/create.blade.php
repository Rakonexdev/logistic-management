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
            color: var(--text-primary, #1e293b);
        }

        .form-select option {
            background: var(--bg-color, #ffffff);
            color: var(--text-primary, #1e293b);
        }

        .form-input:focus, .form-select:focus {
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

        .cheques-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .cheques-table th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            background: rgba(0,0,0,0.15);
        }

        .cheques-table td {
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
            <i class="ph ph-bank"></i> Create Cheque Collection Invoice
        </h1>
        <a href="{{ route('cheque-collection-invoices.index') }}" class="btn btn-outline">
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
        <form action="{{ route('cheque-collection-invoices.store') }}" method="POST" id="chequeInvoiceForm">
            @csrf

            <div class="section-title">Invoice General Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Invoice Number *</label>
                    <input type="text" name="invoice_number" class="form-input" required value="{{ old('invoice_number', $defaultInvoiceNum) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Select Delivery Invoice (Auto-fill Customer & Ref)</label>
                    <select id="deliveryInvoiceSelect" class="form-select" onchange="onSelectDeliveryInvoice(this)">
                        <option value="">Select Delivery Invoice</option>
                        @foreach($deliveryInvoices as $delInv)
                            <option value="{{ $delInv->id }}"
                                    data-customer="{{ $delInv->customer_name }}"
                                    data-invoice-ref="{{ $delInv->invoice_number }}"
                                    data-so-ref="{{ $delInv->deliveryInstruction->di_number ?? '' }}"
                                    data-amount="{{ number_format($delInv->total_amount, 2) }}">
                                {{ $delInv->invoice_number }} - {{ $delInv->customer_name }} (QAR {{ number_format($delInv->total_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer / Client Name *</label>
                    <input type="text" name="customer_name" id="customerNameInput" class="form-input" required placeholder="e.g. Acme Corporation" value="{{ old('customer_name') }}">
                </div>
            </div>

            <div class="section-title">Select Collected Cheques / Delivery Invoices to Invoice</div>

            @if($cheques->isEmpty())
                <div style="padding: 2rem; text-align: center; color: var(--text-secondary); background: rgba(0,0,0,0.1); border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="ph ph-info" style="font-size: 1.75rem; display: block; margin-bottom: 0.5rem; color: var(--accent-primary, #6366f1);"></i>
                    No un-invoiced collected cheques found. All collected cheques have already been invoiced.
                </div>
            @else
                <table class="cheques-table">
                    <thead>
                        <tr>
                            <th style="width: 8%; text-align: center;">Select</th>
                            <th style="width: 20%;">Ref #</th>
                            <th style="width: 27%;">Customer</th>
                            <th style="width: 27%;">Cheque & Reference Info</th>
                            <th style="width: 18%;">Cheque Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cheques as $index => $chq)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" class="cheque-checkbox" value="{{ $chq->id }}" data-amount="{{ $chq->amount }}" onchange="toggleChequeRow(this, {{ $chq->id }}, '{{ addslashes($chq->customer_name) }}')" style="width: 1.2rem; height: 1.2rem; cursor: pointer; accent-color: var(--accent-primary, #6366f1);">
                                    <input type="hidden" name="items[{{ $index }}][cheque_collection_id]" value="{{ $chq->id }}" id="item_input_{{ $chq->id }}" disabled>
                                </td>
                                <td><strong>{{ $chq->collection_ref }}</strong></td>
                                <td>{{ $chq->customer_name }}</td>
                                <td>
                                    <div><strong>Cheque #:</strong> {{ $chq->cheque_number ?: $chq->collection_ref }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        Date: {{ $chq->cheque_date ? $chq->cheque_date->format('Y-m-d') : '-' }} | 
                                        PO: {{ $chq->po_reference ?: '-' }} | SO: {{ $chq->so_reference ?: '-' }} | Inv: {{ $chq->invoice_reference ?: '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div><strong>QAR {{ number_format($chq->amount, 2) }}</strong></div>
                                    @if($chq->amount_usd)
                                        <div style="font-size: 0.75rem; color: var(--text-secondary);">(USD ${{ number_format($chq->amount_usd, 2) }})</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="total-summary-card">
                <div class="total-summary-label">
                    <i class="ph ph-calculator" style="margin-right: 0.5rem;"></i> Total Invoice Amount:
                </div>
                <div class="total-summary-value" id="totalInvoiceAmountText">
                    QAR 0.00
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('cheque-collection-invoices.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="ph ph-check-circle"></i> Generate Cheque Invoice
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function onSelectDeliveryInvoice(select) {
            const selectedOpt = select.options[select.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) return;

            const customer = selectedOpt.getAttribute('data-customer');
            const invoiceRef = selectedOpt.getAttribute('data-invoice-ref');

            if (customer) {
                document.getElementById('customerNameInput').value = customer;
            }

            const rows = document.querySelectorAll('.cheques-table tbody tr');
            let foundMatch = false;

            rows.forEach(tr => {
                const rowText = tr.textContent || '';
                if (invoiceRef && rowText.includes(invoiceRef)) {
                    const cb = tr.querySelector('.cheque-checkbox');
                    if (cb && !cb.checked) {
                        cb.checked = true;
                        cb.dispatchEvent(new Event('change'));
                        foundMatch = true;
                    }
                }
            });

            if (!foundMatch && customer) {
                rows.forEach(tr => {
                    const rowText = tr.textContent || '';
                    if (rowText.includes(customer)) {
                        const cb = tr.querySelector('.cheque-checkbox');
                        if (cb && !cb.checked) {
                            cb.checked = true;
                            cb.dispatchEvent(new Event('change'));
                        }
                    }
                });
            }
        }

        function toggleChequeRow(checkbox, id, customerName) {
            const itemInput = document.getElementById(`item_input_${id}`);
            if (itemInput) {
                itemInput.disabled = !checkbox.checked;
            }

            if (checkbox.checked) {
                const customerInput = document.getElementById('customerNameInput');
                if (!customerInput.value) {
                    customerInput.value = customerName;
                }
            }

            calculateTotals();
        }

        function calculateTotals() {
            let grandTotal = 0;
            let selectedCount = 0;

            const checkboxes = document.querySelectorAll('.cheque-checkbox:checked');
            checkboxes.forEach(cb => {
                const amount = parseFloat(cb.getAttribute('data-amount')) || 0;
                grandTotal += amount;
                selectedCount++;
            });

            document.getElementById('totalInvoiceAmountText').textContent = 'QAR ' + grandTotal.toFixed(2);
            document.getElementById('submitBtn').disabled = (selectedCount === 0);
        }
    </script>
@endpush
