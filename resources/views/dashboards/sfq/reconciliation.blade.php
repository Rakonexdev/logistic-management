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

        .badge-reconciled { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .badge-pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-currency-circle-dollar"></i> Charges Reconciliation
        </h1>
        <button class="btn btn-outline" onclick="alert('Export complete!')">
            <i class="ph ph-export"></i> Export Report
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="form-panel glass">
        <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Proposed vs Non-Proposed Charges</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Delivery / Item Ref</th>
                        <th>Charge Type</th>
                        <th>Proposed Amount</th>
                        <th>Non-Proposed Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charges as $chg)
                        <tr>
                            <td><strong>{{ $chg['ref'] }}</strong></td>
                            <td>{{ $chg['type'] }}</td>
                            <td>${{ number_format($chg['proposed'], 2) }}</td>
                            <td style="{{ $chg['non_proposed'] > 0 ? 'color: var(--danger); font-weight: 600;' : '' }}">
                                ${{ number_format($chg['non_proposed'], 2) }}
                            </td>
                            <td>
                                <span class="badge badge-{{ strtolower($chg['status']) }}">
                                    {{ $chg['status'] }}
                                </span>
                            </td>
                            <td>
                                @if($chg['status'] === 'Pending')
                                    <form action="{{ route('sfq.reconciliation.update') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="charge_ref" value="{{ $chg['ref'] }}">
                                        <input type="hidden" name="status" value="Reconciled">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ph ph-shield-check"></i> Reconcile Line
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 0.85rem; color: var(--text-secondary);">Reconciled</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
