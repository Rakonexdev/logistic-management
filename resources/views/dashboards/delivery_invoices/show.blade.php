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

        .badge-unpaid { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge-processing { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-issued { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-paid { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
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
        </div>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass details-panel">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Invoice Number</span>
                <span class="info-value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="badge badge-{{ strtolower($invoice->status) }}" style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="ph {{ strtolower($invoice->status) === 'paid' ? 'ph-check-circle' : (strtolower($invoice->status) === 'processing' ? 'ph-gear-six' : 'ph-clock') }}"></i> {{ ucfirst($invoice->status) }}
                    </span>
                </span>
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
                <span class="info-label">Lump Sum Amount</span>
                <span class="info-value">
                    QAR {{ number_format($invoice->lump_sum_amount ?? 0, 2) }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Total Invoice Amount</span>
                <span class="info-value" style="color: var(--accent-primary, #6366f1); font-size: 1.25rem;">
                    QAR {{ number_format($invoice->total_amount, 2) }}
                </span>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">SFQ Handling Charges Sheet</h3>

        @php
            $hasLineItemCharges = $invoice->items->sum('total_amount') > 0;
        @endphp

        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>SKU Code</th>
                    <th>Serial Number</th>
                    <th style="text-align: center;">Quantity</th>
                    @if($hasLineItemCharges)
                        <th style="text-align: right;">Unit Charge Amount</th>
                        <th style="text-align: right;">Line Total</th>
                    @endif
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
                        @if($hasLineItemCharges)
                            <td style="text-align: right;">QAR {{ number_format($item->charge_amount, 2) }}</td>
                            <td style="text-align: right; font-weight: 700; color: var(--accent-primary, #6366f1);">
                                QAR {{ number_format($item->total_amount, 2) }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $hasLineItemCharges ? 5 : 3 }}" style="text-align: right; font-weight: 700; font-size: 1rem;">TOTAL INVOICE AMOUNT:</td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.2rem; color: var(--accent-primary, #6366f1);">
                        QAR {{ number_format($invoice->total_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Remarks & Action Section -->
        @if(Auth::user()->role === 'end_user')
            <div style="margin-top: 2.5rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.15); border: 1px solid var(--border-color); border-radius: 12px;">
                <h4 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-chat-text" style="color: var(--accent-primary, #6366f1); font-size: 1.2rem;"></i> Delivery Invoice Remarks & Notes
                </h4>
                
                <form action="{{ route('delivery-invoices.remarks', $invoice->id) }}" method="POST">
                    @csrf
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <textarea name="remarks" class="form-input" rows="3" placeholder="Enter special instructions, remarks or notes for this delivery invoice..." style="width: 100%; box-sizing: border-box; padding: 0.75rem 1rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-family: inherit;">{{ old('remarks', $invoice->remarks) }}</textarea>
                        
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem;">
                                <i class="ph ph-floppy-disk"></i> Save Remarks
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- SFQ Status Acknowledge Section -->
            <div style="margin-top: 2.5rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.15); border: 1px solid var(--border-color); border-radius: 12px;">
                <h4 style="margin: 0 0 1.25rem 0; color: var(--text-primary); font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-check-square" style="color: var(--accent-primary, #6366f1); font-size: 1.2rem;"></i> Invoice Status Acknowledge (SFQ)
                </h4>

                <form action="{{ route('delivery-invoices.status', $invoice->id) }}" method="POST" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    @csrf
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 260px;">
                        <label style="font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); white-space: nowrap;">Acknowledge Status:</label>
                        <select name="status" class="form-input" style="padding: 0.6rem 1rem; border-radius: 8px; font-weight: 600; appearance: auto;" required>
                            <option value="Unpaid" {{ $invoice->status === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="Processing" {{ $invoice->status === 'Processing' ? 'selected' : '' }}>Processing</option>
                            <option value="Paid" {{ $invoice->status === 'Paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem;">
                        <i class="ph ph-check-circle"></i> Submit Acknowledge
                    </button>
                </form>
            </div>

            @if($invoice->remarks)
                <div style="margin-top: 1.5rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.15); border: 1px solid var(--border-color); border-radius: 12px;">
                    <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph ph-chat-text" style="color: var(--accent-primary, #6366f1); font-size: 1.2rem;"></i> Delivery Invoice Remarks & Notes
                    </h4>
                    <div style="font-size: 0.925rem; color: var(--text-primary); background: rgba(255, 255, 255, 0.03); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color); white-space: pre-wrap;">{{ $invoice->remarks }}</div>
                </div>
            @endif
        @endif
    </div>
@endsection
