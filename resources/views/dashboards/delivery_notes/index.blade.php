@extends('layouts.dashboard')

@section('content')
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-note"></i> Delivery Notes
        </h1>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
             {{ session('success') }}
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
                        <th style="padding: 1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notes as $note)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;"><strong>{{ $note->dn_number }}</strong></td>
                            <td style="padding: 1rem;">{{ $note->deliveryInstruction->di_number ?? 'N/A' }}</td>
                            <td style="padding: 1rem;">{{ $note->deliveryInstruction->customer_name ?? 'N/A' }}</td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                    @foreach($note->items as $item)
                                        <div class="serial-toggle-wrap" style="font-size: 0.85rem;">
                                            @if($item->serial_numbers)
                                                <button type="button" class="serial-toggle-btn" onclick="toggleDnSerials(this)" style="background: none; border: none; padding: 0; color: var(--text-primary); font-size: 0.85rem; font-family: inherit; cursor: pointer; text-align: left; display: inline-flex; align-items: center; gap: 0.3rem;">
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
                            <td style="padding: 1rem;">{{ $note->created_at->format('Y-m-d H:i') }}</td>
                            <td style="padding: 1rem; display: flex; gap: 0.5rem; align-items: center;">
                                @if($note->status === 'draft')
                                    <form action="{{ route('delivery-notes.release', $note->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem; background: var(--accent-primary, #6366f1); border: none; color: white;">
                                            <i class="ph ph-paper-plane-tilt"></i> Release Note
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-success" style="font-size: 0.75rem; background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600; text-transform: capitalize;">{{ $note->status }}</span>
                                @endif
                                <a href="{{ route('delivery-notes.print', $note->id) }}" target="_blank" class="btn btn-outline"
                                    style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="ph ph-printer"></i> Get Delivery Notes
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No Delivery Notes generated yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $notes->links() }}
        </div>
    </div>

    <!-- Items Modal -->
    <div id="itemsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div style="background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; padding: 2rem; border-radius: 12px; width: 90%; max-width: 600px; position: relative; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">Delivery Note Items</h3>
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

            function showItemsModal(dnNumber, items) {
                document.getElementById('modalTitle').textContent = 'Items for ' + dnNumber;
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
                            <span style="color: #6366f1; font-weight: 700;">${item.quantity}</span>
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

            window.addEventListener('click', function(e) {
                const modal = document.getElementById('itemsModal');
                if (e.target === modal) {
                    closeItemsModal();
                }
            });
        </script>
    @endpush
@endsection
