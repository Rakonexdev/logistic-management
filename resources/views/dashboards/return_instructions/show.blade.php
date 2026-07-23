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

        .grid-panels {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .grid-panels {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            padding: 2rem;
            border-radius: 12px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Workflow Timeline */
        .timeline {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            position: relative;
            padding-left: 1.75rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 10px;
            bottom: 10px;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
        }

        .timeline-dot {
            position: absolute;
            left: -1.75rem;
            top: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--border-color);
            border: 3px solid var(--bg-color, #1e1e2d);
        }

        .timeline-item.completed .timeline-dot {
            background: var(--success, #10b981);
        }

        .timeline-item.active .timeline-dot {
            background: var(--accent-primary, #6366f1);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .timeline-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .timeline-date {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.2rem;
        }

        .timeline-desc {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .badge {
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-passed { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .badge-failed { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .badge-pending { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th, .items-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .items-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-secondary);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-top: 0.2rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-file-text"></i> Return Instruction: {{ $instruction->return_ref }}
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('return-instructions.print', $instruction->id) }}" target="_blank" class="btn btn-outline">
                <i class="ph ph-printer"></i> Print Document
            </a>
            <a href="{{ route('return-instructions.index') }}" class="btn btn-outline">
                <i class="ph ph-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-panels">
        <!-- Main Info -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="glass panel">
                <div class="section-title">General Information</div>
                <div class="info-grid">
                    <div>
                        <div class="info-label">Return Reference</div>
                        <div class="info-value">{{ $instruction->return_ref }}</div>
                    </div>
                    <div>
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge badge-passed">{{ $instruction->status }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Customer / Client</div>
                        <div class="info-value">{{ $instruction->customer_name }}</div>
                    </div>
                    <div>
                        <div class="info-label">Pickup Address</div>
                        <div class="info-value">{{ $instruction->pickup_address }}</div>
                    </div>
                    <div>
                        <div class="info-label">Assigned Driver</div>
                        <div class="info-value">{{ $instruction->driver_name ?? 'Not Assigned Yet' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Designated Storage Location</div>
                        <div class="info-value">{{ $instruction->storing_location ?? 'Not Designated Yet' }}</div>
                    </div>
                </div>

                @if($instruction->remarks)
                    <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(0,0,0,0.1); border-radius: 8px;">
                        <div class="info-label">Remarks / Special Instructions</div>
                        <div style="font-size: 0.875rem; color: var(--text-primary); margin-top: 0.25rem;">{{ $instruction->remarks }}</div>
                    </div>
                @endif
            </div>

            <div class="glass panel">
                <div class="section-title">Returned Items List</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>SKU Code</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Serial Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instruction->items as $item)
                            <tr>
                                <td><strong>{{ $item->sku_code }}</strong></td>
                                <td>{{ $item->description ?: '-' }}</td>
                                <td><strong>{{ $item->quantity }}</strong></td>
                                <td>{{ $item->serial_numbers ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Shipment Evidence & Cost (If Shipped to END) -->
            @if($instruction->shipped_back_date || $instruction->tracking_number)
                <div class="glass panel">
                    <div class="section-title">
                        Return Shipment Evidence & Charges (Paid by END)
                    </div>
                    <div class="info-grid">
                        <div>
                            <div class="info-label">Shipped Back Date</div>
                            <div class="info-value">{{ $instruction->shipped_back_date ? $instruction->shipped_back_date->format('Y-m-d H:i') : '-' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Courier Name</div>
                            <div class="info-value">{{ $instruction->courier_name ?: 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Tracking Number / Evidence</div>
                            <div class="info-value">{{ $instruction->tracking_number ?: 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Shipping Charges Incurred</div>
                            <div class="info-value" style="color: var(--accent-primary);">
                                {{ $instruction->shipping_charges ? '$'.number_format($instruction->shipping_charges, 2) : 'Free / Paid' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Side: Workflow Timeline & Inspection -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Dates Timeline -->
            <div class="glass panel">
                <div class="section-title">Return Workflow Dates</div>
                <div class="timeline">
                    <!-- Date 1: Instruction Received -->
                    <div class="timeline-item {{ $instruction->instruction_received_date ? 'completed' : '' }}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-label">1. Instruction Received Date</div>
                        <div class="timeline-date">{{ $instruction->instruction_received_date ? $instruction->instruction_received_date->format('Y-m-d H:i') : 'Pending' }}</div>
                        <div class="timeline-desc">Date END issued the return instruction</div>
                    </div>

                    <!-- Date 2: Picking Date -->
                    <div class="timeline-item {{ $instruction->picking_date ? 'completed' : ($instruction->driver_name ? 'active' : '') }}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-label">2. Picking Date</div>
                        <div class="timeline-date">{{ $instruction->picking_date ? $instruction->picking_date->format('Y-m-d H:i') : 'Pending Driver Pickup' }}</div>
                        <div class="timeline-desc">Date driver picked up item from customer</div>
                    </div>

                    <!-- Date 3: Storing Date & Location -->
                    <div class="timeline-item {{ $instruction->storing_date ? 'completed' : ($instruction->picking_date ? 'active' : '') }}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-label">3. Storing Date & Location</div>
                        <div class="timeline-date">{{ $instruction->storing_date ? $instruction->storing_date->format('Y-m-d H:i') : 'Pending Warehouse Storing' }}</div>
                        <div class="timeline-desc">Stored at: <strong>{{ $instruction->storing_location ?: 'N/A' }}</strong></div>
                    </div>

                    <!-- Date 4: Shipped Back to END Date -->
                    <div class="timeline-item {{ $instruction->shipped_back_date ? 'completed' : '' }}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-label">4. Shipped Back to END Date</div>
                        <div class="timeline-date">{{ $instruction->shipped_back_date ? $instruction->shipped_back_date->format('Y-m-d H:i') : 'N/A (Stored at SFQ)' }}</div>
                        <div class="timeline-desc">Tracking: {{ $instruction->tracking_number ?: 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Quality Inspection Box -->
            <div class="glass panel">
                <div class="section-title">QC Inspection (END)</div>
                <div style="margin-bottom: 1rem;">
                    <span class="info-label">Status:</span>
                    <span class="badge badge-{{ strtolower(explode(' ', $instruction->inspection_status)[0]) }}">
                        {{ $instruction->inspection_status }}
                    </span>
                </div>

                <form action="{{ route('return-instructions.inspection', $instruction->id) }}" method="POST">
                    @csrf
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <label class="info-label">Perform Inspection Action</label>
                        <select name="inspection_status" class="form-input" style="height: auto; min-height: 40px;" required>
                            <option value="Passed" {{ $instruction->inspection_status === 'Passed' ? 'selected' : '' }}>Passed (Re-stockable)</option>
                            <option value="Failed" {{ $instruction->inspection_status === 'Failed' ? 'selected' : '' }}>Failed (Defective)</option>
                        </select>
                        <textarea name="remarks" class="form-input" rows="2" placeholder="Inspection notes / remarks..."></textarea>
                        <button type="submit" class="btn btn-primary" style="justify-content: center;">
                            <i class="ph ph-check-square"></i> Submit Inspection Result
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
