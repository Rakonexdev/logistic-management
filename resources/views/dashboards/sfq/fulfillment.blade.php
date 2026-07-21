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

        .form-panel {
            padding: 2rem;
            margin-bottom: 2rem;
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
            padding: 0.5rem 1rem;
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
            font-size: 0.825rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: #ffffff;
        }

        .btn-primary:hover {
            opacity: 0.9;
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

        .badge-draft { background: rgba(255, 255, 255, 0.1); color: var(--text-secondary); }
        .badge-submitted { background: rgba(59, 130, 246, 0.15); color: var(--info); }
        .badge-processing { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .badge-completed { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-shopping-cart"></i> Sales Order Fulfillment
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="form-panel glass">
        <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Delivery Notes Awaiting Fulfillment</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>DN Number</th>
                        <th>DI Number</th>
                        <th>Customer / Destination</th>
                        <th>Delivered Items & Serials</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveryNotes as $note)
                        <tr>
                            <td><strong>{{ $note->dn_number }}</strong></td>
                            <td>{{ $note->deliveryInstruction->di_number ?? 'N/A' }}</td>
                            <td>{{ $note->deliveryInstruction->customer_name ?? 'N/A' }}</td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    @foreach($note->items->take(3) as $item)
                                        <span style="font-size: 0.85rem;">
                                            {{ $item->sku_code }} (Qty: <strong>{{ $item->quantity }}</strong>)
                                            @if($item->serial_numbers)
                                                <span style="display: block; font-size: 0.75rem; color: var(--success);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </span>
                                            @endif
                                        </span>
                                    @endforeach

                                    @if(count($note->items) > 3)
                                        <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; margin-top: 0.25rem; align-self: flex-start; display: inline-flex; align-items: center; gap: 0.25rem;"
                                                onclick="showItemsModal('{{ $note->dn_number }}', {{ htmlspecialchars(json_encode($note->items), ENT_QUOTES, 'UTF-8') }})">
                                            <i class="ph ph-eye"></i> View More (+{{ count($note->items) - 3 }})
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $note->status === 'released' ? 'processing' : ($note->status === 'completed' ? 'completed' : $note->status) }}">
                                    {{ $note->status }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    @if($note->status === 'released')
                                        <form action="{{ route('sfq.fulfillment.delivery-note') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="note_id" value="{{ $note->id }}">
                                            <button type="submit" name="status" value="processing" class="btn btn-primary">
                                                <i class="ph ph-hand-pointing"></i> Accept Dispatch
                                            </button>
                                        </form>
                                    @elseif($note->status === 'processing')
                                        <form action="{{ route('sfq.fulfillment.delivery-note') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="note_id" value="{{ $note->id }}">
                                            <button type="submit" name="status" value="completed" class="btn btn-primary" style="background: var(--success); color: white;">
                                                <i class="ph ph-package"></i> Complete Dispatch
                                            </button>
                                        </form>
                                    @else
                                        <span style="font-size: 0.85rem; color: var(--text-secondary);">No action required</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary);">No released Delivery Notes awaiting fulfillment</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Items Modal -->
    <div id="itemsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div class="glass" style="background: var(--panel-bg, #1a1b24); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); padding: 2rem; border-radius: 12px; width: 90%; max-width: 600px; position: relative; box-shadow: var(--shadow-lg); margin: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.1)); padding-bottom: 0.75rem;">
                <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">Delivery Note Items</h3>
                <button type="button" onclick="closeItemsModal()" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.5rem; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px;">&times;</button>
            </div>
            <div id="modalBody" style="max-height: 400px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.75rem; padding-right: 0.25rem;">
                <!-- Items injected here -->
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; border-top: 1px solid var(--border-color, rgba(255,255,255,0.1)); padding-top: 1rem;">
                <button type="button" class="btn btn-outline" onclick="closeItemsModal()">Close</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function reportShortage(soNumber) {
            const reason = prompt(`Enter shortage / exception reason for Sales Order ${soNumber}:`, "Insufficient stock in warehouse Main-A-01");
            if (reason) {
                alert(`Shortage reported for ${soNumber}: ${reason}`);
                window.location.reload();
            }
        }

        function showItemsModal(dnNumber, items) {
            document.getElementById('modalTitle').textContent = 'Items for ' + dnNumber;
            const body = document.getElementById('modalBody');
            body.innerHTML = '';

            items.forEach(item => {
                const div = document.createElement('div');
                div.style.padding = '0.75rem';
                div.style.border = '1px solid var(--border-color, rgba(255,255,255,0.1))';
                div.style.borderRadius = '8px';
                div.style.background = 'rgba(255,255,255,0.02)';

                let serialsHtml = '';
                if (item.serial_numbers) {
                    serialsHtml = `<div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem; font-family: monospace;">
                        <strong>S/N:</strong> ${item.serial_numbers}
                    </div>`;
                }

                div.innerHTML = `
                    <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem;">
                        <span style="color: var(--text-primary);">${item.sku_code}</span>
                        <span style="color: var(--accent-primary, #6366f1);">${item.quantity}</span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">${item.description || 'No Description'}</div>
                    ${serialsHtml}
                `;
                body.appendChild(div);
            });

            const modal = document.getElementById('itemsModal');
            modal.style.display = 'flex';
        }

        function closeItemsModal() {
            document.getElementById('itemsModal').style.display = 'none';
        }

        window.addEventListener('click', function(e) {
            const modal = document.getElementById('itemsModal');
            if (e.target === modal) {
                closeItemsModal();
            }
        });
    </script>
@endpush
