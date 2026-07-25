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
            <i class="ph ph-bank"></i> Cheque Collection Invoice: {{ $invoice->invoice_number }}
        </h1>
        <div style="display: flex; gap: 0.75rem;">
            @if(strtolower($invoice->status) === 'unpaid')
                <form action="{{ route('cheque-collection-invoices.mark-paid', $invoice->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background: #10b981; border-color: #10b981;">
                        <i class="ph ph-check"></i> Mark as Paid
                    </button>
                </form>
            @endif
            <a href="{{ route('cheque-collection-invoices.print', $invoice->id) }}" target="_blank" class="btn btn-outline">
                <i class="ph ph-printer"></i> Print Invoice
            </a>
            <a href="{{ route('cheque-collection-invoices.index') }}" class="btn btn-outline">
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
                <span class="info-label">Customer / Client</span>
                <span class="info-value">{{ $invoice->customer_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date Issued</span>
                <span class="info-value">{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
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
                <span class="info-label">Total Collection Fee</span>
                <span class="info-value" style="color: var(--accent-primary, #6366f1); font-size: 1.25rem;">
                    QAR {{ number_format($invoice->total_amount, 2) }}
                </span>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Cheque Details & Collection Fee Breakdown</h3>

        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Collection Ref</th>
                    <th>Cheque Number</th>
                    <th>Reference IDs (PO / SO / Inv)</th>
                    <th style="text-align: right;">Collected Cheque Amount</th>
                    <th style="text-align: right;">Collection Fee</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item->collection_ref }}</strong></td>
                        <td>
                            <span style="font-family: monospace; background: rgba(99, 102, 241, 0.1); color: var(--accent-primary, #6366f1); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                {{ $item->cheque_number ?: $item->collection_ref }}
                            </span>
                        </td>
                        <td>
                            @if($item->chequeCollection)
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                    PO: {{ $item->chequeCollection->po_reference ?: '-' }} | 
                                    SO: {{ $item->chequeCollection->so_reference ?: '-' }} | 
                                    Inv: {{ $item->chequeCollection->invoice_reference ?: '-' }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: right;">QAR {{ number_format($item->cheque_amount, 2) }}</td>
                        <td style="text-align: right; font-weight: 700; color: var(--accent-primary, #6366f1);">
                            QAR {{ number_format($item->collection_fee, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: 700; font-size: 1rem;">TOTAL SERVICE FEE AMOUNT:</td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.2rem; color: var(--accent-primary, #6366f1);">
                        QAR {{ number_format($invoice->total_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
