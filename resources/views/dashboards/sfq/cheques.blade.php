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

        .badge-collected { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .badge-pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-bank"></i> Cheque Collections
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-panels">
        <!-- Cheques Table -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Cheque Collection Requests</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ref Code</th>
                            <th>Customer</th>
                            <th>Assigned Driver</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cheques as $chq)
                            <tr>
                                <td><strong>{{ $chq['ref'] }}</strong></td>
                                <td>{{ $chq['customer'] }}</td>
                                <td>{{ $chq['driver'] }}</td>
                                <td>${{ number_format($chq['amount'], 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($chq['status']) === 'collected' ? 'collected' : 'pending' }}">
                                        {{ $chq['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Record Form -->
        <div class="form-panel glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Register Collection</h3>
            <form action="{{ route('sfq.cheques.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="cheque_ref">Collection Reference</label>
                    <select id="cheque_ref" name="cheque_ref" class="form-select" required>
                        <option value="">Select Reference</option>
                        @foreach($cheques as $chq)
                            @if($chq['status'] !== 'Collected')
                                <option value="{{ $chq['ref'] }}" data-amount="{{ $chq['amount'] }}">
                                    {{ $chq['ref'] }} - {{ $chq['customer'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="amount">Cheque Amount ($)</label>
                    <input type="number" id="amount" name="amount" class="form-input" step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cheque_image">Upload Cheque Photo</label>
                    <input type="file" id="cheque_image" name="cheque_image" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                    <i class="ph ph-check-square"></i> Submit Collection
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('cheque_ref').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const amount = selectedOption.getAttribute('data-amount') || '';
            document.getElementById('amount').value = amount;
        });
    </script>
@endpush
