@extends('layouts.dashboard')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .actions-group {
        display: flex;
        gap: 0.75rem;
    }

    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 12px;
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
    
    .search-input:focus {
        outline: none;
        border-color: var(--accent-primary);
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
        padding: 1rem 1.5rem;
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

    .data-table tbody tr {
        transition: background 0.2s;
    }

    .data-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .data-table td {
        font-size: 0.875rem;
        color: var(--text-primary);
    }

    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge-active { background: rgba(16, 185, 129, 0.2); color: var(--success); }
    .badge-inactive { background: rgba(239, 68, 68, 0.2); color: var(--danger); }

    .action-icons {
        display: flex;
        gap: 0.5rem;
    }

    .icon-btn {
        background: transparent;
        border: none;
        color: var(--text-secondary);
        font-size: 1.1rem;
        cursor: pointer;
        transition: color 0.2s;
        padding: 0.25rem;
    }

    .icon-btn:hover {
        color: var(--accent-primary);
    }
    .icon-btn.danger:hover {
        color: var(--danger);
    }
    
    .pagination-wrapper {
        padding: 1rem;
        display: flex;
        justify-content: flex-end;
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
            <i class="ph ph-box-box"></i>
            Product / SKU Management
        </h1>
        <div class="actions-group">
            <a href="{{ route('products.template') }}" class="btn btn-outline">
                <i class="ph ph-download-simple"></i> Download Template
            </a>
            
            <form action="{{ route('products.bulk-upload') }}" method="POST" enctype="multipart/form-data" id="bulkUploadForm" style="display: none;">
                @csrf
                <input type="file" name="csv_file" id="csvFileInput" accept=".csv" onchange="document.getElementById('bulkUploadForm').submit()">
            </form>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('csvFileInput').click()">
                <i class="ph ph-upload-simple"></i> Bulk Upload
            </button>
            
            <a href="{{ route('products.create') }}" class="btn btn-primary" style="color: white;">
                <i class="ph ph-plus"></i> Add Product
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
        <form method="GET" action="{{ route('products.index') }}" class="toolbar" id="filterForm">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="Search by SKU or Name..." oninput="debouncedSearch()">
                <!-- Hidden submit button so hitting Enter works smoothly -->
                <button type="submit" style="display: none;"></button>
            </div>
            <div class="actions-group">
                <select name="category" class="search-input" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Product::distinct('category')->whereNotNull('category')->pluck('category') as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                <select name="status" class="search-input" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <a href="{{ route('products.export') }}" class="btn btn-outline">
                    <i class="ph ph-export"></i> Export
                </a>
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
                        <th>SKU Code</th>
                        <th>Product Name</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td style="font-weight: 600;">{{ $product->sku_code }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ ucfirst($product->type) }}</td>
                            <td>{{ $product->category ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $product->status == 'active' ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $product->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <a href="{{ route('products.edit', $product->id) }}" class="icon-btn" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete this product?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn danger" title="Delete">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                <i class="ph ph-archive" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                                No products found. Add one to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            {{ $products->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
