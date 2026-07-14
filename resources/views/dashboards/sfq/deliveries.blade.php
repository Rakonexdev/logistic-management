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

        .btn {
            padding: 0.75rem 1.5rem;
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

        .badge-pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .badge-assigned { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-delivered { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-truck"></i> Delivery Planning & Assignment
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-panels">
        <!-- Deliveries Table -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Current Delivery Trips</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Delivery Ref</th>
                            <th>Sales Order</th>
                            <th>Address</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveries as $del)
                            <tr>
                                <td><strong>{{ $del['ref'] }}</strong></td>
                                <td>{{ $del['so'] }}</td>
                                <td>{{ $del['address'] }}</td>
                                <td>{{ $del['driver'] }}</td>
                                <td>{{ $del['vehicle'] }}</td>
                                <td>
                                    <span class="badge badge-{{ $del['status'] === 'Assigned' ? 'assigned' : ($del['status'] === 'Delivered' ? 'delivered' : 'pending') }}">
                                        {{ $del['status'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($del['status'] === 'Assigned')
                                        <form action="{{ route('sfq.deliveries.complete') }}" method="POST" style="display: inline;" onsubmit="return confirm('Mark delivery trip {{ $del['ref'] }} as Delivered?')">
                                            @csrf
                                            <input type="hidden" name="delivery_ref" value="{{ $del['ref'] }}">
                                            <button type="submit" class="btn btn-outline" style="padding: 0.5rem; font-size: 0.8rem; color: var(--success); border-color: rgba(16, 185, 129, 0.2);">
                                                <i class="ph ph-check"></i> Complete
                                            </button>
                                        </form>
                                    @elseif($del['status'] === 'Delivered')
                                        <span style="font-size: 0.85rem; color: var(--success); font-weight: 600;"><i class="ph ph-check-circle"></i> Delivered</span>
                                    @else
                                        <span style="font-size: 0.85rem; color: var(--text-secondary);">Awaiting Dispatch</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Assign Driver & Vehicle</h3>
            <form action="{{ route('sfq.deliveries.assign') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="delivery_ref">Delivery / Trip Reference</label>
                    <select id="delivery_ref" name="delivery_ref" class="form-select" required>
                        <option value="">Select Trip</option>
                        @foreach($deliveries as $del)
                            @if($del['status'] === 'Pending Assignment')
                                <option value="{{ $del['ref'] }}">{{ $del['ref'] }} ({{ $del['so'] }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="driver">Assign Driver</label>
                    <select id="driver" name="driver" class="form-select" required>
                        <option value="">Select Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->name }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="vehicle">Vehicle / Trip Reference</label>
                    <input type="text" id="vehicle" name="vehicle" class="form-input" placeholder="e.g. Truck-04-A" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                    <i class="ph ph-user-plus"></i> Assign & Dispatch
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelivery(ref) {
            const upload = confirm(`Upload Proof of Delivery photo for ${ref}?`);
            if (upload) {
                alert(`Delivery ${ref} completed successfully. POD registered.`);
                window.location.reload();
            }
        }
    </script>
@endpush
