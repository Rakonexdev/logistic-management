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
        .badge-partially-paid { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .badge-paid { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-bank"></i> Cheque Collection Invoices
        </h1>
        <a href="{{ route('cheque-collection-invoices.create') }}" class="btn btn-primary">
            <i class="ph ph-plus"></i> Create Cheque Invoice
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
                <i class="ph ph-bank"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">QAR {{ number_format($totalFeeSum, 2) }}</div>
                <div class="stat-label">Total Invoiced Amount</div>
            </div>
        </div>

        <div class="glass stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <i class="ph ph-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $unpaidCount }}</div>
                <div class="stat-label">Unpaid Invoices</div>
            </div>
        </div>

        <div class="glass stat-card">
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">
                <i class="ph ph-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $paidCount }}</div>
                <div class="stat-label">Paid Invoices</div>
            </div>
        </div>
    </div>

    <div class="glass" style="padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
        <form method="GET" action="{{ route('cheque-collection-invoices.index') }}" class="toolbar" id="filterForm">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="Search Invoice # or Customer..." oninput="debouncedSearch()">
            </div>
        </form>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer / Client</th>
                        <th>Cheques Invoiced</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Status</th>
                        <th>Date Issued</th>
                        <th style="width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php
                            $collectedCount = 0;
                            $paidAmountSum = 0;

                            foreach ($inv->items as $item) {
                                $chq = $item->chequeCollection;
                                if ($chq) {
                                    if ($chq->payments && $chq->payments->count() > 0) {
                                        $collectedCount += $chq->payments->count();
                                        $paidAmountSum += (float) $chq->payments->sum('paid_amount');
                                    } elseif (in_array($chq->status, ['Collected', 'Submitted']) && (float) $chq->paid_amount > 0) {
                                        $collectedCount += 1;
                                        $paidAmountSum += (float) $chq->paid_amount;
                                    }
                                }
                            }

                            $computedStatus = 'Unpaid';
                            if ($paidAmountSum >= (float) $inv->total_amount && (float) $inv->total_amount > 0) {
                                $computedStatus = 'Paid';
                            } elseif ($paidAmountSum > 0) {
                                $computedStatus = 'Partially Paid';
                            }
                        @endphp
                        <tr>
                            <td><strong>{{ $inv->invoice_number }}</strong></td>
                            <td><strong>{{ $inv->customer_name }}</strong></td>
                            <td>
                                <span style="font-weight: 600; color: {{ $collectedCount > 0 ? '#10b981' : 'var(--accent-primary, #6366f1)' }}; background: {{ $collectedCount > 0 ? 'rgba(16, 185, 129, 0.15)' : 'rgba(99, 102, 241, 0.1)' }}; padding: 0.2rem 0.6rem; border-radius: 6px;">
                                    <i class="ph ph-files"></i> {{ $collectedCount }} Cheque(s)
                                </span>
                            </td>
                            <td style="font-weight: 800; color: var(--accent-primary, #6366f1);">
                                QAR {{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td style="font-weight: 800; color: {{ $paidAmountSum > 0 ? '#10b981' : 'var(--text-secondary)' }};">
                                QAR {{ number_format($paidAmountSum, 2) }}
                            </td>
                            <td>
                                <span class="badge badge-{{ strtolower(str_replace(' ', '-', $computedStatus)) }}">
                                    <i class="ph {{ strtolower($computedStatus) === 'paid' ? 'ph-check-circle' : (strtolower($computedStatus) === 'partially paid' ? 'ph-clock-counter-clockwise' : 'ph-clock') }}"></i> {{ ucfirst($computedStatus) }}
                                </span>
                            </td>
                            <td>{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    @if(Auth::user()->role === 'sfq_user')
                                        <button type="button" class="btn btn-outline" onclick="openAssignDriverModal({{ $inv->id }}, '{{ $inv->invoice_number }}')" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="Assign Driver">
                                            <i class="ph ph-user-plus"></i> Assign Driver
                                        </button>
                                    @endif
                                    <a href="{{ route('cheque-collection-invoices.show', $inv->id) }}" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="View Invoice">
                                        <i class="ph ph-eye"></i> View
                                    </a>
                                    <a href="{{ route('cheque-collection-invoices.print', $inv->id) }}" target="_blank" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="Print Invoice">
                                        <i class="ph ph-printer"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline" onclick="openDeleteModal({{ $inv->id }}, '{{ $inv->invoice_number }}')" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.4);" title="Delete Invoice">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                <i class="ph ph-bank" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                No Cheque Collection Invoices created yet. Click "Create Cheque Invoice" to generate one.
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

    <!-- Custom Delete Confirmation Modal Popup -->
    <div id="deleteModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(5px); z-index: 9999; align-items: center; justify-content: center;">
        <div class="glass" style="width: 90%; max-width: 440px; padding: 2rem; border-radius: 16px; border: 1px solid var(--border-color); text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: var(--danger, #ef4444); display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem; margin-left: auto; margin-right: auto;">
                <i class="ph ph-warning"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Delete Cheque Invoice?</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.75rem; line-height: 1.5;">
                Are you sure you want to delete cheque collection invoice <strong id="deleteInvoiceNumberText" style="color: var(--text-primary);"></strong>? This action cannot be undone.
            </p>

            <form id="deleteInvoiceForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div style="display: flex; gap: 0.75rem; justify-content: center;">
                    <button type="button" class="btn btn-outline" onclick="closeDeleteModal()" style="flex: 1; padding: 0.65rem 1rem;">
                        Cancel
                    </button>
                    <button type="submit" class="btn" style="flex: 1; padding: 0.65rem 1rem; background: var(--danger, #ef4444); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="ph ph-trash"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Driver Modal Popup -->
    <div id="assignDriverModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(5px); z-index: 9999; align-items: center; justify-content: center;">
        <div class="glass" style="width: 90%; max-width: 440px; padding: 2rem; border-radius: 16px; border: 1px solid var(--border-color); text-align: left; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);"><i class="ph ph-user-plus"></i> Assign Driver</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.5;">
                Select driver to collect payment/cheque for invoice <strong id="assignInvoiceNumberText" style="color: var(--text-primary);"></strong>:
            </p>

            <form id="assignDriverForm" method="POST" action="">
                @csrf
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 0.5rem; display: block; font-size: 0.875rem; color: var(--text-secondary);">Select Delivery Driver *</label>
                    <select name="driver" id="modalDriverSelect" class="form-select" required style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-primary);">
                        <option value="">Select Driver</option>
                        @foreach($drivers as $drv)
                            <option value="{{ $drv->name }}">{{ $drv->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeAssignDriverModal()" style="padding: 0.65rem 1rem;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1rem;">
                        Save Assignment
                    </button>
                </div>
            </form>
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

            function openAssignDriverModal(invoiceId, invoiceNumber) {
                const modal = document.getElementById('assignDriverModal');
                const form = document.getElementById('assignDriverForm');
                const invoiceText = document.getElementById('assignInvoiceNumberText');

                form.action = `{{ url('cheque-collection-invoices') }}/${invoiceId}/assign-driver`;
                invoiceText.textContent = invoiceNumber;
                modal.style.display = 'flex';
            }

            function closeAssignDriverModal() {
                const modal = document.getElementById('assignDriverModal');
                modal.style.display = 'none';
            }

            function openDeleteModal(invoiceId, invoiceNumber) {
                const modal = document.getElementById('deleteModal');
                const form = document.getElementById('deleteInvoiceForm');
                const invoiceText = document.getElementById('deleteInvoiceNumberText');

                form.action = `{{ url('cheque-collection-invoices') }}/${invoiceId}`;
                invoiceText.textContent = invoiceNumber;
                modal.style.display = 'flex';
            }

            function closeDeleteModal() {
                const modal = document.getElementById('deleteModal');
                modal.style.display = 'none';
            }

            window.addEventListener('click', function(e) {
                const deleteModal = document.getElementById('deleteModal');
                const assignModal = document.getElementById('assignDriverModal');
                if (e.target === deleteModal) {
                    closeDeleteModal();
                }
                if (e.target === assignModal) {
                    closeAssignDriverModal();
                }
            });
        </script>
    @endpush
@endsection
