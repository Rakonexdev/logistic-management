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
                        <th style="padding: 1rem;">Delivered Items & Serials</th>
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
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    @foreach($note->items->take(2) as $item)
                                        <div style="font-size: 0.85rem;">
                                            {{ $item->sku_code }} - <span style="color: var(--text-secondary);">{{ $item->description ?? '' }}</span> (Qty: <strong>{{ $item->quantity }}</strong>)
                                            @if($item->serial_numbers)
                                                <span style="display: block; font-size: 0.75rem; color: var(--success);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if(count($note->items) > 2)
                                        <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; margin-top: 0.25rem; align-self: flex-start; display: inline-flex; align-items: center; gap: 0.25rem;"
                                                onclick="showItemsModal('{{ $note->dn_number }}', {{ htmlspecialchars(json_encode($note->items), ENT_QUOTES, 'UTF-8') }})">
                                            <i class="ph ph-eye"></i> View More (+{{ count($note->items) - 2 }})
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 1rem;">{{ $note->created_at->format('Y-m-d H:i') }}</td>
                            <td style="padding: 1rem;">
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
        <div class="glass" style="background: var(--panel-bg, #1a1b24); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); padding: 2rem; border-radius: 12px; width: 90%; max-width: 600px; position: relative; box-shadow: var(--shadow-lg);">
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

    @push('scripts')
        <script>
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
@endsection
