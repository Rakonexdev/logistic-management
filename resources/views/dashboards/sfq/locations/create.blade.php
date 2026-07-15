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
            max-width: 800px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
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
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-plus"></i> Create Location
        </h1>
        <a href="{{ route('sfq.locations.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <div class="form-panel glass">
        <form action="{{ route('sfq.locations.store') }}" method="POST">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="warehouse">Warehouse Name</label>
                    <input type="text" id="warehouse" name="warehouse" class="form-input" placeholder="e.g. WH-Main" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="zone">Zone / Area</label>
                    <input type="text" id="zone" name="zone" class="form-input" placeholder="e.g. A" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="rack">Rack</label>
                    <input type="text" id="rack" name="rack" class="form-input" placeholder="e.g. 02" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bin">Bin</label>
                    <input type="text" id="bin" name="bin" class="form-input" placeholder="e.g. B3" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="level">Level</label>
                    <input type="text" id="level" name="level" class="form-input" placeholder="e.g. 1" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="sku">SKU Code</label>
                    <select id="sku" name="sku" class="form-select" required>
                        <option value="">Select SKU</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->sku_code }}">{{ $prod->sku_code }} - {{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="qty">Quantity</label>
                    <input type="number" id="qty" name="qty" class="form-input" min="0" placeholder="e.g. 150" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="Available">Available</option>
                        <option value="Reserved">Reserved</option>
                        <option value="Damaged">Damaged</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('sfq.locations.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i> Store Location
                </button>
            </div>
        </form>
    </div>
@endsection
