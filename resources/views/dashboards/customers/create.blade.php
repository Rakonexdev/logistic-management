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
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-panel {
            padding: 2rem;
            max-width: 700px;
            border-radius: 12px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .text-danger {
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.35rem;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-user-plus" style="color: var(--accent-primary);"></i>
            Add New Customer
        </h1>
        <a href="{{ route('customers.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Customers
        </a>
    </div>

    <div class="glass form-panel">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="name">Customer Name <span style="color: var(--danger);">*</span></label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Acme Corporation" required>
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="contact_number_digits">Contact Number</label>
                <div style="display: flex; gap: 0.5rem;">
                    <select name="country_code" class="form-control" style="width: 120px; flex-shrink: 0;">
                        <option value="+974" {{ old('country_code', '+974') == '+974' ? 'selected' : '' }}>🇶🇦 +974</option>
                        <option value="+971" {{ old('country_code') == '+971' ? 'selected' : '' }}>🇦🇪 +971</option>
                    </select>
                    <input type="text" id="contact_number_digits" name="contact_number_digits" class="form-control @error('contact_number_digits') is-invalid @enderror @error('contact_number') is-invalid @enderror" value="{{ old('contact_number_digits') }}" placeholder="8-digit number" maxlength="8" pattern="[0-9]{8}" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                </div>
                <small style="color: var(--text-secondary); font-size: 0.75rem; margin-top: 0.25rem; display: block;">Supports UAE (+971) & Qatar (+974) with an 8-digit number.</small>
                @error('contact_number_digits')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                @error('contact_number')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" placeholder="Enter complete customer address...">{{ old('address') }}</textarea>
                @error('address')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk"></i> Save Customer
                </button>
            </div>
        </form>
    </div>
@endsection
