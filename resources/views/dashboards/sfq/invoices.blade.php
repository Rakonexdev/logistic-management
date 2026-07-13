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

        .grid-panels {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .grid-panels {
                grid-template-columns: 1fr;
            }
        }

        .form-panel {
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
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
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-select option {
            background: var(--bg-color);
            color: var(--text-primary);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th, .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: inherit;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: #ffffff;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-issued { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .badge-draft { background: rgba(255, 255, 255, 0.1); color: var(--text-secondary); }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-receipt"></i> Invoicing & Payments
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-panels">
        <!-- Invoices List -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Billing Invoices</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Type</th>
                            <th>Billing Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                            <tr>
                                <td><strong>{{ $inv['number'] }}</strong></td>
                                <td>{{ $inv['type'] }}</td>
                                <td>{{ $inv['period'] }}</td>
                                <td>${{ number_format($inv['amount'], 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($inv['status']) }}">
                                        {{ $inv['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="alert('PDF downloaded successfully!')" class="btn btn-outline" style="padding: 0.5rem; font-size: 0.8rem;" title="Download PDF">
                                            <i class="ph ph-file-pdf"></i> PDF
                                        </button>
                                        @if($inv['status'] === 'Draft')
                                            <button onclick="alert('Invoice issued successfully!')" class="btn btn-primary" style="padding: 0.5rem; font-size: 0.8rem;">
                                                <i class="ph ph-paper-plane-tilt"></i> Issue
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Invoice Form -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Create Invoice</h3>
            <form action="{{ route('sfq.invoices.create') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="invoice_type">Invoice Type</label>
                    <select id="invoice_type" name="invoice_type" class="form-select" required>
                        <option value="Delivery Charges">Delivery Charges</option>
                        <option value="Warehouse Rent">Warehouse Rent</option>
                        <option value="Cheque Collection">Cheque Collection</option>
                        <option value="Miscellaneous">Miscellaneous</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="billing_period">Billing Period</label>
                    <input type="text" id="billing_period" name="billing_period" class="form-input" placeholder="e.g. July 2026" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="amount">Invoice Amount ($)</label>
                    <input type="number" id="amount" name="amount" class="form-input" step="0.01" min="0.01" required placeholder="e.g. 500">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                    <i class="ph ph-plus-circle"></i> Create Invoice Draft
                </button>
            </form>
        </div>
    </div>
@endsection
