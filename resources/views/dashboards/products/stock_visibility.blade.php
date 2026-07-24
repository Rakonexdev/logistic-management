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

        .search-filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .search-input-wrapper {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-input {
            width: 100%;
            box-sizing: border-box;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
        }

        [data-theme="light"] .search-input {
            background: rgba(0, 0, 0, 0.02);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-primary);
        }

        .search-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .filter-select {
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            appearance: none;
            cursor: pointer;
        }

        [data-theme="light"] .filter-select {
            background: rgba(0, 0, 0, 0.02);
        }

        .filter-select option {
            background: var(--bg-color);
            color: var(--text-primary);
        }

        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .badge-inactive {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .stock-qty {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .stock-available {
            color: var(--accent-primary);
        }

        .pagination-wrapper {
            padding: 1rem 0 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 0.25rem;
        }

        .page-item .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 0.5rem;
            border-radius: 6px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        [data-theme="light"] .page-item .page-link {
            background: rgba(0, 0, 0, 0.05);
        }

        .page-item:not(.disabled):not(.active) .page-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-secondary);
        }

        .page-item.active .page-link {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }

        .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-stack"></i> Stock
        </h1>
    </div>

    <div class="glass" style="padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
        <!-- Filter Form -->
        <form action="{{ route('products.stock-visibility') }}" method="GET" class="search-filter-bar" id="filterForm">
            <div class="search-input-wrapper">
                <i class="ph ph-magnifying-glass search-icon"></i>
                <input type="text" name="search" class="search-input" placeholder="Search by SKU code, product name or serial number..."
                    value="{{ request('search') }}" oninput="debouncedSearch()">
            </div>

            <div style="position: relative;">
                <select name="per_page" class="filter-select" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10 Per Page</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 Per Page</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Per Page</option>
                </select>
                <i class="ph ph-caret-down"
                    style="position: absolute; right: 0.875rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-secondary);"></i>
            </div>

            @if(request()->anyFilled(['search', 'per_page']))
                <a href="{{ route('products.stock-visibility') }}" class="btn btn-outline"
                    style="padding: 0.75rem 1rem; font-size: 0.875rem; border-radius: 8px;">
                    Clear Filters
                </a>
            @endif
        </form>

        <!-- Stock Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU Code</th>
                        <th>Serial Number</th>
                        <th>Product Name</th>
                        <th>Location</th>
                        <th>Available Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><strong>{{ $product->sku_code }}</strong></td>
                            <td>
                                @php
                                    $serials = array_filter(array_map('trim', explode(',', $product->serial_number ?? '')));
                                    $totalSerials = count($serials);
                                @endphp
                                @if($product->available_qty <= 0)
                                    <span style="color: var(--danger, #ef4444); font-style: italic; font-weight: 500;">Stock Not Available</span>
                                @elseif($totalSerials === 0)
                                    <span style="color: var(--text-secondary); font-style: italic;">No Serial Number</span>
                                @elseif($totalSerials === 1)
                                    <span>{{ $serials[0] }}</span>
                                @else
                                    <div class="serial-toggle-container" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                                        <span class="serial-short" style="font-size: 0.875rem;">{{ $serials[0] }}</span>
                                        <span class="serial-full" style="display: none; font-size: 0.875rem; word-break: break-word;">{{ implode(', ', $serials) }}</span>
                                        <button type="button" class="serial-toggle-btn" onclick="toggleSerials(this)" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.25); color: var(--accent-primary, #6366f1); font-size: 0.75rem; font-weight: 500; border-radius: 4px; padding: 0.15rem 0.4rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem; transition: all 0.2s;">
                                            <i class="ph ph-caret-down serial-icon" style="font-size: 0.75rem;"></i>
                                            <span class="serial-btn-text">+{{ $totalSerials - 1 }} View More</span>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->location_info }}</td>
                            <td class="stock-qty stock-available">{{ $product->available_qty }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 2rem 0;">
                                No products found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            <div style="color: var(--text-secondary); font-size: 0.875rem;">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
                records
            </div>
            {{ $products->links('pagination::bootstrap-4') }}
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

            function toggleSerials(btn) {
                const container = btn.closest('.serial-toggle-container');
                const shortSpan = container.querySelector('.serial-short');
                const fullSpan = container.querySelector('.serial-full');
                const btnText = btn.querySelector('.serial-btn-text');
                const icon = btn.querySelector('.serial-icon');
                
                if (fullSpan.style.display === 'none') {
                    fullSpan.style.display = 'inline';
                    shortSpan.style.display = 'none';
                    btnText.textContent = 'View Less';
                    if (icon) icon.className = 'ph ph-caret-up serial-icon';
                } else {
                    fullSpan.style.display = 'none';
                    shortSpan.style.display = 'inline';
                    const total = fullSpan.textContent.split(',').length;
                    btnText.textContent = `+${total - 1} View More`;
                    if (icon) icon.className = 'ph ph-caret-down serial-icon';
                }
            }
        </script>
    @endpush
@endsection
