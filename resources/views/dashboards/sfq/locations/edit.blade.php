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
            <i class="ph ph-pencil"></i> Edit Location
        </h1>
        <a href="{{ route('sfq.locations.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <div class="form-panel glass">
        <form action="{{ route('sfq.locations.update', $location->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="warehouse">Warehouse Name</label>
                    <input type="text" id="warehouse" name="warehouse" class="form-input" value="{{ old('warehouse', $location->warehouse) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="zone">Zone / Area</label>
                    <input type="text" id="zone" name="zone" class="form-input" value="{{ old('zone', $location->zone) }}" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="rack">Rack</label>
                    <input type="text" id="rack" name="rack" class="form-input" value="{{ old('rack', $location->rack) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bin">Bin</label>
                    <input type="text" id="bin" name="bin" class="form-input" value="{{ old('bin', $location->bin) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="level">Level</label>
                    <input type="text" id="level" name="level" class="form-input" value="{{ old('level', $location->level) }}" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="sku">SKU Code</label>
                    <select id="sku" name="sku" class="form-select" required>
                        <option value="">Select SKU</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->sku_code }}" data-qty="{{ $prod->available_qty }}" {{ old('sku', $location->sku) === $prod->sku_code ? 'selected' : '' }}>
                                {{ $prod->sku_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="qty">Quantity</label>
                    <input type="number" id="qty" name="qty" class="form-input" min="0" value="{{ old('qty', $location->qty) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="Available" {{ old('status', $location->status) === 'Available' ? 'selected' : '' }}>Available</option>
                        <option value="Reserved" {{ old('status', $location->status) === 'Reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="Damaged" {{ old('status', $location->status) === 'Damaged' ? 'selected' : '' }}>Damaged</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('sfq.locations.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i> Update Location
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('sku').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const qty = selectedOption.getAttribute('data-qty') || '';
            document.getElementById('qty').value = qty;
        });
    </script>
@endpush
