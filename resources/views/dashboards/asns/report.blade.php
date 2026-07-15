<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRN Inspection Report - {{ $asn->asn_reference }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .report-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 800px;
            min-height: 1050px;
            padding: 3rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Top Action Bar */
        .action-bar {
            width: 100%;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
        }

        .btn-print {
            background-color: #6366f1;
            color: #ffffff;
        }

        .btn-print:hover {
            background-color: #4f46e5;
        }

        .btn-back {
            background-color: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-back:hover {
            background-color: #f9fafb;
        }

        /* Header Styles */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .company-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-logo {
            width: 32px;
            height: 32px;
            background-color: #6366f1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.25rem;
        }

        .brand-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.025em;
        }

        .report-meta {
            text-align: right;
        }

        .report-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }

        .meta-row {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .meta-row strong {
            color: #374151;
        }

        /* Letter Content */
        .intro-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 2rem;
            white-space: pre-line;
        }

        /* Table Styles */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .details-table th, .details-table td {
            border: 1px solid #e5e7eb;
            padding: 0.85rem 1rem;
            text-align: left;
            font-size: 0.875rem;
        }

        .details-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .details-table td {
            color: #4b5563;
        }

        .badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-verified { background-color: #d1fae5; color: #065f46; }
        .badge-shortage { background-color: #fee2e2; color: #991b1b; }
        .badge-overage { background-color: #dbeafe; color: #1e40af; }
        .badge-damaged { background-color: #fef3c7; color: #92400e; }

        /* Summary & Closing */
        .summary-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 1.5rem;
            background-color: #f9fafb;
            padding: 1.5rem;
            border-radius: 6px;
            border-left: 4px solid #6366f1;
        }

        .closing-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 4rem;
        }

        /* Signatures Section */
        .signature-section {
            margin-top: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            padding-top: 2rem;
            border-top: 1px dashed #e5e7eb;
        }

        .sig-col {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .sig-field {
            font-size: 0.9rem;
            color: #4b5563;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .sig-line {
            flex: 1;
            border-bottom: 1px solid #9ca3af;
            margin-left: 0.5rem;
            height: 1.2rem;
        }

        /* Print Media Overrides */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .report-container {
                box-shadow: none;
                padding: 0;
                border-radius: 0;
                width: 100%;
                max-width: 100%;
                min-height: auto;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- Action Bar (Hidden when printed) -->
<div class="action-bar no-print">
    <a href="{{ route('asns.show', $asn->id) }}" class="btn btn-back">
        <i class="ph ph-arrow-left"></i> Back to Details
    </a>
    <button onclick="window.print()" class="btn btn-print">
        <i class="ph ph-printer"></i> Print Report / PDF
    </button>
</div>

<!-- Printable Report Container -->
<div class="report-container">
    <!-- Header -->
    <header class="report-header">
        <div class="company-brand">
            <div class="brand-logo">
                <i class="ph ph-buildings"></i>
            </div>
            <span class="brand-name">LogisticsPro</span>
        </div>
        <div class="report-meta">
            <h1 class="report-title">Inspection Report</h1>
            <div class="meta-row">ASN Reference: <strong>{{ $asn->asn_reference }}</strong></div>
            <div class="meta-row">AWB: <strong>{{ $asn->airway_bill }}</strong></div>
            <div class="meta-row">Report Date: <strong>{{ date('M d, Y') }}</strong></div>
            <div class="meta-row">Vendor / Supplier: <strong>{{ $asn->vendor_id }}</strong></div>
        </div>
    </header>

    <!-- Introduction -->
    <div class="intro-text">Dear Sir,

We have received the products listed below. The following report contains the details of the received items, including the SKU, item name, quantity, and any discrepancies identified during the inspection.</div>

    @php
        $hasDamaged = false;
        $hasMissing = false;
        foreach ($asn->items as $item) {
            $discQty = $item->discrepancy_qty ?? 0;
            $discReason = strtolower($item->discrepancy_reason ?? '');
            $isDamaged = str_contains($discReason, 'damage') || str_contains($discReason, 'defect');
            $isShortage = str_contains($discReason, 'shortage') || str_contains($discReason, 'missing') || ($discQty < 0 && !$isDamaged);
            if ($isDamaged && abs($discQty) > 0) {
                $hasDamaged = true;
            }
            if ($isShortage && abs($discQty) > 0) {
                $hasMissing = true;
            }
        }
    @endphp

    <!-- Product Details Table -->
    <table class="details-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Item Name</th>
                <th>Ordered Qty</th>
                <th>Received Qty</th>
                @if($hasDamaged)
                    <th>Damaged Qty</th>
                @endif
                @if($hasMissing)
                    <th>Missing Qty</th>
                @endif
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asn->items as $item)
                @php
                    $product = \App\Models\Product::where('sku_code', $item->sku_code)->first();
                    $productName = $product ? $product->name : 'N/A';
                    
                    $orderedQty = $item->quantity;
                    $receivedQty = $item->received_qty ?? 0;
                    
                    $discQty = $item->discrepancy_qty ?? 0;
                    $discReason = strtolower($item->discrepancy_reason ?? '');
                    
                    // Determine damaged and missing
                    $isDamaged = str_contains($discReason, 'damage') || str_contains($discReason, 'defect');
                    $isShortage = str_contains($discReason, 'shortage') || str_contains($discReason, 'missing') || ($discQty < 0 && !$isDamaged);
                    
                    $damagedQty = $isDamaged ? abs($discQty) : 0;
                    $missingQty = $isShortage ? abs($discQty) : 0;
                    
                    // Status Badge
                    if ($discQty == 0 && ($item->received_qty !== null)) {
                        $statusClass = 'verified';
                        $statusLabel = 'Verified';
                    } elseif ($isDamaged) {
                        $statusClass = 'damaged';
                        $statusLabel = 'Damaged';
                    } elseif ($discQty < 0) {
                        $statusClass = 'shortage';
                        $statusLabel = 'Shortage';
                    } elseif ($discQty > 0) {
                        $statusClass = 'overage';
                        $statusLabel = 'Overage';
                    } else {
                        $statusClass = 'verified';
                        $statusLabel = 'Pending Review';
                    }
                @endphp
                <tr>
                    <td style="font-weight: 600;">{{ $item->sku_code }}</td>
                    <td>{{ $productName }}</td>
                    <td>{{ $orderedQty }}</td>
                    <td>{{ $item->received_qty ?? '0' }}</td>
                    @if($hasDamaged)
                        <td>{{ $damagedQty ?: '-' }}</td>
                    @endif
                    @if($hasMissing)
                        <td>{{ $missingQty ?: '-' }}</td>
                    @endif
                    <td>
                        <span class="badge badge-{{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td>{{ $item->discrepancy_reason ?? 'No issues' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary-text">Based on our inspection, the products listed above have been verified. Any shortages, damages, or discrepancies have been recorded in the report. Please review the details and take the necessary action if required.</div>

    <!-- Closing -->
    <div class="closing-text">Kindly review the above report. If you require any further clarification, please let us know.

Thank you.</div>

    <!-- Signature Section -->
    <section class="signature-section">
        <div class="sig-col">
            <div class="sig-field">Prepared By:<div class="sig-line"></div></div>
            <div class="sig-field">Date:<div class="sig-line"></div></div>
        </div>
        <div class="sig-col">
            <div class="sig-field">Verified By:<div class="sig-line"></div></div>
            <div class="sig-field">Signature:<div class="sig-line"></div></div>
        </div>
    </section>

    <!-- Report Footer -->
    <div style="margin-top: 3rem; border-top: 1px solid #e5e7eb; padding-top: 1rem; display: flex; justify-content: space-between; font-size: 0.75rem; color: #9ca3af;">
        <span>ASN Reference: <strong>{{ $asn->asn_reference }}</strong></span>
        <span>Vendor: <strong>{{ $asn->vendor_id }}</strong></span>
    </div>
</div>

</body>
</html>
