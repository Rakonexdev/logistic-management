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

        .grid-panels {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .grid-panels {
                grid-template-columns: 1fr;
            }
        }

        .form-panel {
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .form-input, .form-select {
            width: 100%;
            box-sizing: border-box;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            transition: all 0.2s;
        }

        [data-theme="light"] .form-input,
        [data-theme="light"] .form-select {
            background: rgba(0, 0, 0, 0.02);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-select option {
            background: var(--bg-color);
            color: var(--text-primary);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th, .data-table td {
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

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: inherit;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: #ffffff;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-available { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .badge-reserved { background: rgba(245, 158, 11, 0.15); color: var(--warning); }

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
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 2rem;
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
            <i class="ph ph-stack"></i> Location & Stock Management
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline" onclick="alert('Export completed successfully!')">
                <i class="ph ph-export"></i> Export Inventory
            </button>
            <a href="{{ route('sfq.locations.create') }}" class="btn btn-primary">
                <i class="ph ph-plus"></i> Create Location
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-panels">
        <!-- Stock placement table -->
        <div class="form-panel glass">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h3 style="font-size: 1.1rem; color: var(--text-primary); margin: 0;">Warehouse Layout & Inventory</h3>
                <form method="GET" action="{{ route('sfq.locations.index') }}" style="display: flex; gap: 0.5rem; max-width: 500px; width: 100%; align-items: center;">
                    <input type="text" name="search" class="form-input" style="padding: 0.5rem 0.75rem;" placeholder="Search SKU or Location..." value="{{ request('search') }}">
                    <select name="per_page" class="form-select" style="padding: 0.5rem 0.75rem; width: auto;" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10 Per Page</option>
                        <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20 Per Page</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Per Page</option>
                    </select>
                    @if(request()->anyFilled(['search', 'per_page']))
                        <a href="{{ route('sfq.locations.index') }}" class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table" id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Warehouse</th>
                            <th>Zone</th>
                            <th>Rack</th>
                            <th>Bin</th>
                            <th>Level</th>
                            <th>SKU Code</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locations as $loc)
                            <tr>
                                <td>{{ $loc->warehouse }}</td>
                                <td><strong>{{ $loc->zone }}</strong></td>
                                <td>{{ $loc->rack }}</td>
                                <td>{{ $loc->bin }}</td>
                                <td>{{ $loc->level }}</td>
                                <td><strong>{{ $loc->sku }}</strong></td>
                                <td>{{ $loc->qty }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($loc->status) }}">
                                        {{ $loc->status }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <a href="{{ route('sfq.locations.edit', $loc->id) }}" class="btn btn-outline" style="padding: 0.35rem 0.5rem; font-size: 0.85rem;" title="Edit Location">
                                            <i class="ph ph-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline" onclick="openDeleteModal('{{ route('sfq.locations.destroy', $loc->id) }}')" style="padding: 0.35rem 0.5rem; font-size: 0.85rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);" title="Delete Location">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="pagination-wrapper">
                <div style="color: var(--text-secondary); font-size: 0.875rem;">
                    Showing {{ $locations->firstItem() ?? 0 }} to {{ $locations->lastItem() ?? 0 }} of {{ $locations->total() }} records
                </div>
                {{ $locations->links('pagination::bootstrap-4') }}
            </div>
        </div>

        <!-- Transfer form -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="form-panel glass">
                <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Transfer Stock</h3>
                <form action="{{ route('sfq.locations.transfer') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="transfer_ref">Transfer Reference</label>
                        <input type="text" id="transfer_ref" name="transfer_ref" class="form-input" value="TRF-{{ time() }}" readonly required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sku">SKU Code</label>
                        <select id="sku" name="sku" class="form-select" required>
                            <option value="">Select SKU</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->sku_code }}">{{ $prod->sku_code }} - {{ $prod->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="qty">Quantity</label>
                        <input type="number" id="qty" name="qty" class="form-input" min="1" required placeholder="e.g. 50">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="source">Source Location</label>
                        <input type="text" id="source" name="source" class="form-input" required placeholder="e.g. WH-Main-A-01">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="destination">Destination Location</label>
                        <input type="text" id="destination" name="destination" class="form-input" required placeholder="e.g. WH-Main-B-05">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                        <i class="ph ph-arrows-left-right"></i> Execute Transfer
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
    <div class="modal-content glass">
        <h3 class="modal-title"><i class="ph ph-warning" style="color: var(--danger);"></i> Confirm Delete</h3>
        <p class="modal-message">Are you sure you want to delete this warehouse location? This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-primary" style="background: var(--danger);">
                    Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function openDeleteModal(actionUrl) {
            document.getElementById('deleteForm').action = actionUrl;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
    </script>
@endpush


