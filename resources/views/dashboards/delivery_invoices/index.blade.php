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

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            width: 320px;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .search-input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.875rem;
            box-sizing: border-box;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th, .data-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
        .badge-processing { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-issued { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-paid { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-receipt"></i> Delivery Invoices
        </h1>
        <a href="{{ route('delivery-invoices.create') }}" class="btn btn-primary">
            <i class="ph ph-plus"></i> Create Delivery Invoice
        </a>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass" style="padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
        <form method="GET" action="{{ route('delivery-invoices.index') }}" class="toolbar" id="filterForm">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="Search Invoice #, Customer or SO..." oninput="debouncedSearch()">
            </div>
        </form>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>DI Ref</th>
                        <th>Customer / Destination</th>
                        <th>SO Reference</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>{{ $inv->created_at->format('Y-m-d') }}</td>
                            <td><strong>{{ $inv->invoice_number }}</strong></td>
                            <td>
                                <div><strong>{{ $inv->deliveryInstruction->di_number ?? '-' }}</strong></div>
                                @php
                                    $assignedDriver = $inv->deliveryInstruction ? $inv->deliveryInstruction->deliveryNotes->pluck('driver')->filter()->first() : null;
                                @endphp
                                @if($assignedDriver)
                                    <div style="font-size: 0.75rem; color: var(--accent-primary, #6366f1); display: flex; align-items: center; gap: 0.2rem; margin-top: 0.2rem;">
                                        <i class="ph ph-user"></i> {{ $assignedDriver }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div><strong>{{ $inv->customer_name }}</strong></div>
                                @if($inv->end_user_name)
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">End User: {{ $inv->end_user_name }}</div>
                                @endif
                            </td>
                            <td>{{ $inv->so_reference ?: '-' }}</td>
                            <td style="font-weight: 700; color: var(--accent-primary, #6366f1);">
                                QAR {{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td>
                                <span class="badge badge-{{ strtolower($inv->status) }}">
                                    <i class="ph {{ strtolower($inv->status) === 'paid' ? 'ph-check-circle' : (strtolower($inv->status) === 'processing' ? 'ph-gear-six' : 'ph-clock') }}"></i> {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.4rem;">
                                    <a href="{{ route('delivery-invoices.show', $inv->id) }}" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="View Invoice">
                                        <i class="ph ph-eye"></i> View
                                    </a>
                                    <a href="{{ route('delivery-invoices.print', $inv->id) }}" target="_blank" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="Print Invoice">
                                        <i class="ph ph-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                <i class="ph ph-receipt" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                No Delivery Invoices created yet. Click "Create Delivery Invoice" to generate one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $invoices->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            let searchTimeout;
            function debouncedSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 600);
            }
        </script>
    @endpush
@endsection
