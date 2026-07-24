<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note - {{ $note->dn_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .company-info {
            line-height: 1.4;
        }
        .company-info h2 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .logo-box {
            text-align: right;
        }
        .logo-box h1 {
            margin: 0;
            font-size: 20px;
            color: #7b1e7a;
            font-weight: bold;
            letter-spacing: 0.05em;
        }
        .logo-sub {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0;
            letter-spacing: 0.1em;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-block {
            border: 1px solid #000;
            padding: 10px;
            min-height: 100px;
        }
        .info-block.noborder {
            border: none;
            padding: 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .info-table td.label {
            background-color: #e5e5e5;
            font-weight: bold;
            width: 35%;
        }
        .label-text {
            font-weight: bold;
            margin-bottom: 4px;
            text-decoration: underline;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        .data-table th {
            background-color: #e5e5e5;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .data-table td.center {
            text-align: center;
        }
        .total-row td {
            font-weight: bold;
        }
        .footer-note {
            font-weight: bold;
            margin: 15px 0;
            font-size: 10px;
        }
        .receipt-section {
            margin-top: 30px;
            border-top: 1px dashed #000;
            padding-top: 15px;
        }
        .receipt-title {
            font-weight: bold;
            margin-bottom: 15px;
        }
        .receipt-fields {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 25px;
        }
        .field-line {
            border-bottom: 1px solid #000;
            height: 20px;
        }
        .field-label {
            margin-top: 5px;
            font-size: 9px;
            font-weight: bold;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
        .print-btn-bar {
            background: #f5f5f5;
            padding: 10px;
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .btn {
            background: #7b1e7a;
            color: #fff;
            border: none;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn:hover {
            background: #601560;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="print-btn-bar no-print">
        <button class="btn" onclick="window.print()">Print Document</button>
    </div>

    <div class="container">
        <!-- Top Header -->
        <div class="header">
            <div class="company-info">
                <h2>Solutions Four WLL</h2>
                MR GEORGE THOMAS<br>
                C/O EXECUTIVE NETWORKS ME<br>
                OPP OF AL AREEKAH CO<br>
                3F9M+R7V8BIRKAT AL AWAMER, QATAR,<br>
                DOHA, BUHAMOUR<br>
                QATAR<br>
                Contact: +97455848627
            </div>
            <div class="logo-box" style="text-align: right;">
                <div style="display: inline-flex; flex-direction: column; align-items: flex-end;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-family: Arial, sans-serif; font-weight: 900; font-size: 22px; color: #0255a5; letter-spacing: -0.5px;">solutions<span style="font-weight: 900;">four</span></span>
                        <svg width="34" height="18" viewBox="0 0 34 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="26" cy="4" r="3.5" fill="#0255a5"/>
                            <circle cx="16" cy="11" r="3.5" fill="#0255a5"/>
                            <circle cx="24" cy="11" r="3.5" fill="#0255a5"/>
                            <circle cx="31" cy="11" r="2.5" fill="#0255a5"/>
                        </svg>
                    </div>
                    <div style="font-family: Arial, sans-serif; font-weight: 900; font-size: 14px; color: #0255a5; letter-spacing: 4px; margin-top: -2px; text-transform: uppercase;">QATAR</div>
                </div>
            </div>
        </div>

        <div class="title">MATERIAL RELEASE NOTE</div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-block">
                <div class="label-text">SHIP TO:</div>
                <strong>{{ $note->deliveryInstruction->customer_name ?? 'N/A' }}</strong><br>
                {{ $note->deliveryInstruction->delivery_address ?? 'N/A' }}
            </div>
            <div class="info-block noborder">
                <table class="info-table">
                    <tr>
                        <td class="label">DATE</td>
                        <td>{{ $note->created_at->format('d-M-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">EXN Ref</td>
                        <td>{{ $note->dn_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Customer PO</td>
                        <td>{{ $note->deliveryInstruction->so_reference ?? $note->deliveryInstruction->di_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">End User</td>
                        <td>{{ $note->deliveryInstruction->end_user_name ?? $note->deliveryInstruction->customer_name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Opening Salutation & Intro Paragraph -->
        <div style="margin-bottom: 12px; font-size: 11px; line-height: 1.5; color: #222;">
            <strong>Dear Sir,</strong><br>
            Please release the following warehouse items as detailed below:
        </div>

        <!-- Items Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 25%;">SKU</th>
                    <th style="width: 40%;">DESCRIPTION</th>
                    <th style="width: 8%; text-align: center;">QTY</th>
                    <th style="width: 22%;">SERIAL NO</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach($note->items as $index => $item)
                    @php $totalQty += $item->quantity; @endphp
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td><strong>{{ $item->sku_code }}</strong></td>
                        <td>{{ $item->description ?? 'No Description' }}</td>
                        <td class="center">{{ $item->quantity }}</td>
                        <td>{{ $item->serial_numbers ?: 'BUILD IN' }}</td>
                    </tr>
                @endforeach
                <!-- xxxnothing followsxxx line -->
                <tr>
                    <td class="center"></td>
                    <td></td>
                    <td class="center" style="font-style: italic; color: #666;">xxxnothing followsxxx</td>
                    <td class="center"></td>
                    <td></td>
                </tr>
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; text-transform: uppercase;">Total</td>
                    <td class="center">{{ $totalQty }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Release Confirmation Paragraph -->
        <div style="margin-top: 10px; margin-bottom: 12px; font-size: 10px; line-height: 1.5; color: #333;">
            The items listed above have been inspected, verified, and released from the warehouse in good order and condition according to the delivery instruction requirements.
        </div>

        <div class="footer-note">
            NOTE: "NO REEXPORT/RESALE/TRANSFER TO A DIFFERENT END USER"
        </div>

        <!-- Receipt Section -->
        <div class="receipt-section">
            <div class="receipt-title">RECEIVED IN GOOD ORDER AND CONDITION</div>
            <div class="receipt-fields">
                <div>
                    <div class="field-line"></div>
                    <div class="field-label">NAME</div>
                </div>
                <div>
                    <div class="field-line"></div>
                    <div class="field-label">DATE</div>
                </div>
                <div>
                    <div class="field-line"></div>
                    <div class="field-label">SIGNATURE</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
