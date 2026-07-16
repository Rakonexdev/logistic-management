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

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-completed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-partial {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-pending {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .glass-panel {
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-truck"></i> Delivery Management
        </h1>
        <a href="{{ route('delivery-instructions.create') }}" class="btn btn-primary">
            <i class="ph ph-plus"></i> Create Delivery Instruction
        </a>
    </div>

    @if(session('success'))
        <div class="glass"
            style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <!-- Delivery Instructions Panel -->
    <div class="glass glass-panel">
        <div class="section-header">
            <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">
                <i class="ph ph-receipt"></i> Delivery Instructions
            </h2>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>DI Number</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Items Status (Delivered / Ordered)</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instructions as $di)
                        <tr>
                            <td><strong>{{ $di->di_number }}</strong></td>
                            <td>{{ $di->customer_name }}</td>
                            <td>{{ $di->delivery_address }}</td>
                            <td>
                                <span class="status-badge status-{{ $di->status }}">
                                    {{ $di->status }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    @foreach($di->items->take(2) as $item)
                                        <div style="font-size: 0.85rem;">
                                            {{ $item->sku_code }} - <span
                                                style="color: var(--text-secondary);">{{ $item->description ?? 'No Description' }}</span>
                                            (<strong>{{ $item->delivered_quantity }}</strong> / {{ $item->quantity }})
                                            @if($item->serial_numbers)
                                                <span style="display: block; font-size: 0.75rem; color: var(--text-secondary);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if(count($di->items) > 2)
                                        <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; margin-top: 0.25rem; align-self: flex-start; display: inline-flex; align-items: center; gap: 0.25rem;"
                                                onclick="showItemsModal('{{ $di->di_number }}', {{ htmlspecialchars(json_encode($di->items), ENT_QUOTES, 'UTF-8') }})">
                                            <i class="ph ph-eye"></i> View More (+{{ count($di->items) - 2 }})
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $di->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.35rem; align-items: flex-start;">
                                    @if($di->status === 'partial')
                                        <a href="{{ route('delivery-instructions.fulfill-remaining', $di->id) }}"
                                            class="btn btn-outline"
                                            style="color: var(--warning); border-color: rgba(245, 158, 11, 0.3); font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <i class="ph ph-arrow-counter-clockwise"></i> Fulfill Remaining
                                        </a>
                                    @endif

                                    @foreach($di->deliveryNotes as $dn)
                                        <a href="{{ route('delivery-notes.print', $dn->id) }}" target="_blank"
                                            class="btn btn-outline"
                                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <i class="ph ph-printer"></i> Print DN
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No Delivery
                                Instructions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $instructions->links() }}
        </div>
    </div>

    </div>

    <!-- Items Modal -->
    <div id="itemsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div class="glass" style="background: var(--panel-bg, #1a1b24); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); padding: 2rem; border-radius: 12px; width: 90%; max-width: 600px; position: relative; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.1)); padding-bottom: 0.75rem;">
                <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">Delivery Instruction Items</h3>
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

    @push('scripts')
        <script>
            function showItemsModal(diNumber, items) {
                document.getElementById('modalTitle').textContent = 'Items for ' + diNumber;
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
                            <span style="color: var(--accent-primary, #6366f1);">${item.delivered_quantity} / ${item.quantity}</span>
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

            // Close modal if clicked outside
            window.addEventListener('click', function(e) {
                const modal = document.getElementById('itemsModal');
                if (e.target === modal) {
                    closeItemsModal();
                }
            });
        </script>
    @endpush
@endsection