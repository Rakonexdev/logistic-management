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
                                    @foreach($note->items as $item)
                                        <span style="font-size: 0.85rem;">
                                            {{ $item->sku_code }} (Qty: <strong>{{ $item->quantity }}</strong>)
                                            @if($item->serial_numbers)
                                                <span style="display: block; font-size: 0.75rem; color: var(--success);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </span>
                                            @endif
                                        </span>
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
    </script>
@endpush
