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

        .badge-defective { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        .badge-re-stockable { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-arrow-u-up-left"></i> Returns Execution
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-panels">
        <!-- Returns List -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Operational Returns</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Return Ref</th>
                            <th>Driver</th>
                            <th>SKU Code</th>
                            <th>Qty</th>
                            <th>Classification</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $ret)
                            <tr>
                                <td><strong>{{ $ret['ref'] }}</strong></td>
                                <td>{{ $ret['driver'] }}</td>
                                <td>{{ $ret['sku'] }}</td>
                                <td>{{ $ret['qty'] }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($ret['classification']) }}">
                                        {{ $ret['classification'] }}
                                    </span>
                                </td>
                                <td>{{ $ret['status'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Classification Form -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Classify Returned Stock</h3>
            <form action="{{ route('sfq.returns.classify') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="return_ref">Return Reference</label>
                    <select id="return_ref" name="return_ref" class="form-select" required>
                        <option value="">Select Return</option>
                        @foreach($returns as $ret)
                            <option value="{{ $ret['ref'] }}">{{ $ret['ref'] }} ({{ $ret['sku'] }} x{{ $ret['qty'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="classification">Classification</label>
                    <select id="classification" name="classification" class="form-select" required>
                        <option value="Re-stockable">Re-stockable (Return to Inventory)</option>
                        <option value="Defective">Defective (Move to Scrap/Defects Zone)</option>
                    </select>
                </div>

                <div class="form-group" style="flex-direction: row; gap: 0.5rem; align-items: center; margin-top: 0.5rem;">
                    <input type="checkbox" id="ship_back" name="ship_back" value="1" style="width: 18px; height: 18px;">
                    <label class="form-label" for="ship_back" style="margin: 0;">Arrange shipment back to END customer</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                    <i class="ph ph-shield-check"></i> Submit Classification
                </button>
            </form>
        </div>
    </div>
@endsection
