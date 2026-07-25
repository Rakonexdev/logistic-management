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
            <a href="{{ route('cheque-collection-invoices.print', $invoice->id) }}" target="_blank" class="btn btn-outline">
                <i class="ph ph-printer"></i> Print Invoice
            </a>
            <a href="{{ route('cheque-collection-invoices.index') }}" class="btn btn-outline">
                <i class="ph ph-arrow-left"></i> Back to Invoices
            </a>
        </div>
    </div>

    <div class="glass details-panel">
        @php
            $totalPaidSum = 0;
            $allPayments = collect();

            foreach ($invoice->items as $item) {
                $chq = $item->chequeCollection;
                if ($chq) {
                    if ($chq->payments && $chq->payments->count() > 0) {
                        foreach ($chq->payments as $p) {
                            $allPayments->push([
                                'payment_number' => $p->payment_number,
                                'collection_ref' => $chq->collection_ref,
                                'cheque_number' => $p->cheque_number ?: $chq->cheque_number ?: $chq->collection_ref,
                                'paid_amount' => $p->paid_amount,
                                'remaining_balance' => $p->remaining_balance,
                                'date' => $p->created_at ? $p->created_at->format('Y-m-d H:i') : ($p->cheque_date ? $p->cheque_date->format('Y-m-d') : '-'),
                                'driver' => $p->driver ?: $chq->driver ?: 'Driver',
                                'photo_path' => $p->photo_path ?: $chq->photo_path,
                                'status' => 'Collected',
                            ]);
                            $totalPaidSum += (float) $p->paid_amount;
                        }
                    } else if (in_array($chq->status, ['Collected', 'Submitted']) && (float) $chq->paid_amount > 0) {
                        $paid = (float) $chq->paid_amount;
                        $rem = max(0, (float)$chq->amount - $paid);
                        $allPayments->push([
                            'payment_number' => 1,
                            'collection_ref' => $chq->collection_ref,
                            'cheque_number' => $chq->cheque_number ?: $chq->collection_ref,
                            'paid_amount' => $paid,
                            'remaining_balance' => $rem,
                            'date' => $chq->submission_time ? $chq->submission_time->format('Y-m-d H:i') : $chq->updated_at->format('Y-m-d H:i'),
                            'driver' => $chq->driver ?: 'Driver',
                            'photo_path' => $chq->photo_path,
                            'status' => $chq->status,
                        ]);
                        $totalPaidSum += $paid;
                    }
                }
            }
            $remainingBalanceTotal = max(0, (float) $invoice->total_amount - $totalPaidSum);

            $computedStatus = 'Unpaid';
            if ($totalPaidSum >= (float) $invoice->total_amount && (float) $invoice->total_amount > 0) {
                $computedStatus = 'Paid';
            } elseif ($totalPaidSum > 0) {
                $computedStatus = 'Partially Paid';
            }
        @endphp

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
                    <span class="badge badge-{{ strtolower(str_replace(' ', '-', $computedStatus)) }}">
                        <i class="ph {{ strtolower($computedStatus) === 'paid' ? 'ph-check-circle' : (strtolower($computedStatus) === 'partially paid' ? 'ph-clock-counter-clockwise' : 'ph-clock') }}"></i> {{ ucfirst($computedStatus) }}
                    </span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-label">Assigned Driver</span>
                <span class="info-value">
                    <i class="ph ph-user"></i> {{ $invoice->items->first()?->chequeCollection?->driver ?: 'Not Assigned' }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Total Invoice Amount</span>
                <span class="info-value" style="color: var(--accent-primary, #6366f1); font-size: 1.2rem;">
                    QAR {{ number_format($invoice->total_amount, 2) }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Total Paid Amount</span>
                <span class="info-value" style="color: #10b981; font-size: 1.2rem;">
                    QAR {{ number_format($totalPaidSum, 2) }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Remaining Balance</span>
                <span class="info-value" style="color: {{ $remainingBalanceTotal > 0 ? '#f59e0b' : '#10b981' }}; font-size: 1.2rem;">
                    QAR {{ number_format($remainingBalanceTotal, 2) }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Collection Fee Rate</span>
                <span class="info-value">QAR 35.00 / Cheque</span>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; color: var(--text-primary);"><i class="ph ph-receipt"></i> Customer Payment & Cheque Collection History</h3>

        @if($allPayments->isEmpty())
            <div style="padding: 2rem; text-align: center; color: var(--text-secondary); background: rgba(0,0,0,0.1); border-radius: 10px; margin-bottom: 1.5rem;">
                <i class="ph ph-clock" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #f59e0b;"></i>
                No payments collected by driver yet. Outstanding Balance: <strong>QAR {{ number_format($invoice->total_amount, 2) }}</strong>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Collection Ref</th>
                        <th>Cheque Number</th>
                        <th>Date Collected</th>
                        <th style="text-align: right;">Paid Amount</th>
                        <th style="text-align: right;">Collection Fee</th>
                        <th style="text-align: right;">Remaining Balance</th>
                        <th>Collector / Driver</th>
                        <th>Proof Photo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allPayments as $idx => $p)
                        <tr>
                            <td><strong>Payment #{{ $p['payment_number'] ?? ($idx + 1) }}</strong></td>
                            <td>{{ $p['collection_ref'] }}</td>
                            <td>
                                <span style="font-family: monospace; background: rgba(99, 102, 241, 0.1); color: var(--accent-primary, #6366f1); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                    {{ $p['cheque_number'] }}
                                </span>
                            </td>
                            <td>{{ $p['date'] }}</td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">
                                QAR {{ number_format($p['paid_amount'], 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: var(--text-secondary);">
                                QAR 35.00
                            </td>
                            <td style="text-align: right; font-weight: 700; color: {{ $p['remaining_balance'] > 0 ? '#f59e0b' : '#10b981' }};">
                                QAR {{ number_format($p['remaining_balance'], 2) }}
                            </td>
                            <td><i class="ph ph-user"></i> {{ $p['driver'] }}</td>
                            <td>
                                @if(!empty($p['photo_path']))
                                    <a href="{{ asset($p['photo_path']) }}" target="_blank" style="color: var(--accent-primary, #6366f1); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <i class="ph ph-image"></i> View Proof
                                    </a>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-paid">
                                    <i class="ph ph-check-circle"></i> {{ $p['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700; font-size: 1rem;">TOTAL SUMMARY:</td>
                        <td style="text-align: right; font-weight: 800; font-size: 1.1rem; color: #10b981;">
                            QAR {{ number_format($totalPaidSum, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 1rem; color: var(--text-secondary);">
                            QAR {{ number_format($allPayments->count() * 35, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 800; font-size: 1.1rem; color: {{ $remainingBalanceTotal > 0 ? '#f59e0b' : '#10b981' }};">
                            QAR {{ number_format($remainingBalanceTotal, 2) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
    </div>
@endsection
