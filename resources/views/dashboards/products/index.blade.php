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
            <i class="ph ph-box-box"></i>
            Product Management
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
                <select name="status" class="search-input" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <select name="per_page" class="search-input" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10 Per Page</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 Per Page</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Per Page</option>
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
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $serials = array_values(array_filter(array_map('trim', explode(',', $product->serial_number ?? ''))));
                        @endphp
                        <tr>
                            <td style="font-weight: 600;">{{ $product->sku_code }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ ucfirst($product->type) }}</td>
                            <td>
                                <span class="badge {{ $product->status == 'active' ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $product->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="icon-btn" title="View Serial Numbers" 
                                        onclick="openSerialModal('{{ e($product->sku_code) }}', '{{ e($product->name) }}', {{ json_encode($serials) }}, {{ $product->qty }})">
                                        <i class="ph ph-eye"></i>
                                    </button>
                                    <a href="{{ route('products.edit', $product->id) }}" class="icon-btn" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                <i class="ph ph-archive" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                                No products found. Add one to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            <div style="color: var(--text-secondary); font-size: 0.875rem;">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} records
            </div>
            {{ $products->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

<!-- Custom Serial Numbers Modal -->
<div id="serialModal" class="modal-overlay" style="display: none;">
    <div class="modal-content glass" style="max-width: 660px; width: 92%; text-align: left; padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="modal-title" style="margin: 0; justify-content: flex-start; font-size: 1.2rem; color: var(--text-primary);">
                <i class="ph ph-barcode" style="color: var(--accent-primary, #6366f1); font-size: 1.35rem;"></i> Serial Numbers
            </h3>
            <button type="button" onclick="closeSerialModal()" style="background: transparent; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; padding: 0.25rem; border-radius: 4px;" title="Close">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div style="margin-bottom: 1rem; padding: 0.85rem 1rem; background: var(--bg-color, #0f111a); border: 1px solid var(--border-color); border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="background: rgba(99, 102, 241, 0.15); color: var(--accent-primary, #6366f1); font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 4px; letter-spacing: 0.05em; text-transform: uppercase;">SKU</span>
                <span id="modalSkuCode" style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary); font-family: monospace;"></span>
            </div>
            <div style="font-weight: 600; color: var(--text-primary); font-size: 1rem;" id="modalProductName">Product Name</div>
        </div>

        <!-- Modal Search Input -->
        <div id="modalSearchWrapper" style="margin-bottom: 1rem; position: relative; display: none;">
            <i class="ph ph-magnifying-glass" style="position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 1.1rem;"></i>
            <input type="text" id="modalSerialSearch" placeholder="Search serial numbers..." oninput="filterModalSerials()" 
                style="width: 100%; box-sizing: border-box; padding: 0.65rem 1rem 0.65rem 2.5rem; background: var(--bg-color, #0f111a); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-family: inherit; font-size: 0.875rem; outline: none;">
        </div>

        <div id="modalSerialList" style="max-height: 360px; overflow-y: auto; background: var(--bg-color, #0f111a); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem; margin-bottom: 1.25rem;">
            <!-- Dynamically populated -->
        </div>

        <div class="modal-actions" style="justify-content: flex-end;">
            <button type="button" class="btn btn-outline" onclick="closeSerialModal()" style="padding: 0.5rem 1.25rem; font-size: 0.875rem; border-radius: 6px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        let currentModalSerials = [];
        let currentModalQty = 0;

        function openSerialModal(sku, name, serials, qty) {
            document.getElementById('modalSkuCode').textContent = sku;
            document.getElementById('modalProductName').textContent = name;

            currentModalSerials = Array.isArray(serials) ? serials : [];
            currentModalQty = qty;

            const searchInput = document.getElementById('modalSerialSearch');
            if (searchInput) {
                searchInput.value = '';
            }

            const searchWrapper = document.getElementById('modalSearchWrapper');
            if (searchWrapper) {
                searchWrapper.style.display = (qty > 0 && currentModalSerials.length > 0) ? 'block' : 'none';
            }

            renderModalSerials(currentModalSerials);

            const modal = document.getElementById('serialModal');
            modal.style.display = 'flex';
        }

        function filterModalSerials() {
            const query = document.getElementById('modalSerialSearch').value.trim().toLowerCase();
            if (!query) {
                renderModalSerials(currentModalSerials);
                return;
            }
            const filtered = currentModalSerials.filter(sn => sn.toLowerCase().includes(query));
            renderModalSerials(filtered, query);
        }

        function renderModalSerials(serialsToDisplay, searchQuery = '') {
            const container = document.getElementById('modalSerialList');

            if (currentModalQty <= 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--danger, #ef4444); font-style: italic; padding: 1.5rem 0;">
                        <i class="ph ph-x-circle" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; margin-left: auto; margin-right: auto;"></i>
                        Stock Not Available
                    </div>
                `;
                return;
            }

            if (!currentModalSerials || currentModalSerials.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--text-secondary); font-style: italic; padding: 1.5rem 0;">
                        <i class="ph ph-info" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; margin-left: auto; margin-right: auto;"></i>
                        No Serial Number
                    </div>
                `;
                return;
            }

            if (serialsToDisplay.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--text-secondary); padding: 1.5rem 0;">
                        <i class="ph ph-magnifying-glass" style="font-size: 1.75rem; display: block; margin-bottom: 0.5rem; margin-left: auto; margin-right: auto; opacity: 0.7;"></i>
                        No serial numbers match "${escapeHtml(searchQuery)}"
                    </div>
                `;
                return;
            }

            let countText = searchQuery 
                ? `Showing ${serialsToDisplay.length} of ${currentModalSerials.length} Serial Numbers` 
                : `Serial Numbers (${currentModalSerials.length})`;

            let html = `<div style="margin-bottom: 0.6rem; font-size: 0.75rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">${countText}</div>`;
            html += '<div style="display: flex; flex-direction: column; gap: 0.45rem;">';
            serialsToDisplay.forEach((sn, idx) => {
                html += `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--surface-color, rgba(255, 255, 255, 0.04)); border: 1px solid var(--border-color); border-radius: 6px; font-family: monospace; font-size: 0.875rem; color: var(--text-primary);">
                        <span><strong style="color: var(--accent-primary, #6366f1); margin-right: 0.6rem; font-family: inherit; display: inline-block; min-width: 28px;">#${idx + 1}</strong> ${escapeHtml(sn)}</span>
                        <button type="button" onclick="copySerial('${escapeHtml(sn)}', this)" title="Copy Serial" style="background: transparent; border: 1px solid var(--border-color); color: var(--accent-primary, #6366f1); cursor: pointer; padding: 0.25rem 0.55rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; transition: all 0.2s;">
                            <i class="ph ph-copy"></i> Copy
                        </button>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        function closeSerialModal() {
            document.getElementById('serialModal').style.display = 'none';
        }

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function copySerial(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="ph ph-check" style="color: var(--success, #10b981);"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = orig;
                }, 1500);
            }).catch(err => {
                console.error('Failed to copy', err);
            });
        }

        window.addEventListener('click', function(e) {
            const serialModal = document.getElementById('serialModal');
            if (e.target === serialModal) {
                closeSerialModal();
            }
        });
    </script>
@endpush


