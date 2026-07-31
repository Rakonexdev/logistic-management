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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            padding: 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
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
        .badge-paid { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-currency-circle-dollar"></i> Rent Invoices
        </h1>
        <a href="{{ route('rent-invoices.create') }}" class="btn btn-primary">
            <i class="ph ph-plus"></i> Create Rent Invoice
        </a>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <div class="stats-grid">
        <div class="glass stat-card">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: var(--accent-primary, #6366f1);">
                <i class="ph ph-warehouse"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">QAR {{ number_format($totalRentSum, 2) }}</div>
                <div class="stat-label">Total Warehouse Rent Invoiced</div>
            </div>
        </div>

        <div class="glass stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <i class="ph ph-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $unpaidCount }}</div>
                <div class="stat-label">Unpaid Rent Invoices</div>
            </div>
        </div>

        <div class="glass stat-card">
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">
                <i class="ph ph-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $paidCount }}</div>
                <div class="stat-label">Paid Rent Invoices</div>
            </div>
        </div>
    </div>

    <div class="glass" style="padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
        <form method="GET" action="{{ route('rent-invoices.index') }}" class="toolbar" id="filterForm">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="Search Invoice #, Warehouse or Month..." oninput="debouncedSearch()">
            </div>
        </form>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Warehouse / Facility</th>
                        <th>Rent Month / Period</th>
                        <th>Monthly Rent</th>
                        <th>Utilities / Extras</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>{{ $inv->created_at->format('Y-m-d') }}</td>
                            <td><strong>{{ $inv->invoice_number }}</strong></td>
                            <td>
                                <div><strong>{{ $inv->warehouse_name }}</strong></div>
                                @if($inv->due_date)
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">Due: {{ $inv->due_date->format('Y-m-d') }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--accent-primary, #6366f1); background: rgba(99, 102, 241, 0.1); padding: 0.2rem 0.6rem; border-radius: 6px;">
                                    <i class="ph ph-calendar-blank"></i> {{ $inv->rent_month }}
                                </span>
                            </td>
                            <td>QAR {{ number_format($inv->monthly_rent_amount, 2) }}</td>
                            <td>QAR {{ number_format($inv->utility_charges, 2) }}</td>
                            <td style="font-weight: 800; color: var(--accent-primary, #6366f1);">
                                QAR {{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td>
                                <span class="badge badge-{{ strtolower($inv->status) }}">
                                    <i class="ph {{ strtolower($inv->status) === 'paid' ? 'ph-check-circle' : 'ph-clock' }}"></i> {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    <a href="{{ route('rent-invoices.show', $inv->id) }}" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="View Rent Invoice">
                                        <i class="ph ph-eye"></i> View
                                    </a>
                                    <a href="{{ route('rent-invoices.print', $inv->id) }}" target="_blank" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="Print Rent Invoice">
                                        <i class="ph ph-printer"></i>
                                    </a>
                                    @if(strtolower($inv->status) === 'unpaid')
                                        <form action="{{ route('rent-invoices.mark-paid', $inv->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; color: #10b981; border-color: rgba(16, 185, 129, 0.4);" title="Mark as Paid">
                                                <i class="ph ph-check"></i> Paid
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                <i class="ph ph-currency-circle-dollar" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                No Rent Invoices created yet. Click "Create Rent Invoice" to generate one.
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
