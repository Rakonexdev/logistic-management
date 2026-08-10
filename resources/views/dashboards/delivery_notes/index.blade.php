@extends('layouts.dashboard')

@section('content')
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-note"></i> Delivery Notes
        </h1>

        <div>
            @if(request('show_all'))
                <a href="{{ route('delivery-notes.index') }}" class="btn btn-outline" style="font-size: 0.85rem;">
                    <i class="ph ph-funnel"></i> Showing All Versions (Click for Active Only)
                </a>
            @else
                <a href="{{ route('delivery-notes.index', ['show_all' => 1]) }}" class="btn btn-outline" style="font-size: 0.85rem;">
                    <i class="ph ph-clock-counter-clockwise"></i> Show All Revisions & History
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
             {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--danger); background: rgba(239, 68, 68, 0.1); color: var(--danger);">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass glass-panel" style="margin-bottom: 2rem; padding: 1.5rem;">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">
                <i class="ph ph-list-bullets"></i> Generated Delivery Notes
            </h2>
        </div>

        <div class="table-container" style="overflow-x: auto; margin-bottom: 1.5rem;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary);">
                        <th style="padding: 1rem;">DN Number</th>
                        <th style="padding: 1rem;">DI Number</th>
                        <th style="padding: 1rem;">Customer</th>
                        <th style="padding: 1rem;">PRODUCT / SERIAL NUMBER</th>
                        <th style="padding: 1rem;">Created At</th>
                        <th style="padding: 1rem;">Status</th>
                        <th style="padding: 1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notes as $note)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <strong>{{ $note->dn_number }}</strong>
                                @if($note->version_label)
                                    <span class="badge" style="font-size: 0.7rem; background: rgba(99, 102, 241, 0.15); color: var(--accent-primary, #6366f1); padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 700; margin-left: 0.3rem;">
                                        {{ $note->version_label }}
                                    </span>
                                @endif
                                @if($note->parent_dn_id)
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">
                                        Revised from {{ $note->parentNote->dn_number ?? 'Previous Version' }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 1rem;">{{ $note->deliveryInstruction->di_number ?? 'N/A' }}</td>
                            <td style="padding: 1rem;">{{ $note->deliveryInstruction->customer_name ?? 'N/A' }}</td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                    @foreach($note->items as $item)
                                        <div class="serial-toggle-wrap" style="font-size: 0.85rem;">
                                            @if($item->serial_numbers)
                                                <button type="button" class="serial-toggle-btn" onclick="toggleDnSerials(this)" style="background: none; border: none; padding: 0; color: var(--text-primary); font-size: 0.85rem; font-family: inherit; cursor: pointer; text-align: left; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                    <strong>{{ $item->sku_code }}</strong> (Qty: {{ $item->quantity }})
                                                    <i class="ph ph-caret-down serial-icon" style="font-size: 0.75rem; color: var(--accent-primary, #6366f1);"></i>
                                                </button>
                                                <div class="serial-full-text" style="display: none; font-size: 0.75rem; color: var(--text-secondary); font-family: monospace; margin-top: 0.2rem; word-break: break-all; padding-left: 0.5rem; border-left: 2px solid var(--accent-primary, #6366f1);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </div>
                                            @else
                                                <div>
                                                    <strong>{{ $item->sku_code }}</strong> (Qty: {{ $item->quantity }})
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="padding: 1rem;">{{ $note->created_at->format('Y-m-d H:i') }}</td>
                            <td style="padding: 1rem;">
                                @if($note->status === 'draft')
                                    <span class="badge" style="font-size: 0.75rem; background: rgba(107, 114, 128, 0.15); color: #9ca3af; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600;">Draft</span>
                                @elseif($note->status === 'canceled')
                                    <span class="badge" style="font-size: 0.75rem; background: rgba(239, 68, 68, 0.15); color: var(--danger, #ef4444); padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600;">Canceled</span>
                                @elseif($note->status === 'amended')
                                    <span class="badge" style="font-size: 0.75rem; background: rgba(245, 158, 11, 0.15); color: var(--warning, #f59e0b); padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600;">Amended</span>
                                @else
                                    <span class="badge badge-success" style="font-size: 0.75rem; background: rgba(16, 185, 129, 0.15); color: var(--success, #10b981); padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600; text-transform: capitalize;">{{ $note->status }}</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center;">
                                    @if($note->status === 'draft')
                                        <form action="{{ route('delivery-notes.release', $note->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem; background: var(--accent-primary, #6366f1); border: none; color: white;">
                                                <i class="ph ph-paper-plane-tilt"></i> Release
                                            </button>
                                        </form>
                                    @endif

                                    @if($note->status !== 'canceled')
                                        <button type="button" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;" onclick='openAmendModal({{ json_encode($note) }}, {{ json_encode($note->items) }})' title="Amend Delivery Note">
                                            <i class="ph ph-pencil-simple"></i> Amend
                                        </button>

                                        <button type="button" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem; color: var(--danger, #ef4444); border-color: rgba(239, 68, 68, 0.3);" onclick="openCancelModal({{ $note->id }}, '{{ $note->dn_number }}')" title="Cancel Delivery Note">
                                            <i class="ph ph-x-circle"></i> Cancel
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;" onclick="openHistoryModal({{ $note->id }})" title="View Revision History">
                                        <i class="ph ph-clock-counter-clockwise"></i> History
                                    </button>

                                    @if($note->status !== 'canceled')
                                        <a href="{{ route('delivery-notes.print', $note->id) }}" target="_blank" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <i class="ph ph-printer"></i> Print
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No Delivery Notes found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $notes->links() }}
        </div>
    </div>

    <!-- Amend Modal -->
    <div id="amendModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div class="glass" style="background: var(--bg-color, #1e1e2d); color: var(--text-primary); border: 1px solid var(--border-color); padding: 2rem; border-radius: 12px; width: 90%; max-width: 750px; position: relative; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <h3 id="amendModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-pencil-simple"></i> Amend Delivery Note
                </h3>
                <button type="button" onclick="closeAmendModal()" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.5rem;">&times;</button>
            </div>

            <form id="amendForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 1.25rem; max-height: 500px; overflow-y: auto; padding-right: 0.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.4rem;">
                            Reason for Amendment *
                        </label>
                        <textarea name="amendment_reason" class="form-input" rows="2" required placeholder="e.g. Corrected quantity discrepancy, updated serial numbers..." style="width: 100%; box-sizing: border-box; padding: 0.6rem 0.8rem; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary);"></textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.4rem;">
                            Upload Revised Document / Attachment
                        </label>
                        <input type="file" name="delivery_note_attachment" class="form-input" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" style="width: 100%; box-sizing: border-box; padding: 0.5rem; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary);">
                    </div>

                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Item Quantities & Serial Numbers</h4>
                        <div id="amendItemsContainer" style="display: flex; flex-direction: column; gap: 0.75rem;"></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <button type="button" class="btn btn-outline" onclick="closeAmendModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, var(--accent-primary, #6366f1), var(--accent-secondary, #8b5cf6)); border: none; color: white; padding: 0.6rem 1.25rem;">
                        <i class="ph ph-check-circle"></i> Save Amendment (v1/v2)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div class="glass" style="background: var(--bg-color, #1e1e2d); color: var(--text-primary); border: 1px solid var(--border-color); padding: 2rem; border-radius: 12px; width: 90%; max-width: 500px; position: relative; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <h3 id="cancelModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--danger, #ef4444); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-warning-octagon"></i> Cancel Delivery Note
                </h3>
                <button type="button" onclick="closeCancelModal()" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.5rem;">&times;</button>
            </div>

            <form id="cancelForm" method="POST">
                @csrf
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    Are you sure you want to cancel <strong id="cancelDnNumberText" style="color: var(--text-primary);"></strong>? Canceled notes will be archived and retained for audit purposes.
                </p>

                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.4rem;">
                        Cancellation Reason *
                    </label>
                    <textarea name="cancellation_reason" class="form-input" rows="3" required placeholder="e.g. Erroneous delivery instruction, order canceled by customer..." style="width: 100%; box-sizing: border-box; padding: 0.6rem 0.8rem; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary);"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <button type="button" class="btn btn-outline" onclick="closeCancelModal()">Close</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--danger, #ef4444); border: none; color: white; padding: 0.6rem 1.25rem;">
                        <i class="ph ph-x-circle"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- History Timeline Modal -->
    <div id="historyModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div class="glass" style="background: var(--bg-color, #1e1e2d); color: var(--text-primary); border: 1px solid var(--border-color); padding: 2rem; border-radius: 12px; width: 90%; max-width: 650px; position: relative; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <h3 id="historyModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-clock-counter-clockwise"></i> Revision History
                </h3>
                <button type="button" onclick="closeHistoryModal()" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.5rem;">&times;</button>
            </div>

            <div id="historyModalBody" style="max-height: 450px; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; padding-right: 0.25rem;">
                <div style="text-align: center; color: var(--text-secondary); padding: 2rem;">Loading history...</div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <button type="button" class="btn btn-outline" onclick="closeHistoryModal()">Close</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleDnSerials(btn) {
                const wrap = btn.closest('.serial-toggle-wrap');
                const text = wrap.querySelector('.serial-full-text');
                const icon = btn.querySelector('.serial-icon');

                if (text.style.display === 'none') {
                    text.style.display = 'block';
                    if (icon) {
                        icon.classList.remove('ph-caret-down');
                        icon.classList.add('ph-caret-up');
                    }
                } else {
                    text.style.display = 'none';
                    if (icon) {
                        icon.classList.remove('ph-caret-up');
                        icon.classList.add('ph-caret-down');
                    }
                }
            }

            function openAmendModal(note, items) {
                document.getElementById('amendModalTitle').innerHTML = `<i class="ph ph-pencil-simple"></i> Amend ${note.dn_number}`;
                const form = document.getElementById('amendForm');
                form.action = `/delivery-notes/${note.id}/amend`;

                const itemsContainer = document.getElementById('amendItemsContainer');
                itemsContainer.innerHTML = '';

                items.forEach((item, idx) => {
                    const row = document.createElement('div');
                    row.style.padding = '0.75rem';
                    row.style.border = '1px solid var(--border-color)';
                    row.style.borderRadius = '8px';
                    row.style.background = 'rgba(255,255,255,0.02)';
                    row.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong style="color: var(--text-primary); font-size: 0.9rem;">${item.sku_code}</strong>
                            <input type="hidden" name="items[${idx}][sku_code]" value="${item.sku_code}">
                            <input type="hidden" name="items[${idx}][description]" value="${item.description || ''}">
                        </div>
                        <div style="display: grid; grid-template-columns: 100px 1fr; gap: 0.75rem;">
                            <div>
                                <label style="font-size: 0.75rem; color: var(--text-secondary);">Qty</label>
                                <input type="number" name="items[${idx}][quantity]" value="${item.quantity}" min="1" required class="form-input" style="width: 100%; padding: 0.4rem; box-sizing: border-box; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 4px; color: var(--text-primary);">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: var(--text-secondary);">Serial Numbers</label>
                                <input type="text" name="items[${idx}][serial_numbers]" value="${item.serial_numbers || ''}" placeholder="e.g. SN1, SN2" class="form-input" style="width: 100%; padding: 0.4rem; box-sizing: border-box; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 4px; color: var(--text-primary);">
                            </div>
                        </div>
                    `;
                    itemsContainer.appendChild(row);
                });

                document.getElementById('amendModal').style.display = 'flex';
            }

            function closeAmendModal() {
                document.getElementById('amendModal').style.display = 'none';
            }

            function openCancelModal(id, dnNumber) {
                document.getElementById('cancelDnNumberText').textContent = dnNumber;
                const form = document.getElementById('cancelForm');
                form.action = `/delivery-notes/${id}/cancel`;
                document.getElementById('cancelModal').style.display = 'flex';
            }

            function closeCancelModal() {
                document.getElementById('cancelModal').style.display = 'none';
            }

            function openHistoryModal(id) {
                const body = document.getElementById('historyModalBody');
                body.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 2rem;">Loading history...</div>';
                document.getElementById('historyModal').style.display = 'flex';

                fetch(`/delivery-notes/${id}/history`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('historyModalTitle').innerHTML = `<i class="ph ph-clock-counter-clockwise"></i> Revision History for ${data.dn_number}`;
                        body.innerHTML = '';

                        if (!data.history || data.history.length === 0) {
                            body.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 2rem;">No history found.</div>';
                            return;
                        }

                        data.history.forEach((h, idx) => {
                            const itemDiv = document.createElement('div');
                            itemDiv.style.padding = '1rem';
                            itemDiv.style.border = '1px solid var(--border-color)';
                            itemDiv.style.borderRadius = '8px';
                            itemDiv.style.background = h.is_latest ? 'rgba(99, 102, 241, 0.05)' : 'rgba(255,255,255,0.02)';
                            itemDiv.style.position = 'relative';

                            let statusBadge = `<span style="font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600; background: rgba(16, 185, 129, 0.15); color: var(--success, #10b981);">${h.status}</span>`;
                            if (h.status === 'Canceled') {
                                statusBadge = `<span style="font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600; background: rgba(239, 68, 68, 0.15); color: var(--danger, #ef4444);">${h.status}</span>`;
                            } else if (h.status === 'Amended') {
                                statusBadge = `<span style="font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600; background: rgba(245, 158, 11, 0.15); color: var(--warning, #f59e0b);">${h.status}</span>`;
                            }

                            let itemsHtml = h.items.map(i => `<div><strong>${i.sku_code}</strong> (Qty: ${i.quantity}) ${i.serial_numbers ? `<span style="font-family: monospace; font-size: 0.8rem; color: var(--text-secondary);">(S/N: ${i.serial_numbers})</span>` : ''}</div>`).join('');

                            let reasonHtml = h.amendment_reason ? `<div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.5rem; font-style: italic; background: rgba(0,0,0,0.1); padding: 0.4rem 0.6rem; border-radius: 4px;">"${h.amendment_reason}"</div>` : '';

                            let attachmentHtml = h.attachment_url ? `<div style="margin-top: 0.5rem;"><a href="${h.attachment_url}" target="_blank" style="color: var(--accent-primary, #6366f1); font-size: 0.8rem; text-decoration: underline;"><i class="ph ph-file-pdf"></i> View Attachment</a></div>` : '';

                            itemDiv.innerHTML = `
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <div>
                                        <strong style="font-size: 1rem; color: var(--text-primary);">${h.dn_number}</strong>
                                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--accent-primary, #6366f1); margin-left: 0.3rem;">${h.version_label}</span>
                                        ${h.is_latest ? '<span style="font-size: 0.7rem; background: var(--accent-primary, #6366f1); color: white; padding: 0.1rem 0.35rem; border-radius: 4px; margin-left: 0.3rem;">ACTIVE</span>' : ''}
                                    </div>
                                    <div>${statusBadge}</div>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    Created: ${h.created_at} by ${h.author}
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.25rem; font-size: 0.85rem;">
                                    ${itemsHtml}
                                </div>
                                ${reasonHtml}
                                ${attachmentHtml}
                            `;
                            body.appendChild(itemDiv);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        body.innerHTML = '<div style="text-align: center; color: var(--danger, #ef4444); padding: 2rem;">Failed to load history.</div>';
                    });
            }

            function closeHistoryModal() {
                document.getElementById('historyModal').style.display = 'none';
            }

            window.addEventListener('click', function(e) {
                if (e.target === document.getElementById('amendModal')) closeAmendModal();
                if (e.target === document.getElementById('cancelModal')) closeCancelModal();
                if (e.target === document.getElementById('historyModal')) closeHistoryModal();
            });
        </script>
    @endpush
@endsection
