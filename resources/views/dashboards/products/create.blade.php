@extends('layouts.dashboard')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 2rem;
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
            max-width: 800px;
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
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
        }

        .form-control option {
            background: var(--bg-color);
            color: var(--text-primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
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
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">
            <i class="ph ph-plus-circle"></i>
            Add New Product
        </h1>
        <a href="{{ route('products.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="glass form-panel">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">SKU Code *</label>
                    <input type="text" name="sku_code" class="form-control" value="{{ old('sku_code') }}" required>
                    @error('sku_code') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Product Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="physical" {{ old('type') == 'physical' ? 'selected' : '' }}>Physical</option>
                        <option value="electronic" {{ old('type') == 'electronic' ? 'selected' : '' }}>Electronic</option>
                    </select>
                    @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category') }}">
                    @error('category') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" id="serial-numbers-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label class="form-label" style="margin-bottom: 0;">Serial Numbers</label>
                        <button type="button" class="btn btn-outline" id="add-serial-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                            <i class="ph ph-plus"></i> Add Serial
                        </button>
                    </div>
                    <div id="serial-inputs-wrapper" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div class="serial-input-row" style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="text" name="serial_numbers[]" class="form-control" placeholder="e.g. SN-12345" style="flex: 1;">
                            <button type="button" class="btn btn-outline remove-serial-btn" style="padding: 0.5rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </div>
                    @error('serial_number') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity *</label>
                    <input type="number" name="qty" class="form-control" id="qty-input" value="{{ old('qty', 0) }}" min="0" required>
                    @error('qty') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Vendor ID</label>
                    <input type="text" name="vendor_id" class="form-control" value="{{ old('vendor_id') }}">
                    @error('vendor_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk"></i> Save Product
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const wrapper = document.getElementById('serial-inputs-wrapper');
                const addBtn = document.getElementById('add-serial-btn');
                const qtyInput = document.getElementById('qty-input');

                function updateQuantity() {
                    const typeSelect = document.querySelector('select[name="type"]');
                    if (typeSelect && typeSelect.value === 'physical') {
                        let filledCount = 0;
                        wrapper.querySelectorAll('input[name="serial_numbers[]"]').forEach(input => {
                            if (input.value.trim() !== '') {
                                filledCount++;
                            }
                        });
                        if (filledCount > 0) {
                            qtyInput.value = filledCount;
                        }
                    }
                }

                addBtn.addEventListener('click', function() {
                    const row = document.createElement('div');
                    row.className = 'serial-input-row';
                    row.style.display = 'flex';
                    row.style.gap = '0.5rem';
                    row.style.alignItems = 'center';
                    row.innerHTML = `
                        <input type="text" name="serial_numbers[]" class="form-control" placeholder="e.g. SN-12345" style="flex: 1;">
                        <button type="button" class="btn btn-outline remove-serial-btn" style="padding: 0.5rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);">
                            <i class="ph ph-trash"></i>
                        </button>
                    `;
                    wrapper.appendChild(row);
                    row.querySelector('input').addEventListener('input', updateQuantity);
                });

                wrapper.addEventListener('click', function(e) {
                    const btn = e.target.closest('.remove-serial-btn');
                    if (btn) {
                        const row = btn.closest('.serial-input-row');
                        if (wrapper.querySelectorAll('.serial-input-row').length > 1) {
                            row.remove();
                            updateQuantity();
                        } else {
                            row.querySelector('input').value = '';
                            updateQuantity();
                        }
                    }
                });

                wrapper.querySelectorAll('input[name="serial_numbers[]"]').forEach(input => {
                    input.addEventListener('input', updateQuantity);
                });
            });
        </script>
    @endpush
@endsection