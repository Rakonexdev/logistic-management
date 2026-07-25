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
                    <label class="form-label">Customer / Client Name *</label>
                    <input type="text" name="customer_name" id="customerNameInput" class="form-input" required placeholder="e.g. Acme Corporation" value="{{ old('customer_name') }}">
                </div>
            </div>

            <div class="section-title">Select Collected Cheques to Invoice (Collection Charge: QAR 35.00 / Cheque)</div>

            @if($cheques->isEmpty())
                <div style="padding: 2rem; text-align: center; color: var(--text-secondary); background: rgba(0,0,0,0.1); border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="ph ph-info" style="font-size: 1.75rem; display: block; margin-bottom: 0.5rem; color: var(--accent-primary, #6366f1);"></i>
                    No un-invoiced collected cheques found. All collected cheques have already been invoiced.
                </div>
            @else
                <table class="cheques-table">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">Select</th>
                            <th style="width: 15%;">Ref #</th>
                            <th style="width: 25%;">Customer</th>
                            <th style="width: 25%;">Cheque & Reference Info</th>
                            <th style="width: 15%;">Cheque Amount</th>
                            <th style="width: 15%;">Collection Fee (QAR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cheques as $index => $chq)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" class="cheque-checkbox" value="{{ $chq->id }}" onchange="toggleChequeRow(this, {{ $chq->id }}, '{{ addslashes($chq->customer_name) }}')" style="width: 1.2rem; height: 1.2rem; cursor: pointer; accent-color: var(--accent-primary, #6366f1);">
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
                                <td>
                                    <div id="feeBox_{{ $chq->id }}" style="display: none;">
                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][collection_fee]" value="35.00" class="form-input fee-input" required oninput="calculateTotals()" style="padding: 0.4rem 0.75rem;">
                                        <input type="hidden" name="items[{{ $index }}][cheque_collection_id]" value="{{ $chq->id }}">
                                    </div>
                                    <span id="feePlaceholder_{{ $chq->id }}" style="color: var(--text-secondary); font-style: italic;">QAR 35.00</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="total-summary-card">
                <div class="total-summary-label">
                    <i class="ph ph-calculator" style="margin-right: 0.5rem;"></i> Total Invoice Collection Fee:
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

    @push('scripts')
        <script>
            function toggleChequeRow(checkbox, id, customerName) {
                const feeBox = document.getElementById(`feeBox_${id}`);
                const feePlaceholder = document.getElementById(`feePlaceholder_${id}`);

                if (checkbox.checked) {
                    feeBox.style.display = 'block';
                    feePlaceholder.style.display = 'none';

                    const customerInput = document.getElementById('customerNameInput');
                    if (!customerInput.value) {
                        customerInput.value = customerName;
                    }
                } else {
                    feeBox.style.display = 'none';
                    feePlaceholder.style.display = 'inline';
                }

                calculateTotals();
            }

            function calculateTotals() {
                let grandTotal = 0;
                let selectedCount = 0;

                const checkboxes = document.querySelectorAll('.cheque-checkbox:checked');
                checkboxes.forEach(cb => {
                    const id = cb.value;
                    const feeInput = document.querySelector(`#feeBox_${id} .fee-input`);
                    const fee = parseFloat(feeInput ? feeInput.value : 35) || 35;
                    grandTotal += fee;
                    selectedCount++;
                });

                document.getElementById('totalInvoiceAmountText').textContent = 'QAR ' + grandTotal.toFixed(2);
                document.getElementById('submitBtn').disabled = (selectedCount === 0);
            }
        </script>
    @endpush
@endsection
