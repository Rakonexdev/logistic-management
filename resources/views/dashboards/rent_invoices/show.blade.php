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

        .details-panel {
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
            background: rgba(0,0,0,0.15);
            padding: 1.5rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .badge {
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-unpaid { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge-paid { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-currency-circle-dollar"></i> Rent Invoice: {{ $invoice->invoice_number }}
        </h1>
        <div style="display: flex; gap: 0.75rem;">
            @if(strtolower($invoice->status) === 'unpaid')
                <form action="{{ route('rent-invoices.mark-paid', $invoice->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background: #10b981; border-color: #10b981;">
                        <i class="ph ph-check"></i> Mark as Paid
                    </button>
                </form>
            @endif
            <a href="{{ route('rent-invoices.print', $invoice->id) }}" target="_blank" class="btn btn-outline">
                <i class="ph ph-printer"></i> Print Invoice
            </a>
            <a href="{{ route('rent-invoices.index') }}" class="btn btn-outline">
                <i class="ph ph-arrow-left"></i> Back to Rent Invoices
            </a>
        </div>
    </div>

    <div class="glass details-panel">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Invoice Number</span>
                <span class="info-value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Warehouse Facility</span>
                <span class="info-value">{{ $invoice->warehouse_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Rent Period / Month</span>
                <span class="info-value" style="color: var(--accent-primary, #6366f1);">{{ $invoice->rent_month }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Due Date</span>
                <span class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <div>
                    <span class="badge badge-{{ strtolower($invoice->status) }}">
                        <i class="ph {{ strtolower($invoice->status) === 'paid' ? 'ph-check-circle' : 'ph-clock' }}"></i> {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-label">Total Amount</span>
                <span class="info-value" style="color: var(--accent-primary, #6366f1); font-size: 1.25rem;">
                    QAR {{ number_format($invoice->total_amount, 2) }}
                </span>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Cost Breakdown</h3>

        <div style="background: rgba(0,0,0,0.1); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                <span>Monthly Warehouse Base Rent:</span>
                <strong>QAR {{ number_format($invoice->monthly_rent_amount, 2) }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                <span>Utility & Maintenance Extras:</span>
                <strong>QAR {{ number_format($invoice->utility_charges, 2) }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; font-size: 1.1rem; font-weight: 700; color: var(--accent-primary, #6366f1);">
                <span>TOTAL AMOUNT PAYABLE:</span>
                <span>QAR {{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>

        @if($invoice->remarks)
            <div style="margin-top: 1.5rem;">
                <h4 style="color: var(--text-secondary); margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase;">Remarks / Payment Instructions</h4>
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                    {{ $invoice->remarks }}
                </div>
            </div>
        @endif
    </div>
@endsection
