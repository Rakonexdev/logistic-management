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

        .form-panel {
            padding: 2rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .form-input, .form-select {
            width: 100%;
            box-sizing: border-box;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            transition: all 0.2s;
        }

        [data-theme="light"] .form-input,
        [data-theme="light"] .form-select {
            background: rgba(0, 0, 0, 0.02);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-select option {
            background: var(--bg-color);
            color: var(--text-primary);
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

        .badge {
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-created { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-assigned { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge-picked { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
        .badge-stored { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .badge-shipped { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
        .badge-completed { background: rgba(34, 197, 94, 0.2); color: #22c55e; }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            padding: 2rem;
            border-radius: 12px;
            max-width: 450px;
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-arrow-u-up-left"></i> Returns Execution
        </h1>
    </div>

    @if(session('success'))
        <div class="glass" style="padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--success); background: rgba(16, 185, 129, 0.1); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-panels">
        <!-- Operational Returns List -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Operational Return Instructions</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Return Ref</th>
                            <th>Driver & Location</th>
                            <th>Items & Serials</th>
                            <th>Workflow Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instructions as $ret)
                            <tr>
                                <td>
                                    <strong>{{ $ret->return_ref }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ $ret->customer_name }}</div>
                                </td>
                                <td>
                                    <div><i class="ph ph-user"></i> {{ $ret->driver_name ?: 'Not Assigned' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><i class="ph ph-map-pin"></i> {{ $ret->storing_location ?: 'Pending Location' }}</div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                        @foreach($ret->items as $item)
                                            <div style="font-size: 0.85rem;">
                                                <strong>{{ $item->sku_code }}</strong> (x{{ $item->quantity }})
                                                @if($item->serial_numbers)
                                                    <span style="color: var(--text-secondary); font-size: 0.75rem; display: block;">S/N: {{ $item->serial_numbers }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusKey = strtolower(explode(' ', $ret->status)[0]);
                                    @endphp
                                    <span class="badge badge-{{ $statusKey }}">
                                        {{ $ret->status }}
                                    </span>
                                    <div style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                        @if($ret->picking_date) Picked: {{ $ret->picking_date->format('M d, H:i') }} @endif
                                        @if($ret->storing_date) | Stored: {{ $ret->storing_date->format('M d, H:i') }} @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                        <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" 
                                                onclick="openAssignModal('{{ $ret->return_ref }}', '{{ $ret->driver_name }}', '{{ $ret->storing_location }}')">
                                            <i class="ph ph-user-plus"></i> Assign Driver / Loc
                                        </button>

                                        @if($ret->status === 'Driver Assigned')
                                            <form action="{{ route('sfq.returns.status') }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <input type="hidden" name="return_ref" value="{{ $ret->return_ref }}">
                                                <input type="hidden" name="status" value="Picked Up">
                                                <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: var(--accent-primary); width: 100%;">
                                                    <i class="ph ph-check-square"></i> Mark Picked Up
                                                </button>
                                            </form>
                                        @elseif($ret->status === 'Picked Up')
                                            <form action="{{ route('sfq.returns.status') }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <input type="hidden" name="return_ref" value="{{ $ret->return_ref }}">
                                                <input type="hidden" name="status" value="Stored">
                                                <button type="submit" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 100%;">
                                                    <i class="ph ph-stack"></i> Mark Stored (Restock)
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No active Return Instructions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Classification & Return Shipping Form -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Classify & Return Shipment</h3>
            <form action="{{ route('sfq.returns.classify') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="return_ref">Return Reference *</label>
                    <select id="return_ref" name="return_ref" class="form-select" required>
                        <option value="">Select Return</option>
                        @foreach($instructions as $ret)
                            <option value="{{ $ret->return_ref }}">{{ $ret->return_ref }} ({{ $ret->customer_name }})</option>
                        @endforeach
                        @foreach($legacyReturns as $leg)
                            <option value="{{ $leg->return_ref }}">{{ $leg->return_ref }} ({{ $leg->product_sku }} x{{ $leg->quantity }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="classification">Classification *</label>
                    <select id="classification" name="classification" class="form-select" required>
                        <option value="Re-stockable">Re-stockable (Return to Inventory)</option>
                        <option value="Defective">Defective (Send to END for Inspection)</option>
                    </select>
                </div>

                <div class="form-group" style="flex-direction: row; gap: 0.5rem; align-items: center; margin-top: 0.5rem;">
                    <input type="checkbox" id="ship_back" name="ship_back" value="1" style="width: 18px; height: 18px;" onchange="toggleShipmentFields(this.checked)">
                    <label class="form-label" for="ship_back" style="margin: 0;">Arrange shipment back to END (Dubai)</label>
                </div>

                <!-- Shipping Evidence Fields -->
                <div id="shipmentFields" style="display: none; margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Courier Name</label>
                        <input type="text" name="courier_name" class="form-input" placeholder="e.g. DHL / Aramex">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tracking Number / Evidence</label>
                        <input type="text" name="tracking_number" class="form-input" placeholder="Tracking # or Receipt ref">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Shipping Charges Incurred ($)</label>
                        <input type="number" step="0.01" name="shipping_charges" class="form-input" placeholder="0.00 (Cost charged to END)">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1.5rem;">
                    <i class="ph ph-shield-check"></i> Submit Classification & Action
                </button>
            </form>
        </div>
    </div>

    <!-- Assign Driver & Location Modal -->
    <div id="assignModal" class="modal-overlay" style="display: none;">
        <div class="modal-content glass">
            <h3 style="font-size: 1.25rem; margin-top: 0; margin-bottom: 1.5rem; color: var(--text-primary);"><i class="ph ph-user-plus"></i> Assign Driver & Location</h3>
            <form action="{{ route('sfq.returns.assign') }}" method="POST">
                @csrf
                <input type="hidden" name="return_ref" id="modal_return_ref">

                <div class="form-group">
                    <label class="form-label">Assign Delivery Driver *</label>
                    <input type="text" name="driver_name" id="modal_driver_name" class="form-input" required placeholder="Driver Name (e.g. Ahmed)">
                </div>

                <div class="form-group">
                    <label class="form-label">Designated Storage Location *</label>
                    <select name="storing_location" id="modal_storing_location" class="form-select" required>
                        <option value="">Select Storage Location</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->warehouse }} ({{ $loc->zone }}-{{ $loc->rack }}-{{ $loc->bin }}-{{ $loc->level }})">
                                {{ $loc->warehouse }} ({{ $loc->zone }}-{{ $loc->rack }}-{{ $loc->bin }}-{{ $loc->level }}) - {{ $loc->sku }}
                            </option>
                        @endforeach
                        <option value="WH-1 (Zone-A, Rack-1, Bin-1, Level-1)">WH-1 (Zone-A, Rack-1, Bin-1, Level-1) [Default]</option>
                        <option value="WH-1 (Defects & Scrap Zone)">WH-1 (Defects & Scrap Zone)</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeAssignModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openAssignModal(ref, driver, location) {
                document.getElementById('modal_return_ref').value = ref;
                if (driver && driver !== '-') document.getElementById('modal_driver_name').value = driver;
                if (location && location !== '-') document.getElementById('modal_storing_location').value = location;
                document.getElementById('assignModal').style.display = 'flex';
            }

            function closeAssignModal() {
                document.getElementById('assignModal').style.display = 'none';
            }

            function toggleShipmentFields(show) {
                document.getElementById('shipmentFields').style.display = show ? 'block' : 'none';
            }
        </script>
    @endpush
@endsection
