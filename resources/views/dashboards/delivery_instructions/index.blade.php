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
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
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
                                    @foreach($di->items as $item)
                                        <div style="font-size: 0.85rem;">
                                            {{ $item->sku_code }}: <strong>{{ $item->delivered_quantity }}</strong> / {{ $item->quantity }}
                                            @if($item->serial_numbers)
                                                <span style="display: block; font-size: 0.75rem; color: var(--text-secondary);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $di->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($di->status === 'partial')
                                    <a href="{{ route('delivery-instructions.fulfill-remaining', $di->id) }}" class="btn btn-outline" style="color: var(--warning); border-color: rgba(245, 158, 11, 0.3); font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                        <i class="ph ph-arrow-counter-clockwise"></i> Fulfill Remaining
                                    </a>
                                @else
                                    <span style="font-size: 0.85rem; color: var(--text-secondary);">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No Delivery Instructions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $instructions->links() }}
        </div>
    </div>

    <!-- Delivery Notes Panel -->
    <div class="glass glass-panel">
        <div class="section-header">
            <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">
                <i class="ph ph-note"></i> Generated Delivery Notes
            </h2>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>DN Number</th>
                        <th>DI Number</th>
                        <th>Customer</th>
                        <th>Delivered Items & Serials</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notes as $note)
                        <tr>
                            <td><strong>{{ $note->dn_number }}</strong></td>
                            <td>{{ $note->deliveryInstruction->di_number ?? 'N/A' }}</td>
                            <td>{{ $note->deliveryInstruction->customer_name ?? 'N/A' }}</td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    @foreach($note->items as $item)
                                        <div style="font-size: 0.85rem;">
                                            {{ $item->sku_code }} (Qty: <strong>{{ $item->quantity }}</strong>)
                                            @if($item->serial_numbers)
                                                <span style="display: block; font-size: 0.75rem; color: var(--success);">
                                                    S/N: {{ $item->serial_numbers }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $note->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No Delivery Notes generated yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $notes->links() }}
        </div>
    </div>
@endsection
