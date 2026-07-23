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

        .verify-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .verify-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #475569;
            padding: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
            background: #f8fafc;
        }
        .verify-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            color: #0f172a;
        }

        .sn-box-item {
            border: 1px solid #ef4444;
            background: #fef2f2;
            border-radius: 6px;
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            font-family: monospace;
            color: #991b1b;
            display: inline-block;
            margin-top: 0.2rem;
            font-weight: 600;
        }
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
                        <th>PRODUCT / SERIAL NUMBER</th>
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
                                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                    @foreach($note->items as $item)
                                        <div class="serial-toggle-wrap" style="font-size: 0.85rem;">
                                            @if($item->serial_numbers)
                                                <button type="button" class="serial-toggle-btn" onclick="toggleFulfillmentSerials(this)" style="background: none; border: none; padding: 0; color: var(--text-primary); font-size: 0.85rem; font-family: inherit; cursor: pointer; text-align: left; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                    <strong>{{ $item->sku_code }}</strong>
                                                    <i class="ph ph-caret-down serial-icon" style="font-size: 0.75rem; color: var(--accent-primary, #6366f1);"></i>
                                                </button>
                                                <div class="serial-full-text" style="display: none; font-size: 0.75rem; color: var(--text-secondary); font-family: monospace; margin-top: 0.2rem; word-break: break-all; padding-left: 0.5rem; border-left: 2px solid var(--accent-primary, #6366f1);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </div>
                                            @else
                                                <div>
                                                    <strong>{{ $item->sku_code }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
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
                                        <button type="button" class="btn btn-primary" onclick="openVerifyModal('{{ $note->id }}')" style="background: var(--accent-primary, #6366f1); color: white;">
                                            <i class="ph ph-check-square-offset"></i> Verify & Dispatch
                                        </button>
                                    @else
                                        <span style="font-size: 0.85rem; color: var(--text-secondary);">Completed</span>
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

    <!-- Verification Modal (White Theme) -->
    <div id="verifyDispatchModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.65); align-items: center; justify-content: center;">
        <div style="background: #ffffff; color: #0f172a; border: 1px solid #e2e8f0; padding: 2rem; border-radius: 12px; width: 95%; max-width: 1200px; position: relative; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); margin: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 id="verifyModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Verify Received Quantities</h3>
                <button type="button" onclick="closeVerifyModal()" style="background: none; border: none; color: #64748b; cursor: pointer; font-size: 1.5rem; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px;">&times;</button>
            </div>
            
            <form action="{{ route('sfq.fulfillment.delivery-note') }}" method="POST" id="verifyDispatchForm">
                @csrf
                <input type="hidden" name="note_id" id="modalNoteId" value="">
                <input type="hidden" name="status" value="completed">

                <div style="max-height: 480px; overflow-y: auto; margin-bottom: 1.5rem;">
                    <table class="verify-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">VERIFY</th>
                                <th style="width: 200px;">PRODUCT / SKU</th>
                                <th style="width: 250px;">SERIAL NUMBERS</th>
                                <th style="width: 90px; text-align: center;">EXPECTED QTY</th>
                                <th style="width: 90px; text-align: center;">RECEIVED QTY</th>
                                <th style="width: 100px; text-align: center;">DISCREPANCY QTY</th>
                                <th style="width: 150px;">DISCREPANCY REASON</th>
                            </tr>
                        </thead>
                        <tbody id="verifyModalBody">
                            <!-- Items injected via JS -->
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <button type="button" class="btn" onclick="closeVerifyModal()" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 600;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; font-weight: 700; padding: 0.6rem 1.25rem;">
                        <i class="ph ph-package"></i> Confirm Verification & Complete Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const deliveryNotesMap = {
            @foreach($deliveryNotes as $note)
                "{{ $note->id }}": {
                    "dn_number": @json($note->dn_number),
                    "items": @json($note->items)
                },
            @endforeach
        };

        function toggleFulfillmentSerials(btn) {
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

        function openVerifyModal(noteId) {
            const noteData = deliveryNotesMap[noteId];
            if (!noteData) {
                alert('Unable to load items for this Delivery Note.');
                return;
            }

            document.getElementById('modalNoteId').value = noteId;
            document.getElementById('verifyModalTitle').textContent = 'Verify Received Quantities (' + noteData.dn_number + ')';

            const tbody = document.getElementById('verifyModalBody');
            tbody.innerHTML = '';

            noteData.items.forEach((item, itemIdx) => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #e2e8f0';

                const serials = item.serial_numbers ? item.serial_numbers.split(',').map(s => s.trim()).filter(Boolean) : [];

                let serialsHtml = '';
                if (serials.length > 0) {
                    serialsHtml = serials.map((sn, sIdx) => `
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-family: monospace; font-size: 0.85rem; margin-bottom: 0.35rem; cursor: pointer; color: #1e293b; white-space: nowrap;">
                            <input type="checkbox" class="sn-cb-${itemIdx} form-checkbox" value="${sn}" onchange="onVerifySerialChange(${itemIdx}, ${item.quantity})" style="cursor: pointer; width: 16px; height: 16px; accent-color: #3b82f6; flex-shrink: 0;">
                            <span style="white-space: nowrap;">${sn}</span>
                        </label>
                    `).join('');

                    serialsHtml += `<div id="sn-summary-box-${itemIdx}" style="display: none; margin-top: 0.35rem; gap: 0.3rem; flex-wrap: wrap;"></div>`;
                } else {
                    serialsHtml = '<span style="font-size: 0.8rem; color: #64748b; font-style: italic;">No serial numbers required</span>';
                }

                tr.innerHTML = `
                    <td style="padding: 0.85rem;">
                        <input type="checkbox" id="main-cb-${itemIdx}" onchange="toggleMainVerify(${itemIdx}, ${item.quantity})" style="cursor: pointer; width: 18px; height: 18px; accent-color: #3b82f6;">
                    </td>
                    <td style="padding: 0.85rem;">
                        <strong style="color: #0f172a; font-size: 0.95rem;">${item.sku_code}</strong>
                        ${item.description ? `<div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">${item.description}</div>` : ''}
                    </td>
                    <td style="padding: 0.85rem;">
                        <div style="display: flex; flex-direction: column;">
                            ${serialsHtml}
                        </div>
                    </td>
                    <td style="font-weight: 700; text-align: center; color: #0f172a; padding: 0.85rem;">${item.quantity}</td>
                    <td style="padding: 0.85rem;">
                        <input type="number" id="verified-qty-${itemIdx}" name="verified_qty[${item.sku_code}]" value="0" readonly style="width: 75px; text-align: center; font-weight: 700; background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; border-radius: 6px; padding: 0.4rem;">
                    </td>
                    <td style="padding: 0.85rem;">
                        <input type="number" id="disc-qty-${itemIdx}" name="discrepancy_qty[${item.sku_code}]" value="0" readonly style="width: 75px; text-align: center; font-weight: 700; background: #f8fafc; border: 1px solid #cbd5e1; color: #d97706; border-radius: 6px; padding: 0.4rem;">
                    </td>
                    <td style="padding: 0.85rem;">
                        <select id="reason-select-${itemIdx}" name="discrepancy_reason[${item.sku_code}]" style="font-size: 0.85rem; padding: 0.4rem 0.6rem; background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; border-radius: 6px; width: 100%;">
                            <option value="None">None</option>
                            <option value="Shortage">Shortage</option>
                            <option value="Damaged Item">Damaged Item</option>
                            <option value="Missing Serial Number">Missing Serial Number</option>
                            <option value="Wrong SKU">Wrong SKU</option>
                            <option value="Other">Other</option>
                        </select>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('verifyDispatchModal').style.display = 'flex';
        }

        function closeVerifyModal() {
            document.getElementById('verifyDispatchModal').style.display = 'none';
        }

        function onVerifySerialChange(itemIdx, expectedQty) {
            const checkboxes = document.querySelectorAll(`.sn-cb-${itemIdx}`);
            const mainCb = document.getElementById(`main-cb-${itemIdx}`);
            const verifiedQtyInput = document.getElementById(`verified-qty-${itemIdx}`);
            const discQtyInput = document.getElementById(`disc-qty-${itemIdx}`);
            const reasonSelect = document.getElementById(`reason-select-${itemIdx}`);
            const summaryBox = document.getElementById(`sn-summary-box-${itemIdx}`);

            const checkedSerials = [];
            const uncheckedSerials = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    checkedSerials.push(cb.value);
                } else {
                    uncheckedSerials.push(cb.value);
                }
            });

            let checkedCount = checkedSerials.length;
            if (checkboxes.length === 0) {
                checkedCount = mainCb.checked ? expectedQty : 0;
            }

            if (verifiedQtyInput) verifiedQtyInput.value = checkedCount;

            const disc = checkedCount - expectedQty;
            if (discQtyInput) discQtyInput.value = disc;

            if (reasonSelect) {
                if (disc < 0) {
                    reasonSelect.value = 'Shortage';
                } else {
                    reasonSelect.value = 'None';
                }
            }

            if (summaryBox) {
                if (uncheckedSerials.length > 0 && checkedSerials.length > 0) {
                    summaryBox.style.display = 'flex';
                    summaryBox.innerHTML = uncheckedSerials.map(s => `<span class="sn-box-item" style="border: 1px solid #ef4444; background: #fef2f2; color: #991b1b;">Missing: ${s}</span>`).join('');
                } else {
                    summaryBox.style.display = 'none';
                }
            }

            if (mainCb && checkboxes.length > 0) {
                mainCb.checked = (checkedCount === checkboxes.length);
            }
        }

        function toggleMainVerify(itemIdx, expectedQty) {
            const mainCb = document.getElementById(`main-cb-${itemIdx}`);
            const checkboxes = document.querySelectorAll(`.sn-cb-${itemIdx}`);

            checkboxes.forEach(cb => {
                cb.checked = mainCb.checked;
            });

            onVerifySerialChange(itemIdx, expectedQty);
        }

        window.addEventListener('click', function(e) {
            const modal = document.getElementById('verifyDispatchModal');
            if (e.target === modal) {
                closeVerifyModal();
            }
        });
    </script>
@endpush
