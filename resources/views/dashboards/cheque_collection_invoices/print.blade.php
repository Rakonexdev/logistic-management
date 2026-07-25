<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cheque Collection Invoice - {{ $invoice->invoice_number }}</title>
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
            min-height: 80px;
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
            width: 40%;
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
        .data-table td.right {
            text-align: right;
        }
        .data-table td.center {
            text-align: center;
        }
        .total-row td {
            font-weight: bold;
            font-size: 11px;
            background-color: #f5f5f5;
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
            background: #0255a5;
            color: #fff;
            border: none;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="print-btn-bar no-print">
        <button class="btn" onclick="window.print()">Print Invoice</button>
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
            <div class="logo-box">
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

        <div class="title">CHEQUE COLLECTION SERVICE INVOICE</div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-block">
                <div class="label-text">CUSTOMER / INVOICE TO:</div>
                <strong>{{ $invoice->customer_name }}</strong><br>
                Qatar Representative Office
            </div>
            <div class="info-block noborder">
                <table class="info-table">
                    <tr>
                        <td class="label">INVOICE NO</td>
                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">DATE ISSUED</td>
                        <td>{{ $invoice->created_at->format('d-M-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">PAYMENT STATUS</td>
                        <td><strong>{{ strtoupper($invoice->status) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 18%;">REF #</th>
                    <th style="width: 18%;">CHEQUE NO</th>
                    <th style="width: 29%;">REFERENCE IDS (PO / SO / INV)</th>
                    <th style="width: 15%; text-align: right;">CHEQUE AMOUNT</th>
                    <th style="width: 15%; text-align: right;">FEE (QAR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td><strong>{{ $item->collection_ref }}</strong></td>
                        <td>{{ $item->cheque_number ?: $item->collection_ref }}</td>
                        <td>
                            @if($item->chequeCollection)
                                PO: {{ $item->chequeCollection->po_reference ?: '-' }} | 
                                SO: {{ $item->chequeCollection->so_reference ?: '-' }} | 
                                Inv: {{ $item->chequeCollection->invoice_reference ?: '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="right">QAR {{ number_format($item->cheque_amount, 2) }}</td>
                        <td class="right"><strong>QAR {{ number_format($item->collection_fee, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">TOTAL CHEQUE COLLECTION SERVICE FEE (QAR):</td>
                    <td class="right" style="font-size: 12px; color: #0255a5;">QAR {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 15px; font-size: 10px; color: #666; text-align: center;">
            Thank you for doing business with Solutions Four WLL.
        </div>
    </div>
</body>
</html>
