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

    .action-btn {
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.15);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #10b981;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pagination-wrapper {
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        border-top: 1px solid var(--border-color);
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

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Custom Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        padding: 2rem;
        border-radius: 12px;
        max-width: 450px;
        width: 100%;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-title {
        font-size: 1.25rem;
        margin-top: 0;
        margin-bottom: 1rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .modal-message {
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }
</style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-users" style="color: var(--accent-primary);"></i>
            Customer Management
        </h1>
        <div class="actions-group">
            <a href="{{ route('customers.template') }}" class="btn btn-outline">
                <i class="ph ph-download-simple"></i> Download Template
            </a>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('csvFileInput').click()">
                <i class="ph ph-upload-simple"></i> Bulk Upload CSV
            </button>
            <form action="{{ route('customers.bulk-upload') }}" method="POST" enctype="multipart/form-data" id="bulkUploadForm" style="display: none;">
                @csrf
                <input type="file" name="csv_file" id="csvFileInput" accept=".csv" onchange="document.getElementById('bulkUploadForm').submit()">
            </form>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="ph ph-plus"></i> Add Customer
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success">
            <i class="ph ph-check-circle" style="font-size: 1.25rem;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->has('csv_file'))
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-warning-circle" style="font-size: 1.25rem;"></i>
            <span>{{ $errors->first('csv_file') }}</span>
        </div>
    @endif

    <div class="glass toolbar">
        <form action="{{ route('customers.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%; justify-content: space-between;">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" class="search-input" placeholder="Search by name, contact, address..." value="{{ request('search') }}">
            </div>
            
            @if(request('search'))
                <a href="{{ route('customers.index') }}" class="btn btn-outline">
                    <i class="ph ph-x"></i> Clear Search
                </a>
            @endif
        </form>
    </div>

    <div class="glass table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Contact Number</th>
                    <th>Address</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary);">
                            {{ $customer->name }}
                        </td>
                        <td>
                            @if($customer->contact_number)
                                <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <i class="ph ph-phone" style="color: var(--text-secondary);"></i>
                                    {{ $customer->contact_number }}
                                </span>
                            @else
                                <span style="color: var(--text-secondary); font-style: italic;">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($customer->address)
                                <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <i class="ph ph-map-pin" style="color: var(--text-secondary);"></i>
                                    {{ Str::limit($customer->address, 60) }}
                                </span>
                            @else
                                <span style="color: var(--text-secondary); font-style: italic;">N/A</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline action-btn" title="Edit Customer">
                                    <i class="ph ph-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-danger action-btn" onclick="openDeleteModal('{{ route('customers.destroy', $customer) }}', '{{ e($customer->name) }}')" title="Delete Customer">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="ph ph-users"></i>
                                <p>No customers found.</p>
                                <a href="{{ route('customers.create') }}" class="btn btn-primary" style="margin-top: 0.5rem;">
                                    <i class="ph ph-plus"></i> Add Your First Customer
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($customers->hasPages())
            <div class="pagination-wrapper">
                <div style="color: var(--text-secondary); font-size: 0.875rem;">
                    Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} records
                </div>
                {{ $customers->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay" style="display: none;">
        <div class="glass modal-content">
            <h3 class="modal-title">
                <i class="ph ph-warning" style="color: var(--danger);"></i>
                Confirm Delete
            </h3>
            <p class="modal-message">
                Are you sure you want to delete customer <strong id="deleteCustomerName"></strong>? This action cannot be undone.
            </p>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ph ph-trash"></i> Delete Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openDeleteModal(actionUrl, customerName) {
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteCustomerName').textContent = customerName;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeDeleteModal();
        }
    }
</script>
@endpush
