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

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
            width: fit-content;
        }

        .status-draft {
            background: rgba(107, 114, 128, 0.1);
            color: #9CA3AF;
        }

        .status-submitted {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
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
            margin-bottom: 2rem;
        }

        .items-table th {
            padding: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.02);
        }

        .items-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .attachment-link:hover {
            color: var(--accent-secondary);
            text-decoration: underline;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-file-text"></i>
            Sales Order Details: {{ $order->so_number }}
        </h1>
        <div style="display: flex; gap: 0.75rem;">
            @if($order->status === 'draft')
                <a href="{{ route('sales-orders.edit', $order->id) }}" class="btn btn-outline" style="background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); border-color: rgba(99, 102, 241, 0.2);">
                    <i class="ph ph-pencil-simple"></i> Edit Draft
                </a>
            @endif
            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline">
                <i class="ph ph-arrow-left"></i> Back to list
            </a>
        </div>
    </div>

    <div class="glass details-panel">
        <div class="section-title">General Information</div>
        <div class="details-grid">
            <div class="detail-item">
                <div class="detail-label">Sales Order Number</div>
                <div class="detail-value" style="font-weight: 700;">{{ $order->so_number }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Customer Name</div>
                <div class="detail-value">{{ $order->customer_name }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Customer Address</div>
                <div class="detail-value">{{ $order->customer_address ?: '—' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Order Date</div>
                <div class="detail-value">{{ $order->order_date->format('M d, Y') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge status-{{ strtolower($order->status) }}">
                        {{ $order->status }}
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Created At</div>
                <div class="detail-value">{{ $order->created_at->format('M d, Y H:i') }}</div>
            </div>
        </div>

        <div class="section-title">Line Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product / SKU</th>
                    <th>Ordered Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td style="font-weight: 600;">{{ $item->sku_code }}</td>
                        <td>{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Attachments & Remarks</div>
        <div class="details-grid">
            <div class="detail-item">
                <div class="detail-label">Excel Upload File (Reference)</div>
                <div class="detail-value" style="margin-top: 0.25rem;">
                    @if($order->excel_file_path)
                        <a href="{{ Storage::url($order->excel_file_path) }}" class="attachment-link" target="_blank" download>
                            <i class="ph ph-file-csv" style="font-size: 1.25rem;"></i> Download CSV/Excel
                        </a>
                    @else
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">No Excel attachment</span>
                    @endif
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">Attached PDF Document</div>
                <div class="detail-value" style="margin-top: 0.25rem;">
                    @if($order->pdf_file_path)
                        <a href="{{ Storage::url($order->pdf_file_path) }}" class="attachment-link" target="_blank" download>
                            <i class="ph ph-file-pdf" style="font-size: 1.25rem;"></i> Download PDF Document
                        </a>
                    @else
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">No PDF attachment</span>
                    @endif
                </div>
            </div>
        </div>

        @if($order->remarks)
            <div class="detail-item" style="margin-top: 1.5rem;">
                <div class="detail-label">Remarks / Notes</div>
                <div class="glass" style="padding: 1rem; border-radius: 8px; margin-top: 0.25rem; font-size: 0.9rem; line-height: 1.5; white-space: pre-line;">{{ $order->remarks }}</div>
            </div>
        @endif
    </div>
@endsection
