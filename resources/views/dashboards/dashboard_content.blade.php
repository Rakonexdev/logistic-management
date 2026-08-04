@push('styles')
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .filter-group label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filter-select,
        .filter-input {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.875rem;
            min-width: 150px;
            transition: border-color 0.2s;
        }

        .filter-select:focus,
        .filter-input:focus {
            outline: none;
            border-color: var(--accent-primary);
        }

        .filter-select option {
            background: var(--bg-color);
            color: var(--text-primary);
        }

        /* Grid Layouts */
        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .grid-panels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Widgets */
        .widget-card {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .widget-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .widget-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .icon-stock {
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-primary);
        }

        .icon-orders {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .icon-deliveries {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .icon-invoices {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .icon-returns {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .icon-cheque {
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-secondary);
        }

        .widget-title {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0 0 0.5rem 0;
        }

        .widget-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .widget-trend {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .trend-up {
            color: var(--success);
        }

        .trend-down {
            color: var(--danger);
        }

        .widget-link {
            position: absolute;
            inset: 0;
            z-index: 10;
        }

        /* Panels */
        .panel {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .panel-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .panel-action {
            font-size: 0.875rem;
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .panel-action:hover {
            text-decoration: underline;
        }

        /* Lists inside panels */
        .data-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex: 1;
        }

        .data-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            transition: background 0.2s;
        }

        .data-item:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        .data-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .data-label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .data-sub {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.2);
            color: var(--info);
        }

        /* Progress bar */
        .progress-wrapper {
            margin-top: 0.5rem;
            width: 100%;
        }

        .progress-bar-bg {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 3px;
        }
    </style>
@endpush

<div class="dashboard-header">
    <h1 class="page-title">
        <i class="ph ph-squares-four"></i>
        Dashboard Overview
    </h1>
</div>

<form method="GET" action="{{ url()->current() }}" class="filter-bar glass">
    <div class="filter-group">
        <label>Date Range</label>
        <input type="date" name="date" class="filter-input" value="{{ request('date') }}">
    </div>
    <div class="filter-group">
        <label>Customer</label>
        <select name="customer_id" class="filter-select">
            <option value="all">All Customers</option>
            @if(isset($customers))
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="filter-group">
        <label>Warehouse</label>
        <select name="warehouse" class="filter-select">
            <option value="all">All Warehouses</option>
            @if(isset($warehouses))
                @foreach($warehouses as $wh)
                    <option value="{{ $wh }}" {{ request('warehouse') == $wh ? 'selected' : '' }}>{{ $wh }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="filter-group">
        <label>Status</label>
        <select name="status" class="filter-select">
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Any Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
    </div>
    <div style="flex-grow: 1; display: flex; justify-content: flex-end; align-items: flex-end; padding-bottom: 4px;">
        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Apply Filters</button>
        @if(request()->hasAny(['date', 'customer_id', 'warehouse', 'status']))
            <a href="{{ url()->current() }}" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-left: 0.5rem; text-decoration: none;">Clear</a>
        @endif
    </div>
</form>

<!-- Key Metrics Row -->
<div class="grid-cards">
    <!-- Current Stock -->
    <div class="widget-card glass">
        <a href="{{ route('products.index') }}" class="widget-link" title="Drill down to Stock"></a>
        <div class="widget-icon icon-stock">
            <i class="ph ph-stack"></i>
        </div>
        <h3 class="widget-title">Stocks</h3>
        <p class="widget-value">{{ number_format($metrics['stocks']['total_qty'] ?? 0) }}</p>
        <div class="widget-trend trend-up">
            <i class="ph ph-tag"></i> {{ number_format($metrics['stocks']['total_skus'] ?? 0) }} Total SKUs
        </div>
    </div>

    <!-- Sales Orders / ASN -->
    <div class="widget-card glass">
        <a href="{{ Auth::user() && Auth::user()->role === 'sfq_user' ? route('sfq.grns.index') : route('asns.index') }}" class="widget-link" title="Drill down to ASN"></a>
        <div class="widget-icon icon-orders">
            <i class="ph ph-shopping-cart"></i>
        </div>
        <h3 class="widget-title">ASN</h3>
        <p class="widget-value">{{ number_format($metrics['asn']['total'] ?? 0) }}</p>
        <div class="progress-wrapper">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: {{ $metrics['asn']['percentage'] ?? 0 }}%; background: var(--success);"></div>
            </div>
        </div>
        <div class="widget-trend" style="color: var(--text-secondary)">
            {{ $metrics['asn']['percentage'] ?? 0 }}% Fulfilled ({{ number_format($metrics['asn']['completed'] ?? 0) }} of {{ number_format($metrics['asn']['total'] ?? 0) }})
        </div>
    </div>

    <!-- Active Deliveries -->
    <div class="widget-card glass">
        <a href="{{ route('delivery-instructions.index') }}" class="widget-link" title="Drill down to Deliveries"></a>
        <div class="widget-icon icon-deliveries">
            <i class="ph ph-truck"></i>
        </div>
        <h3 class="widget-title">Active Deliveries</h3>
        <p class="widget-value">{{ number_format($metrics['deliveries']['total'] ?? 0) }}</p>
        <div class="progress-wrapper">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: {{ $metrics['deliveries']['percentage'] ?? 0 }}%; background: var(--info);"></div>
            </div>
        </div>
        <div class="widget-trend" style="color: var(--text-secondary)">
            {{ number_format($metrics['deliveries']['in_transit'] ?? 0) }} In Transit
        </div>
    </div>

    <!-- Open Invoices -->
    <div class="widget-card glass">
        <a href="{{ route('delivery-invoices.index') }}" class="widget-link" title="Drill down to Invoices"></a>
        <div class="widget-icon icon-invoices">
            <i class="ph ph-receipt"></i>
        </div>
        <h3 class="widget-title">Open Invoices</h3>
        <p class="widget-value">QAR {{ number_format($metrics['open_invoices']['total_amount'] ?? 0, 2) }}</p>
        <div class="widget-trend trend-down">
            <i class="ph ph-receipt"></i> {{ number_format($metrics['open_invoices']['count'] ?? 0) }} Pending Invoices
        </div>
    </div>
</div>

<!-- Month-Wise Stock Movement Chart Row -->
<div class="glass" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.25rem 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-chart-bar" style="color: var(--accent-primary, #6366f1); font-size: 1.3rem;"></i> Month-Wise Stock Movement (Stock In vs Stock Out)
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0;">Comparison of total monthly stock coming in (Inbound) vs stock going out (Outbound).</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center; font-size: 0.85rem; font-weight: 600;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: #10b981; display: inline-block;"></span>
                <span>Stock In (Inbound)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: #6366f1; display: inline-block;"></span>
                <span>Stock Out (Outbound)</span>
            </div>
        </div>
    </div>
    
    <div style="position: relative; height: 320px; width: 100%;">
        <canvas id="monthlyStockMovementChart"></canvas>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('monthlyStockMovementChart');
            if (!ctx) return;

            const labels = {!! json_encode($monthlyStockData['labels'] ?? []) !!};
            const stockIn = {!! json_encode($monthlyStockData['stock_in'] ?? []) !!};
            const stockOut = {!! json_encode($monthlyStockData['stock_out'] ?? []) !!};

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Stock In (Inbound)',
                            data: stockIn,
                            backgroundColor: 'rgba(16, 185, 129, 0.75)',
                            borderColor: '#10b981',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            borderSkipped: false
                        },
                        {
                            label: 'Stock Out (Outbound)',
                            data: stockOut,
                            backgroundColor: 'rgba(99, 102, 241, 0.75)',
                            borderColor: '#6366f1',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            padding: 12,
                            backgroundColor: 'rgba(15, 17, 26, 0.95)',
                            titleColor: '#f8f9fa',
                            bodyColor: '#f8f9fa',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: '#adb5bd',
                                font: { family: 'Inter', size: 12 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: '#adb5bd',
                                font: { family: 'Inter', size: 12 },
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush