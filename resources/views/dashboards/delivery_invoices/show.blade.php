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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .data-table th, .data-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .data-table th {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            background: rgba(0, 0, 0, 0.2);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-receipt"></i> Delivery Invoice: {{ $invoice->invoice_number }}
        </h1>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('delivery-invoices.print', $invoice->id) }}" target="_blank" class="btn btn-primary">
                <i class="ph ph-printer"></i> Print Invoice
            </a>
            <a href="{{ route('delivery-invoices.index') }}" class="btn btn-outline">
                <i class="ph ph-arrow-left"></i> Back to Invoices
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
                <span class="info-label">DI Reference</span>
                <span class="info-value">{{ $invoice->deliveryInstruction->di_number ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Customer / Destination</span>
                <span class="info-value">{{ $invoice->customer_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">SO Reference</span>
                <span class="info-value">{{ $invoice->so_reference ?: '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">End User Name</span>
                <span class="info-value">{{ $invoice->end_user_name ?: '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Total Invoice Amount</span>
                <span class="info-value" style="color: var(--accent-primary, #6366f1); font-size: 1.25rem;">
                    QAR {{ number_format($invoice->total_amount, 2) }}
                </span>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Serial Number Charge Breakdown</h3>

        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>SKU Code</th>
                    <th>Serial Number</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: right;">Unit Charge Amount</th>
                    <th style="text-align: right;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item->sku_code }}</strong></td>
                        <td>
                            @if($item->serial_number)
                                <span style="font-family: monospace; background: rgba(99, 102, 241, 0.1); color: var(--accent-primary, #6366f1); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                    {{ $item->serial_number }}
                                </span>
                            @else
                                <span style="color: var(--text-secondary); font-style: italic;">No Serial Number</span>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">QAR {{ number_format($item->charge_amount, 2) }}</td>
                        <td style="text-align: right; font-weight: 700; color: var(--accent-primary, #6366f1);">
                            QAR {{ number_format($item->total_amount, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: 700; font-size: 1rem;">TOTAL INVOICE AMOUNT:</td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.2rem; color: var(--accent-primary, #6366f1);">
                        QAR {{ number_format($invoice->total_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
