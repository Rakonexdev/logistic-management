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
                        <th>Date</th>
                        <th>DI Number</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instructions as $di)
                        <tr>
                            <td>{{ $di->created_at->format('Y-m-d') }}</td>
                            <td><strong>{{ $di->di_number }}</strong></td>
                            <td>
                                <strong>{{ $di->customer_name }}</strong>
                            </td>
                            <td>{{ $di->delivery_address }}</td>
                            <td>
                                <span class="status-badge status-{{ $di->status }}">
                                    {{ $di->status }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.4rem; align-items: flex-start; min-width: 170px;">
                                    <button type="button" class="btn btn-outline" 
                                        onclick="showItemsModal('{{ e($di->di_number) }}', {{ json_encode($di->items) }})" 
                                        style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem; color: var(--accent-primary, #6366f1); border-color: rgba(99, 102, 241, 0.3);">
                                        <i class="ph ph-eye"></i> View Items
                                    </button>

                                    @if($di->status === 'partial')
                                        <a href="{{ route('delivery-instructions.fulfill-remaining', $di->id) }}"
                                            class="btn btn-outline"
                                            style="color: var(--warning); border-color: rgba(245, 158, 11, 0.3); font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.2rem;">
                                            <i class="ph ph-arrow-counter-clockwise"></i> Fulfill Remaining
                                        </a>
                                    @endif

                                     @foreach($di->deliveryNotes as $dn)
                                         <div style="display: flex; flex-direction: column; gap: 0.25rem; background: rgba(255,255,255,0.03); padding: 0.45rem; border-radius: 6px; border: 1px solid var(--border-color); width: 100%; box-sizing: border-box;">
                                             <div style="font-size: 0.75rem; font-weight: 600; display: flex; justify-content: space-between; gap: 0.5rem; align-items: center;">
                                                 <span>
                                                     {{ $dn->dn_number }}
                                                     @if($dn->version_label)
                                                         <span class="badge" style="font-size: 0.65rem; background: rgba(99, 102, 241, 0.15); color: var(--accent-primary, #6366f1); padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700; margin-left: 0.2rem;">
                                                             {{ $dn->version_label }}
                                                         </span>
                                                     @endif
                                                 </span>
                                                 <span class="status-badge status-{{ $dn->status }}" style="font-size: 0.65rem; padding: 0.1rem 0.35rem;">{{ $dn->status }}</span>
                                             </div>
                                             @if($dn->amendment_reason)
                                                 <div style="font-size: 0.7rem; color: var(--text-secondary); font-style: italic; margin-top: 0.1rem;">
                                                     Reason: {{ Str::limit($dn->amendment_reason, 40) }}
                                                 </div>
                                             @endif
                                             <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; margin-top: 0.2rem;">
                                                 @if($dn->status === 'draft')
                                                     <form action="{{ route('delivery-notes.release', $dn->id) }}" method="POST" style="margin: 0;">
                                                         @csrf
                                                         <button type="submit" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; color: var(--accent-primary, #6366f1); border-color: var(--accent-primary, #6366f1);">
                                                             <i class="ph ph-paper-plane-tilt"></i> Release to Warehouse
                                                         </button>
                                                     </form>
                                                 @endif
                                                 <a href="{{ route('delivery-notes.print', $dn->id) }}" target="_blank"
                                                     class="btn btn-outline"
                                                     style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                                     <i class="ph ph-printer"></i> Print DN
                                                 </a>
                                                 <a href="{{ route('delivery-notes.index') }}"
                                                     class="btn btn-outline"
                                                     style="font-size: 0.7rem; padding: 0.2rem 0.4rem; color: var(--accent-primary, #6366f1);"
                                                     title="View & Amend Revisions">
                                                     <i class="ph ph-clock-counter-clockwise"></i> Revisions
                                                 </a>
                                                 @if($dn->delivery_note_attachment || $di->delivery_note_attachment)
                                                     <a href="{{ route('delivery-instructions.attachment', $di->id) }}" target="_blank"
                                                         class="btn btn-outline"
                                                         style="font-size: 0.7rem; padding: 0.2rem 0.4rem; color: #10b981; border-color: rgba(16, 185, 129, 0.4);"
                                                         title="View Uploaded Delivery Note">
                                                         <i class="ph ph-file-text"></i> View
                                                     </a>
                                                 @endif
                                             </div>
                                         </div>
                                     @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No Delivery
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
        <div style="background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; padding: 2rem; border-radius: 12px; width: 90%; max-width: 600px; position: relative; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">Delivery Instruction Items</h3>
                <button type="button" onclick="closeItemsModal()" style="background: none; border: none; color: #64748b; cursor: pointer; font-size: 1.5rem; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px;">&times;</button>
            </div>
            <div id="modalBody" style="max-height: 400px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.75rem; padding-right: 0.25rem;">
                <!-- Items injected here -->
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" class="btn btn-outline" style="border: 1px solid #cbd5e1; color: #334155; background: #f8fafc;" onclick="closeItemsModal()">Close</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleDiSerials(btn) {
                const wrap = btn.closest('.serial-toggle-wrap');
                const text = wrap.querySelector('.serial-full-text');
                const btnText = btn.querySelector('.serial-btn-text');
                const icon = btn.querySelector('.serial-icon');

                if (text.style.display === 'none') {
                    text.style.display = 'block';
                    btnText.textContent = 'Hide Serial Numbers';
                    if (icon) {
                        icon.classList.remove('ph-caret-down');
                        icon.classList.add('ph-caret-up');
                    }
                } else {
                    text.style.display = 'none';
                    btnText.textContent = 'View Serial Numbers';
                    if (icon) {
                        icon.classList.remove('ph-caret-up');
                        icon.classList.add('ph-caret-down');
                    }
                }
            }

            function showItemsModal(diNumber, items) {
                document.getElementById('modalTitle').textContent = 'Items for ' + diNumber;
                const body = document.getElementById('modalBody');
                body.innerHTML = '';

                items.forEach(item => {
                    const div = document.createElement('div');
                    div.style.padding = '0.85rem 1rem';
                    div.style.border = '1px solid #e2e8f0';
                    div.style.borderRadius = '8px';
                    div.style.background = '#f8fafc';

                    let serialsHtml = '';
                    if (item.serial_numbers) {
                        serialsHtml = `<div style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem; font-family: monospace; word-break: break-all;">
                            <strong style="color: #334155;">S/N:</strong> ${item.serial_numbers}
                        </div>`;
                    }

                    div.innerHTML = `
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.25rem;">
                            <span style="color: #1e293b;">${item.sku_code}</span>
                            <span style="color: #6366f1; font-weight: 700;">${item.delivered_quantity ?? 0} / ${item.quantity}</span>
                        </div>
                        <div style="font-size: 0.85rem; color: #475569;">${item.description || 'No Description'}</div>
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