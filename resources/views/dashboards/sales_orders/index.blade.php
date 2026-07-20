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
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid var(--border-color);
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
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
        }

        .actions-group {
            display: flex;
            gap: 1rem;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th {
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border-color);
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-draft {
            background: rgba(107, 114, 128, 0.1);
            color: #9CA3AF;
        }

        .status-submitted {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .action-icon {
            color: var(--text-secondary);
            font-size: 1.25rem;
            transition: color 0.2s;
            cursor: pointer;
            text-decoration: none;
        }

        .action-icon:hover {
            color: var(--accent-primary);
        }

        /* Pagination overrides */
        .pagination {
            display: flex;
            justify-content: flex-end;
            padding: 1rem;
            gap: 0.25rem;
            margin: 0;
            list-style: none;
        }
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-color: transparent;
            color: white;
        }
        .page-link {
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
        }
        .page-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border-color: var(--border-color);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-shopping-cart"></i>
            Sales Orders (SO)
        </h1>
        <div class="actions-group">
            <a href="{{ route('sales-orders.export', request()->query()) }}" class="btn btn-outline">
                <i class="ph ph-export"></i> Export Orders
            </a>
            <a href="{{ route('sales-orders.create') }}" class="btn btn-primary">
                <i class="ph ph-plus"></i> Create Sales Order
            </a>
        </div>
    </div>

    @if(session('success'))
        <div id="success-alert" class="glass" style="padding: 1rem; margin-bottom: 1rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1);">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if(alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 3000);
        </script>
    @endif

    <div class="glass">
        <form method="GET" action="{{ route('sales-orders.index') }}" class="toolbar" id="filterForm">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="Search by SO number or Customer..." oninput="debouncedSearch()">
                <button type="submit" style="display: none;"></button>
            </div>
            <div class="actions-group">
                <select name="status" class="search-input" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                </select>
            </div>
        </form>
        <script>
            let searchTimeout;
            function debouncedSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 600);
            }
        </script>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SO Number</th>
                        <th>Customer Name</th>
                        <th>Customer Address</th>
                        <th>Order Date</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td style="font-weight: 600;">{{ $order->so_number }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->customer_address ?: '—' }}</td>
                            <td>{{ $order->order_date->format('M d, Y') }}</td>
                            <td>{{ $order->items()->count() }} items</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($order->status) }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('sales-orders.show', $order->id) }}" class="action-icon" title="View">
                                    <i class="ph ph-eye"></i>
                                </a>
                                @if($order->status === 'draft')
                                    <a href="{{ route('sales-orders.edit', $order->id) }}" class="action-icon" title="Edit" style="margin-left: 0.5rem;">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                <i class="ph ph-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No Sales Order records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="border-top: 1px solid var(--border-color);">
            {{ $orders->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
