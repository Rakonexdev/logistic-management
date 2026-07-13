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

        .form-input, .form-select, .form-textarea {
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
        [data-theme="light"] .form-select,
        [data-theme="light"] .form-textarea {
            background: rgba(0, 0, 0, 0.02);
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
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
            margin-bottom: 1.5rem;
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
            transform: translateY(-1px);
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
            text-transform: uppercase;
        }

        .badge-pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .badge-completed { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-download-simple"></i> GRN Confirmation
        </h1>
        <a href="#" class="btn btn-outline">
            <i class="ph ph-clock-counter-clockwise"></i> View ASN/GRN History
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="form-panel glass">
        <form action="{{ route('sfq.grns.confirm') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="grn_number">GRN Number</label>
                    <input type="text" id="grn_number" name="grn_number" class="form-input" value="GRN-{{ time() }}" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="asn_id">ASN Reference</label>
                    <select id="asn_id" name="asn_id" class="form-select" required>
                        <option value="">Select ASN to Confirm</option>
                        @foreach($asns as $asn)
                            <option value="{{ $asn->id }}" data-airway="{{ $asn->airway_bill }}">
                                {{ $asn->asn_reference }} ({{ $asn->vendor_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="airway_bill">Airway Bill Number</label>
                    <input type="text" id="airway_bill" name="airway_bill" class="form-input" placeholder="e.g. AWB-12345" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="received_date">Received Date</label>
                    <input type="date" id="received_date" name="received_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <!-- Inbound Items Quantity Verification -->
            <div style="margin-top: 2rem; margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text-primary);">Verify Received Quantities</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product / SKU</th>
                                <th>Expected Qty</th>
                                <th>Received Qty</th>
                                <th>Discrepancy Qty</th>
                                <th>Discrepancy Reason</th>
                                <th>Location Assignment</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-secondary);">Select an ASN above to populate items</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="photo">Supporting Photo / Attachment</label>
                    <input type="file" id="photo" name="photo" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label" for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-textarea" rows="2" placeholder="e.g. Discrepancy noted in SKU-001 box damage..."></textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <button type="submit" name="action" value="draft" class="btn btn-outline">
                    <i class="ph ph-floppy-disk"></i> Save Draft
                </button>
                <button type="submit" name="action" value="report" class="btn btn-outline" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.2);">
                    <i class="ph ph-warning-octagon"></i> Report Discrepancy
                </button>
                <button type="submit" name="action" value="submit" class="btn btn-primary">
                    <i class="ph ph-check-square"></i> Submit GRN
                </button>
            </div>
        </form>
    </div>

    <!-- ASN list summary -->
    <div class="form-panel glass" style="padding: 1.5rem;">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text-primary);">Recent Inbound Shipments</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ASN Reference</th>
                        <th>Airway Bill</th>
                        <th>Vendor</th>
                        <th>Status</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asns as $asn)
                        <tr>
                            <td><strong>{{ $asn->asn_reference }}</strong></td>
                            <td>{{ $asn->airway_bill }}</td>
                            <td>{{ $asn->vendor_id }}</td>
                            <td>
                                <span class="badge badge-{{ $asn->status }}">
                                    {{ $asn->status }}
                                </span>
                            </td>
                            <td>{{ $asn->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary);">No shipments found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('asn_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const airway = selectedOption.getAttribute('data-airway') || '';
            document.getElementById('airway_bill').value = airway;

            const asnId = this.value;
            const tbody = document.getElementById('items-tbody');

            if (!asnId) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: var(--text-secondary);">Select an ASN above to populate items</td></tr>';
                return;
            }

            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading ASN items...</td></tr>';

            // Query dynamic details
            fetch('{{ route('asns.index') }}/' + asnId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                const items = data.items || [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No items found in this ASN</td></tr>';
                    return;
                }

                items.forEach((item, idx) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${item.sku_code}</strong></td>
                        <td>
                            <span id="exp-qty-${idx}">${item.quantity}</span>
                            <input type="hidden" name="items[${idx}][sku_code]" value="${item.sku_code}">
                            <input type="hidden" name="items[${idx}][expected_qty]" value="${item.quantity}">
                        </td>
                        <td>
                            <input type="number" name="received_qty[${item.sku_code}]" class="form-input" style="width: 100px; padding: 0.5rem;" value="${item.quantity}" min="0" oninput="calculateDiscrepancy(this, ${item.quantity}, 'disc-${idx}')">
                        </td>
                        <td>
                            <input type="number" id="disc-${idx}" name="discrepancy_qty[${item.sku_code}]" class="form-input" style="width: 100px; padding: 0.5rem; background: rgba(0,0,0,0.1);" value="0" readonly>
                        </td>
                        <td>
                            <select name="discrepancy_reason[${item.sku_code}]" class="form-select" style="padding: 0.5rem;">
                                <option value="">None</option>
                                <option value="damaged">Damaged</option>
                                <option value="shortage">Shortage</option>
                                <option value="overage">Overage</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="location[${item.sku_code}]" class="form-input" style="padding: 0.5rem;" placeholder="e.g. WH-Main-A-02">
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: var(--danger);">Failed to load ASN items</td></tr>';
            });
        });

        function calculateDiscrepancy(input, expected, discId) {
            const received = parseInt(input.value) || 0;
            const discField = document.getElementById(discId);
            if (discField) {
                discField.value = received - expected;
            }
        }
    </script>
@endpush
