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
            <i class="ph ph-currency-circle-dollar"></i> Create Rent Invoice
        </h1>
        <a href="{{ route('rent-invoices.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Rent Invoices
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
        <form action="{{ route('rent-invoices.store') }}" method="POST">
            @csrf

            <div class="section-title">Warehouse Rent Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Invoice Number *</label>
                    <input type="text" name="invoice_number" class="form-input" required value="{{ old('invoice_number', $defaultInvoiceNum) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Warehouse / Facility Name *</label>
                    <input type="text" name="warehouse_name" class="form-input" required placeholder="e.g. Birkat Al Awamer Main Warehouse" value="{{ old('warehouse_name', 'Birkat Al Awamer Main Warehouse') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Rent Month / Period *</label>
                    <input type="text" name="rent_month" class="form-input" required placeholder="e.g. July 2026" value="{{ old('rent_month', $currentMonth) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-input" value="{{ old('due_date', date('Y-m-t')) }}">
                </div>
            </div>

            <div class="section-title">Rent & Charges Breakdown</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Monthly Rent Amount (QAR) *</label>
                    <input type="number" step="0.01" min="0" name="monthly_rent_amount" id="rentInput" class="form-input" required value="{{ old('monthly_rent_amount', number_format($defaultRent, 2, '.', '')) }}" oninput="calculateTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label">Utility / Storage Extras (QAR)</label>
                    <input type="number" step="0.01" min="0" name="utility_charges" id="utilityInput" class="form-input" value="{{ old('utility_charges', '0.00') }}" oninput="calculateTotal()">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Remarks / Payment Instructions</label>
                    <textarea name="remarks" class="form-input" rows="3" placeholder="Additional notes or payment instructions...">{{ old('remarks', 'Monthly warehouse lease payment for storage space.') }}</textarea>
                </div>
            </div>

            <div class="total-summary-card">
                <div class="total-summary-label">
                    <i class="ph ph-calculator" style="margin-right: 0.5rem;"></i> Total Rent Invoice Amount:
                </div>
                <div class="total-summary-value" id="totalAmountText">
                    QAR {{ number_format($defaultRent, 2) }}
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('rent-invoices.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check-circle"></i> Generate Rent Invoice
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function calculateTotal() {
                const rent = parseFloat(document.getElementById('rentInput').value) || 0;
                const utilities = parseFloat(document.getElementById('utilityInput').value) || 0;
                const grandTotal = rent + utilities;

                document.getElementById('totalAmountText').textContent = 'QAR ' + grandTotal.toFixed(2);
            }

            document.addEventListener('DOMContentLoaded', () => {
                calculateTotal();
            });
        </script>
    @endpush
@endsection
