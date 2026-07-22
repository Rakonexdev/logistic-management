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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.05em;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
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
        }

        .items-table th {
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.02);
        }

        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-draft {
            background: rgba(107, 114, 128, 0.1);
            color: #9CA3AF;
        }

        .status-submitted {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }
        
        .status-processing {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
        }
        
        .status-completed {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .status-discrepancy {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }
        
        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .attachment-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-primary);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-file-text"></i>
            ASN Details: {{ $asn->asn_reference }}
        </h1>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <a href="{{ route('asns.report', $asn->id) }}" target="_blank" class="btn btn-primary">
                <i class="ph ph-printer"></i> Generate Report
            </a>
            @if(Auth::user()->role === 'sfq_user')
                <a href="{{ route('sfq.grns.index') }}" class="btn btn-outline">
                    <i class="ph ph-arrow-left"></i> Back to GRNs
                </a>
            @else
                <a href="{{ route('asns.index') }}" class="btn btn-outline">
                    <i class="ph ph-arrow-left"></i> Back to ASNs
                </a>
            @endif
        </div>
    </div>

    <div class="glass details-panel">
        <div class="section-title">General Information</div>
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <span class="status-badge status-{{ strtolower($asn->status) }}">
                        {{ $asn->status }}
                    </span>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Airway Bill Number</span>
                <span class="detail-value">{{ $asn->airway_bill }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Vendor / Supplier</span>
                <span class="detail-value">{{ $asn->vendor_id }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Created At</span>
                <span class="detail-value">{{ $asn->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="section-title">Equipment List ({{ $asn->items->count() }} items)</div>
        <div style="overflow-x: auto; margin-bottom: 2rem;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product / SKU Code</th>
                        <th>Expected Qty</th>
                        <th>Received Qty</th>
                        <th>Discrepancy Qty</th>
                        <th>Discrepancy Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asn->items as $index => $item)
                        <tr>
                            <td style="color: var(--text-secondary);">{{ $index + 1 }}</td>
                            <td style="font-weight: 600;">
                                {{ $item->sku_code }}
                                @if($item->serial_numbers)
                                    <div style="font-size: 0.75rem; color: var(--success); font-family: monospace; font-weight: normal; margin-top: 0.25rem;">
                                        S/N: {{ $item->serial_numbers }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->received_qty ?? '-' }}</td>
                            <td>
                                @if($item->discrepancy_qty !== null && $item->discrepancy_qty != 0)
                                    <span style="color: {{ $item->discrepancy_qty > 0 ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                                        {{ abs($item->discrepancy_qty) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->discrepancy_reason ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section-title">Remarks & Attachments</div>
        <div class="details-grid" style="grid-template-columns: 1fr;">
            @if($asn->remarks)
                <div class="detail-item">
                    <span class="detail-label">Supporting Remarks</span>
                    <span class="detail-value" style="background: rgba(0,0,0,0.1); padding: 1rem; border-radius: 8px;">
                        {{ $asn->remarks }}
                    </span>
                </div>
            @endif
            
            <div class="detail-item">
                <span class="detail-label" style="margin-bottom: 0.5rem;">Uploaded Files</span>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @if($asn->airway_bill_path)
                        <a href="{{ Storage::url($asn->airway_bill_path) }}" target="_blank" class="attachment-link">
                            <i class="ph ph-file-pdf" style="color: #ef4444; font-size: 1.25rem;"></i>
                            Scanned Airway Bill
                        </a>
                    @endif
                    
                    @if($asn->additional_attachments_path)
                        <a href="{{ Storage::url($asn->additional_attachments_path) }}" target="_blank" class="attachment-link">
                            <i class="ph ph-paperclip" style="color: var(--accent-primary); font-size: 1.25rem;"></i>
                            Additional Attachments
                        </a>
                    @endif
                    
                    @if(!$asn->airway_bill_path && !$asn->additional_attachments_path)
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">No attachments uploaded.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
