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

        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .widget-card {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        .widget-value {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 0.5rem;
            background: linear-gradient(to right, var(--text-primary), var(--text-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .widget-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .widget-icon {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 2rem;
            opacity: 0.15;
            color: var(--accent-primary);
        }

        .form-panel {
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .table-responsive {
            overflow-x: auto;
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
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
        }

        .form-input, .form-select {
            padding: 0.5rem 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.875rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1 class="page-title">
            <i class="ph ph-chart-bar"></i> Reports & Dashboards
        </h1>
        <button class="btn btn-primary" onclick="alert('All reports exported to Excel!')">
            <i class="ph ph-file-xls"></i> Export All
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar glass">
        <div class="form-group">
            <label class="form-label" for="period">Billing Period</label>
            <select id="period" class="form-select">
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="this_quarter">This Quarter</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="warehouse">Warehouse</label>
            <select id="warehouse" class="form-select">
                <option value="all">All Warehouses</option>
                <option value="wh_main">WH-Main</option>
                <option value="wh_east">WH-East</option>
            </select>
        </div>

        <button class="btn btn-outline" onclick="alert('Filters applied!')">
            <i class="ph ph-funnel"></i> Apply Filter
        </button>
    </div>

    <!-- Widget Cards -->
    <div class="grid-cards">
        <div class="widget-card glass">
            <i class="ph ph-stack widget-icon"></i>
            <span class="widget-label">Current Stock (Units)</span>
            <span class="widget-value">{{ number_format($metrics['total_stock']) }}</span>
        </div>

        <div class="widget-card glass">
            <i class="ph ph-arrows-left-right widget-icon"></i>
            <span class="widget-label">Stock Movement Ratio</span>
            <span class="widget-value">{{ $metrics['movement_ratio'] }}</span>
        </div>

        <div class="widget-card glass">
            <i class="ph ph-truck widget-icon"></i>
            <span class="widget-label">Delivered Orders</span>
            <span class="widget-value">{{ $metrics['delivered_orders'] }}</span>
        </div>

        <div class="widget-card glass" style="border-color: rgba(239, 68, 68, 0.2);">
            <i class="ph ph-warning-octagon widget-icon" style="color: var(--danger);"></i>
            <span class="widget-label" style="color: var(--danger);">GRN Discrepancies</span>
            <span class="widget-value" style="background: var(--danger); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                {{ $metrics['grn_discrepancies'] }}
            </span>
        </div>
    </div>

    <!-- Details Panels -->
    <div class="form-panel glass">
        <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-primary);">Operational Summary</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Metric Report</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Performance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Delivery Completion Rate</strong></td>
                        <td>98%</td>
                        <td>99.2%</td>
                        <td style="color: var(--success); font-weight: 600;">Excellent</td>
                    </tr>
                    <tr>
                        <td><strong>GRN Receipt Time</strong></td>
                        <td>< 24 Hours</td>
                        <td>18.5 Hours</td>
                        <td style="color: var(--success); font-weight: 600;">Within SLA</td>
                    </tr>
                    <tr>
                        <td><strong>Discrepancy Resolution Rate</strong></td>
                        <td>100%</td>
                        <td>92.0%</td>
                        <td style="color: var(--warning); font-weight: 600;">Attention Needed</td>
                    </tr>
                    <tr>
                        <td><strong>Cheque Collection SLA</strong></td>
                        <td>48 Hours</td>
                        <td>45.0 Hours</td>
                        <td style="color: var(--success); font-weight: 600;">Within SLA</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
