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
            width: 300px;
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

        .badge-created { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-assigned { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge-picked { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
        .badge-stored { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .badge-shipped { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
        .badge-completed { background: rgba(34, 197, 94, 0.2); color: #22c55e; }

        .badge-inspection-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge-inspection-passed { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .badge-inspection-failed { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-arrow-u-up-left"></i> Return Instructions
        </h1>
        <a href="{{ route('return-instructions.create') }}" class="btn btn-primary">
            <i class="ph ph-plus"></i> Issue Return Instruction
        </a>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass" style="padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
        <form method="GET" action="{{ route('return-instructions.index') }}" class="toolbar" id="filterForm">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="Search by Ref or Customer..." oninput="debouncedSearch()">
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <select name="status" class="search-input" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Created" {{ request('status') == 'Created' ? 'selected' : '' }}>Created</option>
                    <option value="Driver Assigned" {{ request('status') == 'Driver Assigned' ? 'selected' : '' }}>Driver Assigned</option>
                    <option value="Picked Up" {{ request('status') == 'Picked Up' ? 'selected' : '' }}>Picked Up</option>
                    <option value="Stored" {{ request('status') == 'Stored' ? 'selected' : '' }}>Stored</option>
                    <option value="Shipped to END" {{ request('status') == 'Shipped to END' ? 'selected' : '' }}>Shipped to END</option>
                </select>
            </div>
        </form>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Return Ref</th>
                        <th>Customer / Pickup Location</th>
                        <th>Returned Items</th>
                        <th>Received Date</th>
                        <th>Status</th>
                        <th>Inspection</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instructions as $ret)
                        <tr>
                            <td><strong>{{ $ret->return_ref }}</strong></td>
                            <td>
                                <div><strong>{{ $ret->customer_name }}</strong></div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $ret->pickup_address }}</div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                    @foreach($ret->items as $item)
                                        <div style="font-size: 0.85rem;">
                                            <strong>{{ $item->sku_code }}</strong> (x{{ $item->quantity }})
                                            @if($item->serial_numbers)
                                                <span style="color: var(--text-secondary); font-size: 0.75rem;">S/N: {{ $item->serial_numbers }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $ret->instruction_received_date ? $ret->instruction_received_date->format('Y-m-d H:i') : '-' }}</td>
                            <td>
                                @php
                                    $statusKey = strtolower(explode(' ', $ret->status)[0]);
                                @endphp
                                <span class="badge badge-{{ $statusKey }}">
                                    <i class="ph ph-clock"></i> {{ $ret->status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-inspection-{{ strtolower(explode(' ', $ret->inspection_status)[0]) }}">
                                    {{ $ret->inspection_status }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('return-instructions.show', $ret->id) }}" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="View Workflow & Timeline">
                                        <i class="ph ph-eye"></i> View
                                    </a>
                                    <a href="{{ route('return-instructions.print', $ret->id) }}" target="_blank" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="Print Return Document">
                                        <i class="ph ph-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                <i class="ph ph-arrow-u-up-left" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                No Return Instructions issued yet. Click "Issue Return Instruction" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $instructions->links() }}
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
