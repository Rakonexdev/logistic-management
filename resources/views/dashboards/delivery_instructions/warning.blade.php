@extends('layouts.dashboard')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .warning-card {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }

        .warning-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--danger);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .section-panel {
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .item-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
        }

        .item-card-danger {
            border-color: rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.02);
        }

        .item-card-success {
            border-color: rgba(16, 185, 129, 0.3);
            background: rgba(16, 185, 129, 0.02);
        }

        .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-warning-octagon" style="color: var(--warning);"></i> Stock & Serial Validation Alert
        </h1>
    </div>

    <!-- Warning Summary -->
    <div class="glass warning-card">
        <div class="warning-title">
            <i class="ph ph-warning-octagon"></i> Mismatched or Unavailable Items Detected
        </div>
        <p>Some of the items requested in the Delivery Instruction are either out of stock, exceed available quantities, or have invalid serial numbers.</p>
    </div>

    <!-- Mismatched Items -->
    <div class="glass section-panel">
        <div class="section-title" style="color: var(--danger);">Unavailable or Mismatched Items</div>
        <div class="item-list">
            @foreach($mismatches as $mis)
                <div class="item-card item-card-danger">
                    <div style="display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 0.5rem;">
                        <span style="color: var(--text-primary);">SKU: {{ $mis['sku_code'] }}</span>
                        @if(!empty($mis['description']))
                            <span style="color: var(--text-secondary); font-weight: normal; font-size: 0.875rem;">Description: {{ $mis['description'] }}</span>
                        @endif
                        <span style="color: var(--danger);">Requested Qty: {{ $mis['quantity'] }}</span>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                        <strong>Validation Error:</strong> <span style="color: var(--danger);">{{ $mis['reason'] }}</span>
                    </div>
                    <div style="font-size: 0.85rem; display: flex; gap: 1.5rem;">
                        <span>Available Stock: <strong>{{ $mis['available_qty'] }}</strong></span>
                        @if(!empty($mis['available_serials']))
                            <span>Valid Serial Number(s): <strong>{{ implode(', ', $mis['available_serials']) }}</strong></span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Available Items -->
    <div class="glass section-panel">
        <div class="section-title" style="color: var(--success);">Available Items (Can Be Delivered Immediately)</div>
        <div class="item-list">
            @forelse($available_items as $avail)
                <div class="item-card item-card-success">
                    <div style="display: flex; justify-content: space-between; font-weight: 600;">
                        <span style="color: var(--text-primary);">SKU: {{ $avail['sku_code'] }}</span>
                        @if(!empty($avail['description']))
                            <span style="color: var(--text-secondary); font-weight: normal; font-size: 0.875rem;">Description: {{ $avail['description'] }}</span>
                        @endif
                        <span style="color: var(--success);">Delivered Qty: {{ $avail['quantity'] }}</span>
                    </div>
                    @if(!empty($avail['serial_numbers']))
                        <div style="font-size: 0.85rem; margin-top: 0.5rem; color: var(--text-secondary);">
                            S/N: {{ implode(', ', $avail['serial_numbers']) }}
                        </div>
                    @endif
                </div>
            @empty
                <div style="text-align: center; color: var(--text-secondary); padding: 1rem;">No items are currently available for immediate delivery.</div>
            @endforelse
        </div>
    </div>

    <!-- Action Choices -->
    <div class="btn-group">
        <!-- Option 1: Go back and edit -->
        <button type="button" class="btn btn-outline" onclick="window.history.back()">
            <i class="ph ph-arrow-left"></i> Go Back & Edit Instruction
        </button>

        <!-- Option 2: Create Partial Delivery for available items only -->
        @if(count($available_items) > 0)
            <form action="{{ route('delivery-instructions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="di_number" value="{{ $di_number }}">
                <input type="hidden" name="customer_name" value="{{ $customer_name }}">
                <input type="hidden" name="delivery_address" value="{{ $delivery_address }}">
                <input type="hidden" name="confirm_partial" value="1">
                
                @foreach($original_items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][sku_code]" value="{{ $item['sku_code'] }}">
                    <input type="hidden" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}">
                    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}">
                    <input type="hidden" name="items[{{ $index }}][serial_numbers]" value="{{ $item['serial_numbers'] ?? '' }}">
                @endforeach

                <button type="submit" class="btn btn-primary" style="background: var(--success);">
                    <i class="ph ph-check-circle"></i> Create Partial Delivery (Generate Delivery Note)
                </button>
            </form>
        @endif
    </div>
@endsection
