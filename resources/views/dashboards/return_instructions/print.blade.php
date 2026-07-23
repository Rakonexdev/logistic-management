<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Instruction - {{ $instruction->return_ref }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            background: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .ref {
            font-size: 16px;
            color: #666;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 6px;
        }

        .box-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #777;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f4f4f4;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
        }

        .sig-box {
            border-top: 1px solid #333;
            padding-top: 8px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; background: #6366f1; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Document
        </button>
    </div>

    <div class="header">
        <div>
            <div class="title">RETURN INSTRUCTION DOCUMENT</div>
            <div class="ref">Ref #: {{ $instruction->return_ref }}</div>
        </div>
        <div style="text-align: right;">
            <strong>LOGISTICSPRO / END SYSTEM</strong><br>
            Date: {{ $instruction->instruction_received_date ? $instruction->instruction_received_date->format('Y-m-d') : date('Y-m-d') }}
        </div>
    </div>

    <div class="grid">
        <div class="box">
            <div class="box-title">Client / Pickup Location</div>
            <strong>{{ $instruction->customer_name }}</strong><br>
            Address: {{ $instruction->pickup_address }}<br>
            Contact: {{ $instruction->contact_person ?: 'N/A' }} ({{ $instruction->contact_phone ?: 'N/A' }})
        </div>

        <div class="box">
            <div class="box-title">SFQ Driver & Storage Assignment</div>
            Driver: <strong>{{ $instruction->driver_name ?: 'Pending Assignment' }}</strong><br>
            Vehicle: {{ $instruction->driver_vehicle ?: 'N/A' }}<br>
            Designated Location: <strong>{{ $instruction->storing_location ?: 'Pending Designation' }}</strong>
        </div>
    </div>

    <div class="box-title" style="margin-bottom: 8px;">Items to be Picked Up</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>SKU Code</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Serial Numbers</th>
            </tr>
        </thead>
        <tbody>
            @foreach($instruction->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item->sku_code }}</strong></td>
                    <td>{{ $item->description ?: '-' }}</td>
                    <td><strong>{{ $item->quantity }}</strong></td>
                    <td>{{ $item->serial_numbers ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($instruction->remarks)
        <div class="box" style="margin-bottom: 25px;">
            <div class="box-title">Remarks & Instructions</div>
            {{ $instruction->remarks }}
        </div>
    @endif

    <div class="signatures">
        <div class="sig-box">
            Client Pickup Signature & Date
        </div>
        <div class="sig-box">
            SFQ Driver Signature & Date
        </div>
    </div>

</body>
</html>
