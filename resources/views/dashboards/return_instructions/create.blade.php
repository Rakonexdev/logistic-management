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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
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

        .form-input {
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

        [data-theme="light"] .form-input {
            background: rgba(0, 0, 0, 0.02);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .items-table th {
            padding: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .items-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .remove-row {
            color: var(--danger);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border-radius: 8px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-plus-circle"></i> Create Return Instruction
        </h1>
        <a href="{{ route('return-instructions.index') }}" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Return Instructions
        </a>
    </div>

    @if($errors->any())
        <div class="glass" style="padding: 1rem; margin-bottom: 1rem; border-left: 4px solid var(--danger); background: rgba(239, 68, 68, 0.1); color: var(--danger);">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass form-panel">
        <form action="{{ route('return-instructions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="section-title">Return Details</div>
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1; background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <label class="form-label" style="font-weight: 700; margin-bottom: 0.5rem;">Return Destination / Type *</label>
                    <div style="display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
                        <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 500;">
                            <input type="radio" name="return_type" value="Return to Warehouse" {{ old('return_type', 'Return to Warehouse') == 'Return to Warehouse' ? 'checked' : '' }} style="accent-color: var(--accent-primary, #6366f1); width: 1.1rem; height: 1.1rem;">
                            <span><i class="ph ph-warehouse" style="color: var(--accent-primary, #6366f1);"></i> Return to Warehouse</span>
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 500;">
                            <input type="radio" name="return_type" value="Shipping to Company Return" {{ old('return_type') == 'Shipping to Company Return' ? 'checked' : '' }} style="accent-color: var(--accent-primary, #6366f1); width: 1.1rem; height: 1.1rem;">
                            <span><i class="ph ph-truck" style="color: var(--accent-primary, #6366f1);"></i> Shipping to Company Return</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Return Reference *</label>
                    <input type="text" name="return_ref" class="form-input" required value="{{ old('return_ref', $defaultRef) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Customer / Client Name *</label>
                    <div class="customer-searchable-container" style="position: relative;">
                        <button type="button" class="form-input customer-dropdown-btn" onclick="toggleCustomerDropdown(event)" style="display: flex; justify-content: space-between; align-items: center; text-align: left; cursor: pointer; width: 100%;">
                            <span id="customer-btn-text" style="font-weight: 500;">
                                {{ old('customer_name', 'Select or Enter Customer') }}
                            </span>
                            <i class="ph ph-caret-down" style="margin-left: 0.5rem;"></i>
                        </button>
                        <div id="customer-dropdown-menu" class="customer-dropdown-menu glass" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 300; max-height: 280px; overflow-y: auto; padding: 0.5rem; background: var(--bg-color, #1e1e2d); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); margin-top: 0.25rem;">
                            <div style="position: sticky; top: 0; background: var(--bg-color, #1e1e2d); padding-bottom: 0.4rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--border-color); z-index: 10;">
                                <input type="text" id="customer-search-input" class="form-input" placeholder="🔍 Search or type customer..." oninput="filterCustomerOptions(this)" onclick="event.stopPropagation()" style="padding: 0.4rem 0.6rem; font-size: 0.85rem; width: 100%; box-sizing: border-box;">
                            </div>
                            <div class="customer-options-list" style="display: flex; flex-direction: column; gap: 0.2rem;">
                                @foreach($customers as $cust)
                                    <div class="customer-option-item" 
                                         onclick="selectCustomerOption('{{ e($cust->name) }}', '{{ e($cust->address ?? '') }}', '{{ e($cust->contact_number ?? '') }}')" 
                                         style="padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 6px; font-weight: 500; font-size: 0.875rem; transition: background 0.15s;" 
                                         onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" 
                                         onmouseout="this.style.background='transparent'">
                                        <div style="font-weight: 600;">{{ $cust->name }}</div>
                                        @if($cust->address || $cust->contact_number)
                                            <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                                {{ implode(' • ', array_filter([$cust->address, $cust->contact_number])) }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                @if($customers->isEmpty())
                                    <div style="padding: 0.5rem 0.75rem; font-size: 0.85rem; color: var(--text-secondary); text-align: center;">
                                        No customers found in database. Type customer name in search box.
                                    </div>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" id="customer_name_input" name="customer_name" value="{{ old('customer_name') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pickup Address *</label>
                    <input type="text" name="pickup_address" class="form-input" required placeholder="Full address for pickup" value="{{ old('pickup_address') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-input" placeholder="Contact person at client site" value="{{ old('contact_person') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-input" placeholder="Phone number" value="{{ old('contact_phone') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Return Document</label>
                    <input type="file" name="attachment" class="form-input" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" style="padding: 0.5rem 0.75rem;">
                </div>
            </div>

            <div class="section-title">Items to Pick Up</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Product / SKU *</th>
                        <th style="width: 35%;">Description</th>
                        <th style="width: 15%;">Quantity *</th>
                        <th style="width: 25%;">Serial Numbers (if any)</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td>
                            <input type="text" name="items[0][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU">
                        </td>
                        <td>
                            <input type="text" name="items[0][description]" class="form-input" placeholder="Item details/condition">
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-input" required min="1" value="1">
                        </td>
                        <td>
                            <input type="text" name="items[0][serial_numbers]" class="form-input" placeholder="Comma separated serials">
                        </td>
                        <td>
                            <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-outline" onclick="addRow()">
                <i class="ph ph-plus"></i> Add Item SKU
            </button>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Special Instructions / Remarks</label>
                <textarea name="remarks" class="form-input" rows="3" placeholder="Additional details for driver or warehouse team..."></textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('return-instructions.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk"></i> Save & Issue Return Instruction
                </button>
            </div>
        </form>

        <datalist id="products-list">
            @foreach($products as $prod)
                <option value="{{ $prod->sku_code }}">{{ $prod->sku_code }} - {{ $prod->name }}</option>
            @endforeach
        </datalist>
    </div>

    <script>
        let rowCount = 1;
        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${rowCount}][sku_code]" list="products-list" class="form-input" required placeholder="Select or Enter SKU">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][description]" class="form-input" placeholder="Item details/condition">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-input" required min="1" value="1">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][serial_numbers]" class="form-input" placeholder="Comma separated serials">
                </td>
                <td>
                    <button type="button" class="remove-row" onclick="this.closest('tr').remove()">
                        <i class="ph ph-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            rowCount++;
        }

        function toggleCustomerDropdown(e) {
            e.stopPropagation();
            const menu = document.getElementById('customer-dropdown-menu');
            if (!menu) return;
            const isVisible = menu.style.display === 'block';

            if (!isVisible) {
                menu.style.display = 'block';
                const searchInput = document.getElementById('customer-search-input');
                if (searchInput) {
                    searchInput.value = '';
                    filterCustomerOptions(searchInput);
                    setTimeout(() => searchInput.focus(), 50);
                }
            } else {
                menu.style.display = 'none';
            }
        }

        function filterCustomerOptions(input) {
            const filter = input.value.trim();
            const filterLower = filter.toLowerCase();
            const list = document.querySelectorAll('.customer-option-item');
            
            list.forEach(item => {
                const text = item.textContent.toLowerCase();
                const matches = text.includes(filterLower);
                item.style.display = matches ? 'block' : 'none';
            });

            const hiddenInput = document.getElementById('customer_name_input');
            const btnText = document.getElementById('customer-btn-text');
            if (filter) {
                hiddenInput.value = filter;
                btnText.textContent = filter;
            }
        }

        function selectCustomerOption(name, address, phone) {
            const hiddenInput = document.getElementById('customer_name_input');
            const btnText = document.getElementById('customer-btn-text');
            const menu = document.getElementById('customer-dropdown-menu');

            if (hiddenInput) hiddenInput.value = name;
            if (btnText) btnText.textContent = name;
            if (menu) menu.style.display = 'none';

            const addressInput = document.querySelector('input[name="pickup_address"]');
            if (address && addressInput) {
                addressInput.value = address;
            }

            const phoneInput = document.querySelector('input[name="contact_phone"]');
            if (phone && phoneInput) {
                phoneInput.value = phone;
            }
        }

        document.addEventListener('click', function(e) {
            const custContainer = document.querySelector('.customer-searchable-container');
            if (custContainer && !custContainer.contains(e.target)) {
                const menu = document.getElementById('customer-dropdown-menu');
                if (menu) menu.style.display = 'none';
            }
        });
    </script>
@endsection
